<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;

class PaymentTransaction extends Model
{
     use SoftDeletes;
    protected $guarded = [];
    public $table = 'payment_transaction';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

}
