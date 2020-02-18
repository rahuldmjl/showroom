<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerQuotationRate extends Model
{
    protected $guarded = [];
    public $table = 'customer_quotation_rate';
    protected $primaryKey = 'id';
}
