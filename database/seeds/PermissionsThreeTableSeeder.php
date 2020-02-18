<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsThreeTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			'payments-create',
			'payments-list',
			'payments-edit',
			'payments-delete',
			'payments-incoming',
			'payments-outgoing',
			'payments-approve',
			'payments-decline',
		];

		foreach ($permissions as $permission) {
			Permission::create(['name' => $permission]);
		}
	}
}