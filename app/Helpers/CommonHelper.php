<?php
namespace App\Helpers;
use App\Setting;
use DB;

class CommonHelper {

	public static function covertToCurrency($amount) {
		$decimal = (string) ($amount - floor($amount));
		$money = floor($amount);
		$length = strlen($money);
		$delimiter = '';
		$money = strrev($money);

		for ($i = 0; $i < $length; $i++) {
			if (($i == 3 || ($i > 3 && ($i - 1) % 2 == 0)) && $i != $length) {
				$delimiter .= ',';
			}
			$delimiter .= $money[$i];
		}

		$result = strrev($delimiter);
		$decimal = preg_replace("/0\./i", ".", $decimal);
		$decimal = substr($decimal, 0, 3);

		if ($decimal != '0') {
			$result = $result . $decimal;
		}

		return '&#x20b9;' . ' ' . $result;
	}

	public static function ExecuteRawQuery($query) {
		return DB::select(DB::raw($query));
	}

	public static function getEavAttributeOptionValue($option_id) {
		$option_value = DB::select("SELECT value FROM eav_attribute_option_value WHERE option_id = " . $option_id);
		return $option_value;
	}
	public static function getEavAttributeOptionId($value) {
		$option_value = DB::select("SELECT option_id FROM eav_attribute_option_value WHERE value = '" . $value . "'");
		return $option_value;
	}

	public static function getWithGSTValue($value) {
		if (!empty($value)) {
			$value = (float) $value;
			$gstPercentage = Setting::where('key', config('constants.settings.keys.igst_percentage'))->first('value');
			$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			$withGstValue = $value + $gstValue;
			return $withGstValue;
		} else {
			return false;
		}
	}

	public static function getWithoutGSTValue($value) {
		if (!empty($value)) {
			$value = (float) $value;
			$gstPercentage = Setting::where('key', config('constants.settings.keys.igst_percentage'))->first('value');
			$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			$withoutGstValue = $value - $gstValue;
			return $withoutGstValue;
		} else {
			return false;
		}
	}

	public static function getGSTValue($value) {
		if (!empty($value)) {
			$value = (float) $value;
			$gstPercentage = Setting::where('key', config('constants.settings.keys.igst_percentage'))->first('value');
			$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			return $gstValue;
		} else {
			return false;
		}
	}
	
	public static function getWithSGSTValue($value) {
		if (!empty($value)) {
			$value = (float) $value;
			$gstPercentage = Setting::where('key', config('constants.settings.keys.sgst_percentage'))->first('value');
			$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			$withGstValue = $value + $gstValue;
			return $withGstValue;
		} else {
			return false;
		}
	}

	public static function getWithoutSGSTValue($value) {
		if (!empty($value)) {
			$value = (float) $value;
			$gstPercentage = Setting::where('key', config('constants.settings.keys.sgst_percentage'))->first('value');
			$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			$withoutGstValue = $value - $gstValue;
			return $withoutGstValue;
		} else {
			return false;
		}
	}

	public static function getSGSTValue($value) {
		if (!empty($value)) {
			$value = (float) $value;
			$gstPercentage = Setting::where('key', config('constants.settings.keys.sgst_percentage'))->first('value');
			$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			return $gstValue;
		} else {
			return false;
		}
	}
	
	public static function getWithCGSTValue($value) {
		if (!empty($value)) {
			$value = (float) $value;
			$gstPercentage = Setting::where('key', config('constants.settings.keys.cgst_percentage'))->first('value');
			$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			$withGstValue = $value + $gstValue;
			return $withGstValue;
		} else {
			return false;
		}
	}

	public static function getWithoutCGSTValue($value) {
		if (!empty($value)) {
			$value = (float) $value;
			$gstPercentage = Setting::where('key', config('constants.settings.keys.cgst_percentage'))->first('value');
			$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			$withoutGstValue = $value - $gstValue;
			return $withoutGstValue;
		} else {
			return false;
		}
	}

	public static function getCGSTValue($value) {
		if (!empty($value)) {
			$value = (float) $value;
			$gstPercentage = Setting::where('key', config('constants.settings.keys.cgst_percentage'))->first('value');
			$gstPercentage = (float) isset($gstPercentage->value) ? $gstPercentage->value : 0;
			$gstValue = (float) (($value * $gstPercentage) / 100);
			return $gstValue;
		} else {
			return false;
		}
	}

}
