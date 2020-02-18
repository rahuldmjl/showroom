<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			'permission-list',
			'permission-create',
			'permission-edit',
			'permission-delete',
			'costing-create',
			'vendor-list',
			'payment-create',
			'paymenttype-list',
			'paymenttype-create',
			'paymenttype-edit',
			'paymenttype-delete',
			'metals-list',
			'metals-create',
			'metals-edit',
			'metals-delete',
			'gold_download_purchase_invoice',
		];

		foreach ($permissions as $permission) {
			Permission::create(['name' => $permission]);
		}
	}
}