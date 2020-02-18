<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashVoucher extends Model
{
    protected $guarded = [];
    public $table = 'cash_voucher';
    protected $primaryKey = 'id';
}
