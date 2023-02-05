<?php /** @noinspection ALL */
/** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpUndefinedFieldInspection */

namespace App\Repositories;

use App\Http\Validations\LoginValidation;
use App\Interfaces\AuthenticationRepositoryInterface;
use App\Mail\sendNotification;
use App\Models\oauth_access_token;
use App\Models\OauthClient;
use App\Models\OnetimeVerificationCode;
use App\Models\OnetimeVerificationType;
use App\Models\Session;
use App\Models\User;
use App\Services\TokenService;
use App\Services\UserService;
use App\Utils\Authentication;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Traits\responses;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Support\Facades\URL;

class AuthenticationRepository implements AuthenticationRepositoryInterface
{
    use responses;

    protected $loginValidation, $authenticationUtils;

    public function __construct(LoginValidation $loginValidation, Authentication $authenticationUtils)
    {
        $this->loginValidation = $loginValidation;
        $this->authenticationUtils = $authenticationUtils;
    }

    /** @noinspection PhpUndefinedFieldInspection */

    public function login($request)
    {
        try {
            DB::beginTransaction();
            if (empty($request->server('HTTP_USER_AGENT'))){
                $this->loginValidation->userAgentRequired($request);
                $user_agent = $request->user_agent;
            } else {
                $user_agent = $request->server('HTTP_USER_AGENT');
            }

            if (isset($request->user_id)){
                $data = ['user_id' => $request->user_id,];
            } else {
                $this->loginValidation->emailPasswordRequired($request);
                $data = ['email' => $request->email, 'password' => $request->password];
            }

            $user = User::where(function ($query) use ($request){
                $query->where('id', $request->user_id);
                $query->orWhere('email', $request->email);
            })->withTrashed()->first();
            if (!$user) return $this->response(ResponseAlias::HTTP_UNAUTHORIZED,'User not found, Please check your credentials and try again.','');

            if (!isset($request->user_id)) {
                if (!Hash::check($request->password, $user->password)) return $this->response(ResponseAlias::HTTP_UNAUTHORIZED,'Incorrect Details, Please check your credentials and try again.','');
            }

            //Check is user is authorized to login
            $userCheck = $this->authenticationUtils->authorizeUser($user);
            if (!$userCheck['authorize']) return $this->response(ResponseAlias::HTTP_UNAUTHORIZED, $userCheck['message'],'');

            //Check if user has an active Session, else create a new one
            if (count($user->activeSession) > 0){
                $token = (object)[
                    'accessToken' => $user->activeSession[0]->token,
                    'token' => [
                        'id' => $user->activeSession[0]->oauth_access_token_id
                    ]
                ];
            } else {
                $token = $user->createToken('authToken');
            }

            //Create User Session
            $this->authenticationUtils->createSession($user->id, $token, $user_agent);

            DB::commit();
            $user = User::getUser($user->id);
            return $this->response(200,'',$user);
        } catch (Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Login', $th);
        }
    }

    public function checkAuth($request): ?JsonResponse
    {
        $authorization = request()->header('Authorization');
        if(!$authorization) return $this->response(401, 'Unauthenticated!', '');
        try {
            DB::beginTransaction();
            $session = Session::where('token', str_replace('Bearer ','',$authorization))->first();
            if (!$session) return $this->response(401,'Unauthenticated! Session not found!','');
            $token = oauth_access_token::where('id' , $session->oauth_access_token_id)->first();
            if (!$token) return $this->response(401,'Unauthenticated! Token not found!','');
            if ($token->revoked || $token->expires_at < Carbon::now()){
                $token->delete();
                $msg = 'Unauthenticated! '. $token->revoked ? 'Token is revoked!' : 'Token time has expired. Please log in again.';
                //Delete login devices and session
                foreach ($session->loginDevices as $loginDevice){
                    $loginDevice->delete();
                }
                $session->forceDelete();
                return $this->response(401, $msg, '');
            }
            $user = User::getUser($session->user_id);
            if(!$user) return $this->response(404,'User not found!','');
            if (!$user->active) return $this->response(401, 'Unauthenticated! User is Deactivated!','');
            DB::commit();
            return $this->response(200, '', ['id' => $user->id, 'email' => $user->email,]);
        } catch (Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Login', $th);
        }
    }

    public function logout($request)
    {
        $authorization = request()->header('Authorization');
        if(!$authorization) return $this->response(401,'Not Authenticated','');
        try {
            DB::beginTransaction();
            $session = Session::where('token', str_replace('Bearer ','',$authorization))->first();
            if ($session){
                foreach($session->loginDevices as $loginDevice){
                    $loginDevice->delete();
                }
                if ($session->oauth_access_token){
                    // $session->oauth_access_token->revoked = true;
                    $session->oauth_access_token->delete();
                }
                $session->forceDelete();
            }
            DB::commit();
            return $this->response(200,'You have logged out successfully.','');
        } catch (Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Logout', $th);
        }
    }

