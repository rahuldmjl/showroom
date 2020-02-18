<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class MetalTransaction extends Model {
	//
	public $table = "metal_transactions";
	use SoftDeletes;
 	protected $dates = ['deleted_at']; 
	protected $fillable = [
		'metal_type', 'metal_weight', 'measurement', 'transaction_type', 'amount_paid', 'metal_rate', 'purchased_invoice', 'user_id', 'transaction_at', 'transaction_id', 'due_date', 'invoice_number', 'vendor_id', 'po_number', 'comment', 'purchased_at','gold_type','advance_payment','issue_date','created_by','updated_by','issue_voucher_no','is_voucher_no_generated','is_handover','handover_at',
	];

	/**
	 * Get the phone record associated with the user.
	 */
	public function transaction_type_value() {
		return $this->belongsTo('App\TransactionType', 'transaction_type');
		//return $this->hasOne('App\TransactionType', 'id');
	}

	/**
	 * Get the phone record associated with the user.
	 */
	public function transaction_by() {
		return $this->belongsTo('App\User', 'user_id');
		//return $this->hasOne('App\TransactionType', 'id');
	}

	/**
	 * Get the phone record associated with the user.
	 */
	public function vendor() {
		return $this->belongsTo('App\User', 'vendor_id');
	}
}
