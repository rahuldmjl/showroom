<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsFourTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			'transaction-type',
			'gold-inventory',
			'gold-inventory-create',
			'diamond-inventory',
			'diamond-inventory-create',
			'diamond-invoiceattachment',
			'diamond-importexcel',
			'diamond-diamondissue',
			'purchase-history',
		];

		foreach ($permissions as $permission) {
			Permission::create(['name' => $permission]);
		}
	}
}