    public function forgotPasswordOneTimeToken($request)
    {
        try {
            $validate = $this->loginValidation->forgotPasswordOneTimeToken($request);
            if (isset($validate->status)) return $this->response(400, $validate->message, '');

            //Check if verification type is valid
            $verificationType = OnetimeVerificationType::where('name', $request->verificationType)->first();
            if (!$verificationType) return $this->response(400,'Failed! Verification is not valid.','');
            //Check if user exist
            $user = User::where('email', $request->email)->first();
            if(!$user) return $this->response(400,'Failed! Cannot find any user with such email.','');
            //Authenticate User
            $loginUserParams = $this->login($request->merge(['user_agent' => $request->user_agent, 'data' => 'id only', 'user_id' => $user->id]));
            $loginUser = (object)json_decode($loginUserParams->content(), true);
            if ($loginUser->status != 200) return $this->errorCallback($loginUser);
            DB::beginTransaction();
            //get all codes that have exceeded its expiry time and make their status false and delete
            $Codes = OnetimeVerificationCode::where('user_id', $user->id)
                ->where('onetime_verification_type_id', $verificationType->id)
                ->where('status', true)
                ->where('created_at','<', Carbon::now()->subMinutes($verificationType->expiry_time))
                ->get();
            foreach ($Codes as $code){
                $code->status = false;
                $code->save();
                $code->delete();
            }

            //Check if the user has used request for more than 3 times in a day
            if ($verificationType->name == 'Password Reset'){

                $verificationCodes = OnetimeVerificationCode::where('user_id', $user->id)->withTrashed()
                    ->where('status', false)
                    ->where('onetime_verification_type_id', $verificationType->id)
                    ->whereDate('created_at', Carbon::now())
                    ->get();
                if (count($verificationCodes) >= 3) return $this->response(400,'Failed! You have exceeded the daily limits of password reset, Try again tomorrow or use the One Time Login to sign in..','');
            }

            //check for the last valid verification code
            $checkVerificationCode = OnetimeVerificationCode::where('user_id', $user->id)
                ->where('onetime_verification_type_id', $verificationType->id)
                ->where('status', true)
                ->where('created_at','>', Carbon::now()->subMinutes($verificationType->expiry_time))
                ->first();

            if ($checkVerificationCode){
                $checkVerificationCode->created_at = Carbon::now();
                $checkVerificationCode->save();
                $code = $checkVerificationCode->code;
            } else {
                $new = new OnetimeVerificationCode();
                if ($verificationType->name == 'Password Reset'){
                    $new->code = Crypt::encryptString($user->email);
                } else {
                    $new->code = strtoupper(Str::random(5));
                }
                $new->user_id = $user->id;
                $new->onetime_verification_type_id = $verificationType->id;
                $new->status = true;
                $new->save();
                $code = $new->code;
            }

            if ($verificationType->name == 'Password Reset'){
                $email_subject = 'Reset Password';
                $email_message_1 = '<p style="text-align: left;">A password reset was initiated for your account.<br> Please click on the link below to change your password. <br>Note that the link is valid for '.CarbonInterval::minutes($verificationType->expiry_time)->cascade()->forHumans().'. After the time limit has expired, you will have to resubmit the request for a password reset.</p>';
                $email_message_2 = '<p style="text-align: left;">If you did not initiate this request. Kindly ignore the mail.</p>';
                $email_button_1 = 'Change Your Password Now';
                $email_button_url_1 = URL::to('/').'/api/v1/reset-password/'.$code.'?app='.$request->app;
                $res = 'Password Reset Link has been sent to your email.';
            } else {
                $email_subject = 'Your One Verification Code';
                $email_message_1 = '<p style="text-align: left;">A One Time Login Code has been generated.<br> <span style="text-transform: uppercase; font-size: 30px; color: black;"> '.$code.'</span>. <br>Note that this code is valid for '.CarbonInterval::minutes($verificationType->expiry_time)->cascade()->forHumans().'. After the time limit has expired, you will have to resubmit the request for a one time login.</p>';
                $email_message_2 = '<p style="text-align: left;">If you did not initiate this request. Kindly ignore the mail.</p>';
                $email_button_1 = null;
                $email_button_url_1 = null;
                $res = 'Onetime verification Code Created and sent to your email.';
            }

            //Send mail to the user
            $data = [
                'subject' => $email_subject,
                'name' => $user->first_name.' '.$user->last_name,
                'email_message_1' => $email_message_1,
                'email_message_2' => $email_message_2,
                'email_button_1' => $email_button_1,
                'email_button_url_1' => $email_button_url_1,
            ];
            Mail::to($request->email)->send(new sendNotification($data));

            DB::commit();
            return $this->response(200,$res,'');
        } catch (Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Generate Forgot Password & OneTime login Token', $th);
        }
    }

