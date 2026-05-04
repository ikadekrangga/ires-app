<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    
    public function run(): void
    {
        $accounts = [
            [
                'account_name' => 'Gooreliea',
                'facebook_page_id' => '12345678',
                'access_token' => 'EEABASBCASDSADSAD',
            ],
        ];

        foreach ($accounts as $data) {
            // updateOrCreate mencegah data double jika seeder dijalankan ulang
            Account::updateOrCreate(
                ['facebook_page_id' => $data['facebook_page_id']], 
                $data
            );
        }

        $this->command->info(count($data) . 'data berhasil dimasukkan ke dalam database!');
    }
}
