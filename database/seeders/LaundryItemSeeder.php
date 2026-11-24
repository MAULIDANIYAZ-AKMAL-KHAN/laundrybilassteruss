<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaundryItemSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Reguler',
                'category' => 'Cuci',
                'price' => 7000,
                'estimated_duration' => '3-4 hari',
                'description' => 'Layanan cuci reguler dengan harga Rp 7.000 per kg.',
                'legacy_names' => ['Cuci Baju Reguler'],
            ],
            [
                'name' => 'Ekspres',
                'category' => 'Cuci',
                'price' => 12000,
                'estimated_duration' => '1-2 hari',
                'description' => 'Layanan cuci ekspres dengan harga Rp 12.000 per kg.',
                'legacy_names' => ['Cuci Baju Ekspres'],
            ],
            [
                'name' => 'Superkilat',
                'category' => 'Cuci',
                'price' => 25000,
                'estimated_duration' => '5 jam selesai',
                'description' => 'Layanan cuci superkilat dengan harga Rp 25.000 per kg.',
                'legacy_names' => ['Setrika Baju Reguler'],
            ],
        ];

        foreach ($plans as $plan) {
            $legacyNames = $plan['legacy_names'];
            unset($plan['legacy_names']);

            $existingItem = DB::table('laundry_items')
                ->whereIn('name', array_merge([$plan['name']], $legacyNames))
                ->first();

            if ($existingItem) {
                DB::table('laundry_items')->where('id', $existingItem->id)->update([
                    ...$plan,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('laundry_items')->insert([
                    ...$plan,
                    'image' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach (['Setrika Baju Ekspres', 'Paket Cuci dan Setrika'] as $legacyName) {
            $legacyItem = DB::table('laundry_items')->where('name', $legacyName)->first();

            if ($legacyItem && ! DB::table('order_details')->where('laundry_item_id', $legacyItem->id)->exists()) {
                DB::table('laundry_items')->where('id', $legacyItem->id)->delete();
            }
        }
    }
}
