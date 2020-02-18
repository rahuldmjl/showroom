<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApprovalMemoHistroy extends Model
{
    protected $guarded = [];
    public $table = 'approval_memo_histroy';
    protected $primaryKey = 'id';
	protected $fillable = [
      'approval_no','product_id', 'status', 'date','approval_memo_id'
	];
	public $timestamps = false;
}
