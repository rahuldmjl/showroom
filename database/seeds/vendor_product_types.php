<?php

use Illuminate\Database\Seeder;

class vendor_product_types extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('vendor_product_types')->insert([

            [
                'name'=>'Except Bracelet',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'=>'Bracelet',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]

        ]);
    }
}
