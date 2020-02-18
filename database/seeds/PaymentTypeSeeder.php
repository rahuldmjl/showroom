<?php

use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	//$faker = 
    	//$date = $faker->dateTimeBetween($startdate,$endDate);
    	$date = date('Y-m-d');
        DB::table('payment_types')->insert([
			
			[
				'name' => 'Accouting Charges',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
			[
				'name' => 'Barcode Scanner Expens',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
			[
				'name' => 'Certification',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
			[
				'name' => 'Conveyance Expenses',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
			[
				'name' => 'Jewellary',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
			[
				'name' => 'Donation',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Electricity Charges',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Employer Contribution',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Franchise Commission.',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Insurance',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Interenet Expenses',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Marketing Expense',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Membership and Subscription',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Misc. Expenses',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Office Expenses',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Office Rent',
				'parent_id'=>'0',
				'creat[ed_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Postage (Courier)',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Printing & Stationery',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Professional Fees',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Rate & Taxes',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Rendering Expenses',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Repair & Maint.',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Repair & Maint. Computer',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Resin Ribbon',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'ROC Expenses',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Server Infrastructure Maintenance',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Server Servicing Charges',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Shipment Charges',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'TAG Purchase',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Jewellary',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Telephone Expenses',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Traveling Expenses',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Welfare',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Suspense',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Salary',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Job Work Jewellery',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Bank Charges',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Business Promotions',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Exhibition Expenses',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'IGI Charges J.W.',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Website Developer',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'W/off',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Jewellary',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Packing Material',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'CGST',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'IGST',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'SGST',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Pt',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'TDS Payable for Commission',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'TDS Payable for Professional Fees',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'TDS Payable for Rent',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'TDS Payable on Contract',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'TDS Payable on Salary',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Softwares',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'TRADE MARK',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Website Development',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Computer and Printers',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Furniture & Fixture',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Office Equipments',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Cash',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Central Bank Of India A/C 3486168684',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'HDFC Bank Ltd - A/c No. 50200010367715',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'HDFC Bank Ltd - A/C No. 50200016334981',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Kotak Mahindra Bank A/C No.7212019981',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'SBI',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Purchase (Interstate)',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'PURCHASE (LOCAL)',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Purchase Return (Interstate)',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Purchase Return (Local)',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Sale (Interstate)',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Sale Return (Interstate)',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Sale Return (Local)',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Sales (Local)',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Shipping Charges',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
				[
				'name' => 'Trade Discount',
				'parent_id'=>'0',
				'created_at' => $date,
				'updated_at' => $date,
				],
	]);	
	}
}