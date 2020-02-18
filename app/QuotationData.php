<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuotationData extends Model
{
    protected $guarded = [];
    public $table = 'quotation_data';
    protected $primaryKey = 'id';
}
