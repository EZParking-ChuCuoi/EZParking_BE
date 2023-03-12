<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Block;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
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
        // message
        \App\Models\Message::factory(50)->create();
        
        
        
        $parkingLots = ParkingLot::factory(50)->create();
        \App\Models\UserParkingLot::factory(20)->create();

        foreach ($parkingLots as $parkingLot) {
            $blocks = Block::factory(5)->create(['parkingLotId' => $parkingLot->id])->each(function ($block, $index) {
                $blockNames = ['Khu A', 'Khu B', 'Khu C', 'Khu D', 'Khu E', 'Khu F', 'Khu G', 'Khu H', 'Khu I', 'Khu J'];
                $block->nameBlock = $blockNames[$index];
                $block->save();
            });

            foreach ($blocks as $block) {
                $slotCount = 1;

                $slots = ParkingSlot::factory(40)->create(['blockId' => $block->id])->each(function ($slot) use ($block, &$slotCount) {
                    $lastLetter = substr($block->nameBlock, -1);
                    $slot->slotName = strtoupper($lastLetter) . $slotCount++;
                    $slot->save();

                    $slot->bookings()->createMany(Booking::factory(3)->raw([
                        'slotId' => $slot->id,
                    ]));
                });
            }
        }

        // import database UserParkingLot

        //import database comments
        \App\Models\Comment::factory(200)->create();


        // import database block


    }
}
