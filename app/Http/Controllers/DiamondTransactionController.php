<?php

namespace App\Http\Controllers;

use App\DiamondTransaction;
use App\Http\Controllers\Controller;

class DiamondTransactionController extends Controller {

	public function downloadInvoice($id) {
		$getTransData = DiamondTransaction::where('id', $id)->first();
		$exactFilePath = base_path("public" . '/' . config('constants.dir.purchased_invoices')) . '/' . $getTransData->purchased_invoice;
		if (file_exists($exactFilePath)) {
			return response()->download($exactFilePath);
		} else {
			return redirect()->route('diamond.transactions', $diamonds->id)->with('error', $message);
		}
	}
}