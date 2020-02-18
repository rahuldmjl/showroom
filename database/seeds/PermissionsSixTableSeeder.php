<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSixTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			'customers-list',
			'customer-view',
		];

		foreach ($permissions as $permission) {
			Permission::create(['name' => $permission]);
		}
	}
}