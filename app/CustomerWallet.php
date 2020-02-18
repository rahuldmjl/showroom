<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerWallet extends Model
{
    protected $guarded = [];
    public $table = 'customer_wallet';
    protected $primaryKey = 'id';
}
