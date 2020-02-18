<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsFiveTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			'inventory-stocktally',
			'showroom-inventory',
			'approval-inventory',
			'sold-inventory',
			'showroom-allstock',
			'inventory-memolist',
			'inventory-invoicelist',
			'inventory-returnmemolist',
			'showroom-salesreturnlist',
			'inventory-quotationlist',
		];

		foreach ($permissions as $permission) {
			Permission::create(['name' => $permission]);
		}
	}
}