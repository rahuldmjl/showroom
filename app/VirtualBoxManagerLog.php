<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class VirtualBoxManagerLog extends Model
{
    protected $guarded = [];
    public $table = "vb_logs";
    protected $primaryKey = 'id';
    //public $incrementing = true;
    protected $fillable = [
     'vb_id', 'product_id', 'certificate_no','action', 'transaction_by','deleted_at','created_at','updated_at'
	];
}