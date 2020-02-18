<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiamondTransaction extends Model {
	//
	use SoftDeletes;
	public $table = "diamond_transactions";
	protected $dates = ['deleted_at']; 
	protected $fillable = [
		'packet_id',
		'stone_shape',
        'diamond_weight',
        'diamond_quality',
		'sieve_size',
		'mm_size',
        'transaction_type',
        'transaction_id',
		'amount_paid',
		'vendor_id',
		'invoice_number',
        'purchased_invoice',
        'user_id',
        'transaction_at',
        'po_number',
        'comment',
		'purchased_at',
		'due_date',
		'amount_paid_with_gst',
		'rate',
		'pieces',
		'custom_stone_quality',
		'custom_mm_size',
		'custom_sieve_size',
		'is_adjustable',
		'is_voucher_no_generated',
		'is_handover',
		'handover_at',
		'deleted_at',
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
}
