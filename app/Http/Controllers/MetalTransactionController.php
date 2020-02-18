<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Metal;
use App\MetalTransaction;
use App\TransactionType;
use App\PaymentType;
use App\Payment;
use Auth;
use DB;
use Illuminate\Http\Request;

class MetalTransactionController extends Controller {

	public function downloadInvoice($id) {
		$getTransData = MetalTransaction::where('id', $id)->first();
	
		if($getTransData->transaction_type == 1)
		{
				$exactFilePath = base_path("public" . '/' . config('constants.dir.purchased_invoices')) . '/' .$getTransData->purchased_invoice;
					return response()->download($exactFilePath);
		}
		if($getTransData->transaction_type == 2){

			$exactFilePath = base_path("public" . '/' . config('constants.dir.issue_vaucher')) . $getTransData->purchased_invoice;
			/*print_r($exactFilePath);exit;*/
		return response()->download($exactFilePath);
		}
		
	}

	public function editTransaction($id) {
		$transaction = MetalTransaction::where('id', $id)->first();
		$transactionTypes = TransactionType::pluck('name', 'id'); // where('id', '>', 1)->
		$metals = Metal::select();
		$role =Auth::user()->roles->first()->name;
		return view('metals.edit', compact('transaction', 'transactionTypes', 'metals','role'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {

		$transaction = MetalTransaction::find($id);
		$prevData = MetalTransaction::find($id);
		$user_id = Auth::user()->id;
		$this->validate($request, [
				'vendor_name' => 'required',
				'metal_weight' => 'required',
				'measurement' => 'required',
				'transaction_type' => 'required',
				'metal_rate' => 'required',
				'purchased_at' => 'required',
			]);
		$role =Auth::user()->roles->first()->name;
		if ($role == "Super Admin" && $request->has('purchased_invoice')) {
			$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			$exactFilePath = base_path("public" . '/' . config('constants.dir.purchased_invoices')) . $att_file;

			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
				$filename = $att_file->getClientOriginalName();
			
			if(!file_exists( $exactFilePath )) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		}else if($role !== "Super Admin" && $request->has('purchased_invoice')){
				$att_file = $request->file('purchased_invoice');
			$destinationPath = config('constants.dir.purchased_invoices');
			$exactFilePath = base_path("public" . '/' . config('constants.dir.purchased_invoices')) . $att_file;
			if (!is_dir($destinationPath)) {
				mkdir($destinationPath);
			}
				$filename = $att_file->getClientOriginalName();
			
			if (!file_exists( $exactFilePath )) {
				$att_file->move($destinationPath, $att_file->getClientOriginalName());
			}
		}else{
			$filename = "";
		}


		if ($request->purchased_invoice != null) {
			$purchased_invoice_file = request()->purchased_invoice;

			//Move Uploaded File
			$destinationPath = config('constants.dir.purchased_invoices');
			$filename = $purchased_invoice_file->getClientOriginalName();
			
			$purchased_invoice_file->move($destinationPath, $purchased_invoice_file->getClientOriginalName());
		} else {
			$filename = $transaction->purchased_invoice;
			$request['purchased_invoice'] = $filename;
		}

		$this->validate($request, [
			//'metal_type' => 'required',
			'metal_weight' => 'required',
			'measurement' => 'required',
			//'transaction_type' => 'required',
			'metal_rate' => 'required',
			//'purchased_invoice' => 'file|mimes:jpeg,jpg,png,pdf',
			'purchased_at' => 'required',
		]);

	
		
			
			$due_date = null;
			$amount_paid = ($request->input('metal_rate') * $request->input('metal_weight'));
			$advance_payment = $request->input('advance_payment');
			
				if(!$advance_payment == 1){
					$due_date = $request->input('due_date');
				}	

				$transaction->update(['metal_weight' => $request->input('metal_weight'), 'measurement' => $request->input('measurement'), 'purchased_at' => $request->input('purchased_at'), 'amount_paid' => $amount_paid, 'metal_rate' => $request->input('metal_rate'), 'purchased_invoice' => $filename, 'po_number' => $request->input('po_number'), 'comment' => $request->input('comment'),'advance_payment' => $request->input('advance_payment'),'gold_type' => $request->input('gold_type'),'invoice_number' => $request->input('invoice_number'),'due_date' => $due_date  ]);

				$requestData = $request->input();
				$requestData['metal_type'] = $transaction->metal_type;

				switch ($request->input('transaction_type')){
					case 1:
						$this->updategold($requestData, $prevData);
						break;

					default:
						$this->updategold($requestData, $prevData);
						break;
				}


				$role =Auth::user()->roles->first()->name;
		if($role == "Super Admin"  && $transaction->transaction_id == "" && $filename !== "" ){
			
			$paymentid= $this->addPayment($requestData,$filename,$transaction->transaction_id,$amount_paid,$advance_payment);
			$transaction->update(['transaction_id' => $paymentid]);
			return redirect()->route('metals.index')
			->with('success', 'Metal transaction updated successfully');
			
		}
		if($role == "Super Admin"  && !empty($transaction->transaction_id) || $filename !== "" ){
			
			$this->updatepayment($transaction, $filename, $transaction->transaction_id, $amount_paid,$advance_payment);
			
		}
		if($role !== "Super Admin"  && $filename !== ""){
			
			$this->updatepayment($transaction, $filename,$transaction->transaction_id, $amount_paid,$advance_payment);
		
		}	
	
		
	
		return redirect()->route('metals.index')
			->with('success', 'Metal transaction updated successfully');
	}

	public function updategold($requestData, $prevData) {

		//var_dump($requestData);

		//echo '<br><br>';
		//var_dump($prevData);exit;

		$metals = DB::table('metals')->where('metal_type', $requestData['metal_type'])->get();
		$metalsCount = count($metals);
		$metalTransactions = DB::table("metal_transactions")->where("metal_type", $requestData['metal_type'])->where("transaction_type", 1)->where("status", 1)->get();

		$metals = DB::table("metal_transactions")->where("metal_type", $requestData['metal_type'])->where("issue_voucher_no","!=",0)->where("status", 1)->get();
		
		$metal_weight_notissue	 = 0;
		$metal_issue = 0;
		$total_amount_paid = 0;
		foreach ($metals as $metalTranKey => $metalTran) {
			//var_dump($metalTran->metal_weight);
			if ($metalTran->measurement == 'mm') {
				$mweight = (float) $metalTran->metal_weight;
				$mweight = $mweight / 1000;
				$metal_weights += $mweight;
			} elseif ($metalTran->measurement == 'kg') {
				$mweight = (float) $metalTran->metal_weight;
				$mweight = $mweight * 1000;
				$metal_weights += $mweight;
			} else {
				$metal_issue += (float) $metalTran->metal_weight;
			}
			
		}
		foreach ($metalTransactions as $metalTranKey => $metalTran) {
			//var_dump($metalTran->metal_weight);
			if ($metalTran->measurement == 'mm') {
				$mweight = (float) $metalTran->metal_weight;
				$mweight = $mweight / 1000;
				$metal_weights += $mweight;
			} elseif ($metalTran->measurement == 'kg') {
				$mweight = (float) $metalTran->metal_weight;
				$mweight = $mweight * 1000;
				$metal_weights += $mweight;
			} else {
				$metal_weight_notissue += (float) $metalTran->metal_weight;
			}
			$total_amount_paid += (float) $metalTran->amount_paid;
		}
		$metal_weights = $metal_weight_notissue - $metal_issue;
		//var_dump($metal_weights);
		//var_dump($total_amount_paid);
		$avg_rate = round($total_amount_paid / ($metal_weights), 2);
		//var_dump($avg_rate);exit;

		if ($metalsCount > 0) {
			Metal::where('metal_type', $requestData['metal_type'])->update(['total_metal_weight' => $metal_weights, 'avg_rate' => $avg_rate]);

		} else {
			$Metal = Metal::create(['metal_type' => $requestData['metal_type'], 'total_metal_weight' => $metal_weights, 'avg_rate' => $avg_rate]);
		}

		return true;
	}

	public function updatepayment($requestData, $filename, $insertedIDTransaction, $payment_amt, $advance_payment) {


		$payment_type_coll = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first();
		//$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;

		if ($payment_type_coll) {
			$payment_type = $payment_type_coll->id;
		} else {
			return false;
		}

		$payment_sub_type_coll = PaymentType::select('id')->where('name', '=', 'Purchase')->first();
		if ($payment_sub_type_coll) {
			$payment_sub_type = $payment_sub_type_coll->id;
		} else {
			return false;
		}
		//$payment_sub_type = PaymentType::select('id')->where('name', '=', 'Purchase')->first()->id;

		if ($advance_payment == 1) {
			$account_status = 1;
		} else {
			$account_status = 0;
		}
		//$payment_upadte 

		 Payment::where('id',$insertedIDTransaction)->update([
			'customer_id' => $requestData['vendorId'],
			'customer_name' => $requestData['vendor_name'],
			'invoice_number' => $requestData['invoice_number'],
			'invoice_attachment' => $filename,
			'invoice_amount' => $payment_amt,
			'due_date' => $requestData['due_date'],
			'account_status' => $account_status,
			'payment_status' => '0',
			'payment_form' => 'Outgoing',
			'payment_type' => $payment_type,
			'payment_sub_type' => $payment_sub_type,
			'customer_type' => "System",
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'created_by' => Auth::User()->id,
			'remarks' => "Purchased from Gold Inventory"
		]);

		
	
			
	}
	public function addPayment($requestData, $filename, $insertedIDTransaction, $payment_amt, $advance_payment) {

		$lastTransactionId = Payment::select('transaction_id')->orderBy('transaction_id', 'desc')->first();
		if (!empty($lastTransactionId)) {
			$newTransactionId = ((int) $lastTransactionId->transaction_id) + 1;
		} else {
			$newTransactionId = (int) 100001701;
		}

		$payment_type_coll = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first();
		//$payment_type = PaymentType::select('id')->where('name', '=', 'Purchase Account')->first()->id;

		if ($payment_type_coll) {
			$payment_type = $payment_type_coll->id;
		} else {
			return false;
		}

		$payment_sub_type_coll = PaymentType::select('id')->where('name', '=', 'Purchase')->first();
		if ($payment_sub_type_coll) {
			$payment_sub_type = $payment_sub_type_coll->id;
		} else {
			return false;
		}
		//$payment_sub_type = PaymentType::select('id')->where('name', '=', 'Purchase')->first()->id;

		if ($advance_payment == 1) {
			$account_status = 1;
		} else {
			$account_status = 0;
		}
		if(!empty($requestData['vendorId']))
		{
			$vendor_id = $requestData['vendorId'];
			$name = $requestData['vendor_name'];
		}else{
			$vendor_id = $requestData->vendor_id;
			$name = Auth::User()->select('name')->where('id',$requestData->vendor_id)->value('name');
		}
		$data = array(
			'transaction_id' => $newTransactionId,
			'customer_id' => $vendor_id,
			'customer_name' => $name,
			'invoice_number' => $requestData['invoice_number'],
			'invoice_attachment' => $filename,
			'invoice_amount' => $payment_amt,
			'due_date' => $requestData['due_date'],
			'account_status' => $account_status,
			'payment_status' => '0',
			'payment_form' => 'Outgoing',
			'payment_type' => $payment_type,
			'payment_sub_type' => $payment_sub_type,
			'customer_type' => "System",
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			'created_by' => Auth::User()->id,
			'remarks' => "Purchased from Gold Inventory",
		);

			$Accountinsert = Payment::create($data);
			return  $Accountinsert->id;
		
		
			

	}


}