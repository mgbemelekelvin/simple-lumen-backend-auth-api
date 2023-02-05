<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OnetimeVerificationType;
class OnetimeVerificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OnetimeVerificationType::firstOrCreate([
            'name'=>'Password Reset',
            'expiry_time'=>120
        ]);
        OnetimeVerificationType::firstOrCreate([
            'name'=>'One Time Login',
            'expiry_time'=>30
        ]);
    }
}
