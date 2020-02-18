<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {

		DB::table('roles')->delete();
		$statement = "ALTER TABLE dml_roles AUTO_INCREMENT = 1;";
		DB::unprepared($statement);

		$roles = [
			'Super Admin',
			'User Manager',
			'Role Manager',
			'Role Creator',
			'Vendor',
			'QC',
			'Diamond Manager',
			'Gold Manager',
			'Account Manager',
			'Payment Manager',
		];

		foreach ($roles as $role) {
			Role::create(['name' => $role]);
		}
	}
}
