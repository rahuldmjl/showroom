<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Setting;
use Auth;
use Db;
use Illuminate\Http\Request;

class SettingController extends Controller {

	public function index() {
		$settings = Setting::all();
		$settingsData = array();
		foreach ($settings as $setKey => $setValue) {
			$settingsData[$setValue->key] = $setValue->value;
		}
		//echo '<pre>';
		//print_r($settingsData);
		//echo '</pre>';exit;
		return view('setting.index')->with('settings', $settingsData);
	}

	public function setsettings(Request $request) {
		$postData = $request->post('settings');
		//echo '<pre>';
		//print_r($postData);exit;
		$user = Auth::user();
		$settings = Setting::all();
		$settingsData = array();
		foreach ($settings as $setKey => $setValue) {
			$settingsData[$setValue->key] = $setValue->id;
		}
		//var_dump($settings);exit;
		foreach ($postData as $postKey => $postValue) {
			if (array_key_exists($postKey, $settingsData)) {
				$setting = Setting::find($settingsData[$postKey]);
				$setting->modified_by = $user->id;
				$setting->value = $postValue;
			} else {
				$setting = new Setting;
				$setting->key = $postKey;
				$setting->value = $postValue;
				$setting->created_by = $user->id;
				$setting->modified_by = $user->id;
			}
			$setting->save();
			if ($postKey == 'INVOICE_INCREMENT_ID_PREFIX') {
				$sql = "select eav_entity_store.entity_type_id from eav_entity_store join eav_entity_type on eav_entity_type.entity_type_id = eav_entity_store.entity_type_id where eav_entity_type.entity_type_code = 'invoice' and eav_entity_store.store_id=1";
				$eavEntityStore = DB::select($sql);
				$eavEntityId = $eavEntityStore[0]->entity_type_id;
				$currentYear = date('y');

				$updateEavSql = "update eav_entity_store set increment_prefix='" . $postValue . '/' . $currentYear . '-' . ($currentYear + 1) . '/' . "' where entity_type_id=" . $eavEntityId;
				DB::statement($updateEavSql);
			}
		}

		return redirect()->route('settings')
			->with('success', config('constants.message.settings_saved'));

	}

	public function clearcache($type) {
		//var_dump($type);exit;

		if ($type == 'Application') {
			$exitCode = Artisan::call('cache:clear');
			echo 'Application cache cleared';exit;
		} elseif ($type == 'Config') {
			$exitCode = Artisan::call('config:cache');
			echo 'Config cache cleared';exit;
		} elseif ($type == 'Route') {
			$exitCode = Artisan::call('route:clear');
			echo 'Route cache cleared';exit;
		} elseif ($type == 'View') {
			$exitCode = Artisan::call('view:clear');
			echo 'View cache cleared';exit;
		} else {
			$exitCode1 = Artisan::call('cache:clear');
			$exitCode2 = Artisan::call('config:clear');
			$exitCode3 = Artisan::call('route:clear');
			$exitCode4 = Artisan::call('view:clear');
			echo 'All cache cleared';exit;
		}

	}

	public function clear_application_cache() {
		$exitCode = \Artisan::call('cache:clear');
		$data = array();
		$data['status'] = 'success';
		$data['msg'] = 'Application cache cleared';
		echo json_encode($data);exit;
	}

	public function clear_config_cache() {
		$exitCode = \Artisan::call('config:cache');
		$data = array();
		$data['status'] = 'success';
		$data['msg'] = 'Config cache cleared';
		echo json_encode($data);exit;
	}

	public function clear_route_cache() {
		$exitCode = \Artisan::call('route:clear');
		$data = array();
		$data['status'] = 'success';
		$data['msg'] = 'Route cache cleared';
		echo json_encode($data);exit;
	}

	public function clear_view_cache() {
		$exitCode = \Artisan::call('view:clear');
		$data = array();
		$data['status'] = 'success';
		$data['msg'] = 'View cache cleared';
		echo json_encode($data);exit;
	}

	public function clear_all_cache() {
		$exitCode1 = \Artisan::call('cache:clear');
		$exitCode2 = \Artisan::call('config:cache');
		$exitCode3 = \Artisan::call('route:clear');
		$exitCode4 = \Artisan::call('view:clear');
		$data = array();
		$data['status'] = 'success';
		$data['msg'] = 'All cache cleared';
		echo json_encode($data);exit;
	}

	public function backup_dng() {
		//var_dump(config('database.connections.mysql.database'));
		//var_dump(DB::connection()->getDatabaseName());exit;
		//DB::connection()->getDatabaseName()
		//var_dump(DB::connection());exit;
		$dbhost = config('database.connections.mysql.host');
		$dbuser = config('database.connections.mysql.username');
		$dbpass = config('database.connections.mysql.password');

		$dbname = config('database.connections.mysql.database');

		$backup_file = storage_path($dbname . '_' . date("Y-m-d-H-i-s") . '.sql');
		//File::put($backup_file, '');
		//$command = "mysqldump --opt -h $dbhost -u $dbuser -p $dbpass " . "dmlsoftware dml_diamond_inventorys | gzip > $backup_file";
		//$command = "mysqldump -p --user=$dbuser $dbpass " . "$dbname dml_diamond_inventorys | zip > $backup_file";
		//$command = "mysqldump -u=$dbuser -p=$dbpass $dbname dml_diamond_inventorys > $backup_file";

		//$backup_file = $dbname . '_' . date("Y-m-d-H-i-s") . '.gz';
		//$command = "mysqldump --opt -h $dbhost -u $dbuser -p $dbpass " . "$dbname dml_diamond_inventorys  | gzip > $backup_file";

		//  echo $command;exit;

		/*$return_var = NULL;
			$output = NULL;
			$command = "/usr/bin/mysqldump -u $dbuser -h $dbhost -p$dbpass $dbname dml_diamond_inventorys > $backup_file";
		*/

		//$cmdresult = system($command);
		//var_dump($cmdresult);

		/*$conn = mysql_connect($dbhost, $dbuser, $dbpass);

			if (!$conn) {
				die('Could not connect: ' . mysql_error());
		*/

		//$table_name = "dml_diamond_inventorys";
		//$backup_file  = "/tmp/employee.sql";
		//$sql = "LOAD DATA INFILE '$backup_file' INTO TABLE $table_name";
		//$sql = "SELECT * INTO OUTFILE '$backup_file' FROM $table_name";

		//mysql_select_db($dbname);
		//$retval = mysql_query($sql, $conn);
		//$retval = DB::statement($sql);

		//$pdo = DB::connection()->getPdo();
		//$pdo->exec($sql);
		//DB::statement($sql);

		/*if (!$retval) {
			die('Could not load data : ' . mysql_error());
		}*/
		//echo "Loaded  data successfully\n";

		//mysql_close($conn);

		\Spatie\DbDumper\Databases\MySql::create()
			->setDbName($dbname)
			->setUserName($dbuser)
			->setPassword($dbpass)
			->includeTables(['dml_diamond_inventorys'])
			->dumpToFile($backup_file);
		exit;
	}
}
