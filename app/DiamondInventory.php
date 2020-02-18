<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiamondInventory extends Model
{
	protected $guarded = [];
    protected $table = 'diamond_inventorys';
    protected $dates = ['deleted_at']; 
}
