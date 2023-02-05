<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait Responses {
    /**
     * Build success response
     * @param string|array $data
     * @param int $status
     * @return ResponseAlias
     */

    public function response($status, $message, $data)
    {
        return response()->json(['status' => $status, 'message' => $message, 'data' => $data], $status);
    }

    public function error($channel, $name, $th)
    {
        //send a slack/database notification
        if (env('APP_DEBUG', false)){
            $res = ['Error' => ['message' => $th->getMessage(), 'file' => $th->getFile(), 'line' => $th->getLine()], 'userId'=>Auth::check()?Auth::user()->id:'', 'address' => config('app.url')];
        }
        return self::response(ResponseAlias::HTTP_BAD_REQUEST, 'Something went wrong', $res ?? null);
    }

    public function errorCallback($result)
    {
        DB::rollback();
        $msg = is_array($result) ? $result['message'] : $result->message;
        return self::response($result->status, $msg, $result->data ?? null);
    }

    public function ApiCallResponse($response)
    {
        return [
            'status' => $response['status'] ?? ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $response['message'] ?? null,
            'data' => $response['data'] ?? null
        ];
    }
}
