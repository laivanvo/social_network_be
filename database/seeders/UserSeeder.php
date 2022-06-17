<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Relation;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::factory()->count(100)->has(Profile::factory())->create();
        for($i = 0; $i <= 10; $i++) {
            for ($j = $i+1; $j <= 20; $j++) {
                Relation::create([
                    'from' => $users[$j]->id,
                    'to' => $users[$i]->id,
                    'type' => 'request',
                ]);
            }
        }
    }
}
