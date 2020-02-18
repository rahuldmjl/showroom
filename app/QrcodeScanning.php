<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QrcodeScanning extends Model {
	protected $guarded = [];
	public $table = "qrcode_scannings";
	protected $primaryKey = 'id';
}
