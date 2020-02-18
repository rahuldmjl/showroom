<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApprovalMemo extends Model
{
    protected $guarded = [];
    public $table = 'approval_memo';
    protected $primaryKey = 'id';
	protected $fillable = [
      'customer_id','approval_no', 'product_ids', 'approval_type','deposit_type', 'status','is_for_old_data','invoice_no','agent_name','franchisee_id','created_at','updated_at'
	];
}
