<?php

use Illuminate\Database\Seeder;

class TransactionTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$transactions = [
			'Purchase',
			'Issue',
			'Reissue',
			'Purchase with Reissue',
			'Purchase from Vendor',
			'Sell',
			'Misc',
		];

		foreach ($transactions as $transaction) {
			//TransactionType::create(['name' => $transaction]);
			DB::table('transaction_types')->insert([
				'name' => $transaction,
			]);
		}
	}
}
