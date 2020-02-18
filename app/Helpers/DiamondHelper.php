<?php
namespace App\Helpers;
use App\DiamondTransaction;
use App\MetalTransaction;
use App\Products;
use App\Setting;
use Illuminate\Support\Facades\DB;

class DiamondHelper {

	public static function getCurrentIssueVoucherNo() {
		$year = date("Y");
		$lyear = substr($year, -2);
		$year1 = date("Y", strtotime("+1 year"));
		$cyear = substr($year1, -2);
		$financialyear = $lyear . '-' . $cyear;

		$prifix = Setting::where('key', config('constants.settings.keys.diamond_issue_voucher_prifix'))->get();
		$series = Setting::where('key', config('constants.settings.keys.diamond_voucher_series'))->get();
		foreach ($prifix as $data) {
			$prifix = $data->value;

			# code...
		}
		foreach ($series as $data) {
			$series = $data->value;

			# code...
		}
		return $prifix . $financialyear . "-" . $series;
	}

	public static function getGoldIssueVoucherNo() {

		$year = date("Y");
		$lyear = substr($year, -2);
		$year1 = date("Y", strtotime("+1 year"));
		$cyear = substr($year1, -2);
		$financialyear = $lyear . '-' . $cyear;

		$prifix = Setting::where('key', config('constants.settings.keys.issue_voucher_prifix'))->get();
		$series = Setting::where('key', config('constants.settings.keys.gold_voucher_series'))->get();
		foreach ($prifix as $data) {
			$prifix = $data->value;

			# code...
		}
		foreach ($series as $data) {
			$series = $data->value;

			# code...
		}
		return $prifix . $financialyear . "/" . $series;
	}

	public static function isIssueVoucherNoExist($issue_voucher_no) {
		//print_r($issue_voucher_no);exit;
		$search_voucher = substr($issue_voucher_no, -5);

		$data = DiamondTransaction::select('issue_voucher_no')->whereRaw('SUBSTRING(issue_voucher_no, -5) = ' . $search_voucher)->get();
		//echo "<pre>"; print_r($data[0]);exit;
		if (empty($data[0])) {
			//echo "true";exit;
			return true;

		} else {
			//echo "false";exit;
			return false;

		}

	}
	public static function isGoldVoucherNoExist($issue_voucher_no) {

		$search_voucher = substr($issue_voucher_no, -5);

		$data = MetalTransaction::select('issue_voucher_no')->whereRaw('SUBSTRING(issue_voucher_no, -5) = ' . $search_voucher)->get();

		if (empty($data[0])) {

			return true;

		} else {
			return false;

		}

	}

	public static function getSideStoneData($product_id, $certificate, $quality) {
		//var_dump($product_id);
		//var_dump($quality);
		if ($quality == '') {
			$quality = "SI-IJ";
		}
		$product_stone_price = array();
		$product_stone_weight = array();

		$dmlProductData = Products::with(['metals', 'stones', 'categorys'])->select('id', 'rts_stone_quality', 'certificate_no')->where('certificate_no', '=', DB::raw("'$certificate'"))->get()->first();

		if (count($dmlProductData) > 0) {

			if (count($dmlProductData->stones->toArray()) > 0) {
				$product_side_stone = $dmlProductData->stones->toArray();
			} else {
				$product_side_stone = DB::select("SELECT * FROM grp_stone WHERE stone_product_id = " . $product_id)->toArray();
			}
		} else {
			$product_side_stone = DB::select("SELECT * FROM grp_stone WHERE stone_product_id = " . $product_id);
		}

		//var_dump($product_side_stone);exit;
		$side_stone_data = array();
		foreach ($product_side_stone as $side_stone) {
			$side_stone = (array) $side_stone;
			//var_dump($side_stone);exit;

			$SideStoneType = CommonHelper::getEavAttributeOptionValue($side_stone['stone_type']);
			$SideStoneSubType = CommonHelper::getEavAttributeOptionValue($side_stone['stone_subtype']);
			$SideStoneShape = CommonHelper::getEavAttributeOptionValue($side_stone['stone_shape']);

			if (!empty($side_stone->stone_clarity)) {
				$SideStoneClarity = $side_stone['stone_clarity'];
				$SideStoneClarityText = CommonHelper::getEavAttributeOptionValue($side_stone['stone_clarity']);
			} else {
				$SideStoneClarity = CommonHelper::getEavAttributeOptionId($quality);
				$SideStoneClarityText = $quality;
			}

			$stone_total_carat = $side_stone['carat'];
			$stone_tot_carat = number_format(floatval(($stone_total_carat / $side_stone['stone_use'])), 3);
		}

		echo "fin";exit;
	}

}
