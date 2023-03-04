<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // import database user
        \App\Models\User::factory(20)->create();
        // import database role
        \App\Models\Role::factory(3)->create();
        
        // import database RoleDFUser
        \App\Models\RoleDFUser::factory(20)->create();
        
        // import database ParkingLot
        \App\Models\ParkingLot::factory(4)->create();

        //import database comments
        \App\Models\Comment::factory(200)->create();

        // import database UserParkingLot
        \App\Models\UserParkingLot::factory(20)->create();
        
        // import database block
        \App\Models\Block::factory(20)->create();
        
        // import database ParkingSlot
        \App\Models\ParkingSlot::factory(900)->create();

        \App\Models\Booking::factory(10000)->create();
        

    }
}