    public function verifyOnetimeToken($request)
    {
        try {
            $validate = $this->loginValidation->verifyOnetimeToken($request);
            if (isset($validate->status)) return $this->response(400, $validate->message, '');

            $code = $request->code;
            $checkCode = OnetimeVerificationCode::where('code', $code)->where('status', true)->first();
            if (!$checkCode) return $this->response(404,'Failed! Onetime login Code not Found!','');
            $user = User::where('id', $checkCode->user_id)->where('email', $request->email)->first();
            if (!$user) return $this->response(404,'User not found!','');
            //Check if verification type is valid
            $verificationType = OnetimeVerificationType::where('id', $checkCode->onetime_verification_type_id)->where('name', 'One Time Login')->first();
            if(!$verificationType) return $this->response(404,'Verification type not found!','');
            DB::beginTransaction();
            if($checkCode->created_at < Carbon::now()->subMinutes($verificationType->expiry_time)){
                $checkCode->status = false;
                $checkCode->save();
                $checkCode->delete();
                return $this->response(400,'Failed! Onetime login Code has expired!','');
            } else {
                //Authenticate User
                $user_agent = $request->server('HTTP_USER_AGENT') ?? $request->user_agent;
                $loginUserParams = $this->login($request->merge([
                    'user_agent' => $user_agent,
                    'data' => 'id only',
                    'user_id' => $user->id
                ]));
                $loginUser = (object)json_decode($loginUserParams->content(), true);
                if ($loginUser->status != 200) return $this->errorCallback($loginUser);
                $checkCode->status = false;
                $checkCode->deleted_at = Carbon::now();
                $checkCode->save();
            }
            DB::commit();
            return $this->response(200,'',$loginUser->data);
        } catch (Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Verify Onetime Token', $th);
        }
    }

    public function verifyForgotPassword($request)
    {
        try {
            $validate = $this->loginValidation->verifyForgotPassword($request);
            if (isset($validate->status)) return $this->response(400, $validate->message, '');

            $checkCode = OnetimeVerificationCode::where('code', $request->code)->where('status', true)->first();
            if (!$checkCode) return $this->response(400,'Failed! Your password reset link is invalid.','');
            $verificationType = OnetimeVerificationType::where('id', $checkCode->onetime_verification_type_id)->where('name', 'Password Reset')->first();
            if (!$verificationType) return $this->response(404,'Verification Type not found!','');
            DB::beginTransaction();
            if($checkCode->created_at < Carbon::now()->subMinutes($verificationType->expiry_time)) return $this->response(400,'Failed! Your password reset link has expired.','');
            $checkCode->status = false;
            $checkCode->deleted_at = Carbon::now();
            $checkCode->save();
            DB::commit();
            return $this->response(200, '', ['email' => Crypt::decryptString($request->code)]);
        } catch (Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Verify Forgot Password', $th);
        }
    }

    public function resetPassword($request)
    {
        try {
            $validate = $this->loginValidation->resetPassword($request);
            if (isset($validate->status)) return $this->response(400, $validate->message, '');

            $user = User::where('email', $request->email)->first();
            if (!$user) return $this->response(404,'User not found!','');
            //Authenticate User
            $loginUserParams = $this->login($request->merge(['user_agent' => $request->server('HTTP_USER_AGENT'), 'data' => 'id only', 'user_id' => $user->id]));
            $loginUser = (object)json_decode($loginUserParams->content(), true);
            if ($loginUser->status != 200) return $this->errorCallback($loginUser);
            DB::beginTransaction();
            //change password
            $user->password = Hash::make($request->new_password);

            //Send mail to the user
            $data = [
                'subject' => 'Password Changed',
                'name' => $user->first_name.' '.$user->last_name,
                'email_message_1' => '<p>This is a confirmation that the password for your  account has just been changed.<br>If you didn\'t change your password, please contact  Customer\'s service immediately.</p>',
            ];
            Mail::to($request->email)->send(new sendNotification($data));

            DB::commit();
            return $this->response(200,'Password changed successfully','');
        } catch (Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Reset Password', $th);
        }
    }
}
