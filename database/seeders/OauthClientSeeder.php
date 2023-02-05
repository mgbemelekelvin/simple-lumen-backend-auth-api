<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OauthClient;
class OauthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OauthClient::firstOrCreate([
            'name'=>'{"name":"WebApp","country":"Nigeria","currency":"Naira"}',
            'secret'=>'eRvMzEIMiFof5LlenTGIA9MrVj12Sf4h6A7QzhpEmP7KoQJ8thZLls55Jjb0WVmxa',
            'provider'=>'Seller',
            'redirect'=>'http://localhost',
            'personal_access_client'=>false,
            'password_client'=>false,
            'revoked'=>false,
        ]);
        OauthClient::firstOrCreate([
            'name'=>'{"name":"Cronjob","country":"Nigeria","currency":"Naira"}',
            'secret'=>'FhYOhoQSbkbpvKOs6tRIZWHDtId123dsffsdff3voaH0ezFUZNtwxYbfgtSX5fx0e6',
            'provider'=>'CronJob',
            'redirect'=>'http://localhost',
            'personal_access_client'=>false,
            'password_client'=>false,
            'revoked'=>false,
        ]);
    }
}
