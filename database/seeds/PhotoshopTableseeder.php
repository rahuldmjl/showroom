<?php

use Illuminate\Database\Seeder;

class PhotoshopTableseeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('categories')->insert(array(
	  [
	  'entity_id'=>'16',
	  'name'=>'Earrings',
	  ],
	  [
	  'entity_id'=>'14',
	  'name'=>'Rings',
	  ],
	   [
	  'entity_id'=>'18',
	  'name'=>'Pendants',
	  ],
	   [
	  'entity_id'=>'33',
	  'name'=>'Necklace',
	  ],
	   [
	  'entity_id'=>'15',
	  'name'=>'Diamond',
	  ],
	   [
	  'entity_id'=>'36',
	  'name'=>'Nosepin',
	  ],
	   [
	  'entity_id'=>'22',
	  'name'=>'Pendant with Earring',
	  ],
	   [
	  'entity_id'=>'17',
	  'name'=>'Bangles',
	  ],
	   [
	  'entity_id'=>'10',
	  'name'=>'PENDANTS & SETS',
	  ],
	   [
	  'entity_id'=>'23',
	  'name'=>'Bracelets',
	  ],
	   [
	  'entity_id'=>'35',
	  'name'=>'Rubber bracelets',
	  ]) 
	   );
	  
	  DB::table('photoshop_status_types')->insert(array(
	  [
	  'status_name'=>'PHOTOGRAPHY_DONE',
	  'status_id'=>'1',
	  ],
	  [
	  'status_name'=>'PHOTOGRAPHY_REWORK',
	  'status_id'=>'2',
	  ],
	   [
	  'status_name'=>'PSD_DONE',
	  'status_id'=>'3',
	  ],
	   [
	  'status_name'=>'PSD_REWORK',
	  'status_id'=>'4',
	  ],
	   [
	  'status_name'=>'PSD_REWORK',
	  'status_id'=>'5',
	  ],
	   [
	  'status_name'=>'EDITING_REWORK',
	  'status_id'=>'6',
	  ],
	   [
	  'status_name'=>'JPEG_DONE',
	  'status_id'=>'7',
	  ],
	   [
	  'status_name'=>'JPEG_REWORK',
	  'status_id'=>'8',
	  ],
	   [
	  'status_name'=>'PLACEMENT_DONE',
	  'status_id'=>'10',
	  ],
	  [
	  'status_name'=>'PLACEMENT_REWORK',
	  'status_id'=>'11',
	  ]
	  ));
    }
}
