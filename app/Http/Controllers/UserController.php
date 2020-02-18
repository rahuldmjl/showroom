<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Validator;

class UserController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	function __construct() {

		$isAdmin = false;
		$this->middleware(function ($request, $next) {
			$isAdmin = Auth::user()->is_admin;

			if (!$isAdmin) {
				$this->middleware('permission:user-list');
				$this->middleware('permission:user-create', ['only' => ['create', 'store']]);
				$this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
				$this->middleware('permission:user-delete', ['only' => ['destroy']]);
			}
			return $next($request);

		});
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {

		$user = Auth::user();

		if ($user->hasRole('Super Admin') || $user->hasRole('User Manager')) {
			$maindata = User::whereHas('roles', function ($q) {$q->where('name', '<>', 'Super Admin');})->orderBy('id', 'DESC');

		} else {

			$maindata = User::where('created_by', $user->id)->whereHas('roles', function ($q) {$q->where('name', '<>', 'Super Admin');})->orderBy('id', 'DESC');
		}

		$datacount = $maindata->count();
		$data = $maindata->paginate(10);

		$roles = Role::select('name', 'id')->where('id', '>', 1)->pluck('name', 'id');

		return view('users.index', compact('data', 'roles', 'datacount'))
			->with('i', ($request->input('page', 1) - 1) * 10);
	}

	public function ajaxlist(Request $request) {

		$data = array();
		$params = $request->post();
		$start = (!empty($params['start']) ? $params['start'] : 0);
		$length = (!empty($params['length']) ? $params['length'] : 10);
		$stalen = $start / $length;
		$curpage = $stalen;
		$where = '';
		$offset = '';
		$limit = '';
		$order = $params['order'][0]['column'];
		$order_direc = strtoupper($params['order'][0]['dir']);
		if ($order == "1") {
			$order_by = 'name';
		} elseif ($order == "2") {
			$order_by = 'email';
		} else {
			$order_by = 'id';
		}

		$user = Auth::user();

		$maindata = User::query();

		if ($user->hasRole('Super Admin') || $user->hasRole('User Manager')) {
			$maindata = $maindata->whereHas('roles', function ($q) {$q->where('name', '<>', 'Super Admin');})->orderBy($order_by, $order_direc);
			//var_dump($maindata->toSql());
		} else {
			$maindata->where('created_by', $user->id)->whereHas('roles', function ($q) {$q->where('name', '<>', 'Super Admin');})->orderBy($order_by, $order_direc);
		}

		if (!empty($params['textfilter'])) {
			//$maindata->where('name', 'like', '%' . $params['textfilter'] . '%')->orWhere('email', 'like', '%' . $params['textfilter'] . '%');
			$maindata->whereRaw('(name like "%' . $params['textfilter'] . '%" or email like "%' . $params['textfilter'] . '%")');
		}
		if (!empty($params['rolefilter'])) {
			$maindata->whereHas('roles', function ($q) use ($params) {$q->where('name', $params['rolefilter']);});
		}

		if (!empty($params['adminfilter'])) {
			if ($params['adminfilter'] == "1") {
				$maindata = $maindata->where('is_admin', 1);
				//var_dump($maindata->toSql());exit;
			} elseif ($params['adminfilter'] == "2") {
				$maindata->where('is_admin', 0);
			}
			//echo '<pre>';
			//print_r($maindata->get());exit;
		}

		if (!empty($params['rolefilter'])) {
			$maindata->whereHas('roles', function ($q) use ($params) {$q->where('name', $params['rolefilter']);});
			//echo '<pre>';
			//print_r($maindata->get());exit;
		}

		//echo '<pre>';
		//print_r($maindata->get());exit;

		//echo $maindata->toSql();exit;
		$datacount = $maindata->count();
		$datacoll = $maindata;

		$datacollection = $datacoll->take($length)->offset($start)->get();
		//echo $datacount;exit;
		$data["draw"] = $params['draw'];
		$data["page"] = $curpage;
		//$data["query"] = $maindata->toSql();
		$data["recordsTotal"] = $datacount;
		$data["recordsFiltered"] = $datacount;
		$data['deferLoading'] = $datacount;
		$data['roles'] = Role::select('name', 'id')->where('id', '>', 1)->pluck('name', 'id');

		if (count($datacollection) > 0) {
			$count = 0;
			foreach ($datacollection as $key => $user) {
				$srno = $key + 1 + $start;
				$name = $user->name;
				$email = $user->email;
				$roles = '';

				if (!empty($user->getRoleNames())) {
					foreach ($user->getRoleNames() as $v) {
						$roles .= '<label class="badge badge-success">' . $v . '</label>';
					}
					$roles .= ($user->is_admin) ? '<i class="list-icon material-icons" title="Admin of Dept" style="cursor: pointer;">verified_user</i>' : '';
				}

				$actions = '<a class="color-content table-action-style" href="' . route('users.show', $user->id) . '" style="display: none;"><i class="material-icons md-18">show</i></a>&nbsp;';

				$actions .= '<a class="color-content table-action-style" href="' . route('users.edit', $user->id) . '"><i class="material-icons md-18">edit</i></a>&nbsp;';

				$actions .= '<a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleteuser(' . $user->id . ', \'' . csrf_token() . '\');" data-token="' . csrf_token() . '"><i class="material-icons md-18">delete</i></a>';

				$actions .= '<form method="POST" action="' . route('users.destroy', $user->id) . '" accept-charset="UTF-8" style="display:none" class="input-has-value"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' . csrf_token() . '"><input class="btn btn-danger" type="submit" value="Delete"></form>';

				$data['data'][] = array($srno, $name, $email, $roles, $actions);
			}
			//exit;
		} else {
			$data['data'][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
		}
		echo json_encode($data);exit;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//$roles = Role::pluck('name', 'name')->all();
		$user = Auth::user();

		if ($user->hasRole('Super Admin') || $user->hasRole('User Manager')) {
			$roles = Role::where('id', '>', 1)->orderBy('name', 'ASC')->pluck('name', 'name');

		} else {

			$userroles = array();
			foreach ($user->roles as $role) {
				$userroles[] = $role->name;
			}

			$roles = Role::whereIn('name', $userroles)->orderBy('name', 'ASC')->pluck('name', 'name');
		}

		return view('users.create', compact('roles'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$validate = $this->validate($request, [
			'name' => 'required',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|same:confirm_password',
			'roles' => 'required',
			//'state' => 'required',
			//'vendor_dmcode' => 'unique:users,vendor_dmcode',
		]);

		//var_dump($request->input('vendor_dmcode'));exit;
		//echo "<pre>";print_r($request->input('vendor_dmcode'));exit;
		//$dmcodeValid = "";
		if (!empty($request->input('vendor_dmcode'))) {
			$dmcodeValid = true;
			$checkDMcodsCnt = User::select('vendor_dmcode')->where('vendor_dmcode', $request->input('vendor_dmcode'))->count();
			if ($checkDMcodsCnt >= 1) {
				$checkDMcod = User::select('vendor_dmcode')->where('vendor_dmcode', $request->input('vendor_dmcode'))->get();
				//$DMcods = User::select('vendor_dmcode')->where('vendor_dmcode',$request->input('vendor_dmcode'))->get();

				if (count($checkDMcod->toArray()) > 0) {
					//echo 111;exit;
					if (strtolower($checkDMcod[0]['vendor_dmcode']) == strtolower($request->input('vendor_dmcode'))) {
						$forminputcod['vendor_dmcode'] = $request->input('vendor_dmcode');
						$rulesCod = array('vendor_dmcode' => 'unique:users,vendor_dmcode');
						$validator = Validator::make($forminputcod, $rulesCod);
						$dmcodeValid = false;
					}
				} else {
					$dmcodeValid = true;
				}

			}
		} else {
			$dmcodeValid = true;
		}

		if (!empty($request->input('gstin'))) {
			$dmcodeValid = true;
			$checkDMcodsCnt = User::select('gstin')->where('gstin', $request->input('gstin'))->count();
			if ($checkDMcodsCnt >= 1) {
				$checkDMcod = User::select('gstin')->where('gstin', $request->input('gstin'))->get();
				//$DMcods = User::select('vendor_dmcode')->where('vendor_dmcode',$request->input('vendor_dmcode'))->get();

				if (count($checkDMcod->toArray()) > 0) {
					//echo 111;exit;
					if (strtolower($checkDMcod[0]['gstin']) == strtolower($request->input('gstin'))) {
						$forminputcod['gstin'] = $request->input('gstin');
						$rulesCod = array('gstin' => 'unique:users,gstin');
						$validator = Validator::make($forminputcod, $rulesCod);
						$dmcodeValid = false;
					}
				} else {
					$dmcodeValid = true;
				}

			}
		} else {
			$dmcodeValid = true;
		}

		$input = $request->all();

		$forminput['is_admin'] = $request->input('is_admin');
		$admin_valid = true;
		if ($request->input('is_admin')) {
			$rules = array('is_admin' => 'unique:users,is_admin');
			$validator = Validator::make($forminput, $rules);
			$roles = $request->input('roles');
			$current_roles_users = User::whereHas('roles', function ($q) use ($roles) {$q->whereIn('name', $roles);})->get();
			foreach ($current_roles_users as $current_roles_user) {
				if ($current_roles_user->is_admin) {
					$admin_valid = false;
				}
			}
		}
		//echo $dmcodeValid;exit;
		//var_dump($dmcodeValid);exit;
		/*if ($dmcodeValid) {
			if ($admin_valid) {
				//echo "dfglkfmghgfkln";exit;
				$input['password'] = Hash::make($input['password']);
				$input['created_by'] = Auth::id();

				$user = User::create($input);

				$user->assignRole($request->input('roles'));

				return redirect()->route('users.index')->with('success', 'User created successfully');
			} else {
				//echo "DFggflhkmfgklhm";exit;
				$roles = Role::where('id', '>', 1)->orderBy('name', 'ASC')->pluck('name', 'name');

				return redirect()->route('users.create', ['roles' => $roles])->withErrors($validator)->withInput();
			}
		} else*/if ($admin_valid && $dmcodeValid) {
			$input['password'] = Hash::make($input['password']);
			$input['created_by'] = Auth::id();

			$user = User::create($input);

			$user->assignRole($request->input('roles'));

			return redirect()->route('users.index')->with('success', 'User created successfully');
		} else {
			$roles = Role::where('id', '>', 1)->orderBy('name', 'ASC')->pluck('name', 'name');

			return redirect()->route('users.create', ['roles' => $roles])->withErrors($validator)->withInput();
		}

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$user = User::find($id);
		return view('users.show', compact('user'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		$user = User::find($id);
		$authuser = Auth::user();

		if ($authuser->hasRole('Super Admin') || $authuser->hasRole('User Manager')) {
			$roles = Role::where('name', '<>', 'Super Admin')->orderBy('name', 'ASC')->pluck('name', 'name')->all();
		} else {
			$userroles = array();
			foreach ($authuser->roles as $role) {
				$userroles[] = $role->name;
			}

			$roles = Role::whereIn('name', $userroles)->orderBy('name', 'ASC')->pluck('name', 'name');
		}
		$userRole = $user->roles->pluck('name', 'name')->all();

		return view('users.edit', compact('user', 'roles', 'userRole'));
	}

	public function profile() {

		$user = Auth::user();
		return view('profile', compact('user', $user));
	}

	public function update_avatar(Request $request) {
		$request->validate([
			'name' => 'required|regex:/^[\pL\s\-]+$/u|max:255',
			'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:1999',
			//'phone' => 'required|numeric',
		]);
		$input = $request->all();
		$user = Auth::user();
		if ($request->hasFile('avatar')) {
			$avatarName = $user->id . '_avatar' . time() . '.' . request()->avatar->getClientOriginalExtension();
			$file = $request->avatar->move(base_path() . '/public/assets/images', $avatarName);
			$user->avatar = $avatarName;
			$user->update($input);

		}

		$user->phone = $request->input('phone');
		$user->update($input);

		$user->save();

		return back()
			->with('success', 'You have successfully upload image.');

	}

	public function removeavatar() {
		//echo "fdgkfmghlfgh";exit;
		$user = Auth::user();
		$user = Auth::user()->find($user->id);
		$avatar = $user->avatar;
		if ($avatar == 'user.jpg') {
			$user->save();
		} else {
			$avatarName = 'users.jpeg';
			/*print_r($avatarName);exit;*/
			$user->avatar = $avatarName;
			/*print_r($user->avatar);exit;*/
			$user->save();
		}
		return redirect()->route('profile')
			->with('success', 'User Avatar updated successfully');
	}
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		$this->validate($request, [
			'name' => 'required',
			'email' => 'required|email|unique:users,email,' . $id,
			'password' => 'same:confirm-password',
			'roles' => 'required',
		]);
		$dmcodeValid = true;
		$checkDMcodsCnt = User::select('vendor_dmcode')->where('vendor_dmcode', $request->input('vendor_dmcode'))->count();
		if ($checkDMcodsCnt >= 1) {
			$checkDMcod = User::select('vendor_dmcode')->where('id', $id)->where('vendor_dmcode', $request->input('vendor_dmcode'))->get();
			if (empty($checkDMcod->toArray())) {
				$DMcods = User::select('vendor_dmcode')->where('vendor_dmcode', $request->input('vendor_dmcode'))->get();
				//var_dump($DMcods->toArray());exit;
				if (count($DMcods->toArray()) > 0) {
					if (strtolower($DMcods[0]['vendor_dmcode']) == strtolower($request->input('vendor_dmcode'))) {
						$forminputcod['vendor_dmcode'] = $request->input('vendor_dmcode');
						$rulesCod = array('vendor_dmcode' => 'unique:users,vendor_dmcode');
						$validator = Validator::make($forminputcod, $rulesCod);
						$dmcodeValid = false;
					}
				} else {
					$dmcodeValid = true;
				}
			} else {
				if ($checkDMcod[0]['vendor_dmcode'] != $request->input('vendor_dmcode')) {
					$forminputcod['vendor_dmcode'] = $request->input('vendor_dmcode');
					$rulesCod = array('vendor_dmcode' => 'unique:users,vendor_dmcode');
					$validator = Validator::make($forminputcod, $rulesCod);
					$dmcodeValid = false;
				}
			}

		}

		$gstinValid = true;
		$checkgstinCnt = User::select('gstin')->where('gstin', $request->input('gstin'))->count();
		if ($checkgstinCnt >= 1) {
			$checkGstin = User::select('gstin')->where('id', $id)->where('gstin', $request->input('gstin'))->get();
			if (empty($checkGstin->toArray())) {
				$GstinVal = User::select('gstin')->where('gstin', $request->input('gstin'))->get();
				//var_dump($DMcods->toArray());exit;
				if (count($GstinVal->toArray()) > 0) {
					if (strtolower($GstinVal[0]['gstin']) == strtolower($request->input('gstin'))) {
						$forminputcod['gstin'] = $request->input('gstin');
						$rulesCod = array('gstin' => 'unique:users,gstin');
						$validator = Validator::make($forminputcod, $rulesCod);
						$gstinValid = false;
					}
				} else {
					$gstinValid = true;
				}
			} else {
				if ($checkGstin[0]['gstin'] != $request->input('gstin')) {
					$forminputcod['gstin'] = $request->input('gstin');
					$rulesCod = array('gstin' => 'unique:users,gstin');
					$validator = Validator::make($forminputcod, $rulesCod);
					$gstinValid = false;
				}
			}

		}

		/* $checkStateCnt = User::select('state')->where('state', $request->input('state'))->count();
			if ($checkStateCnt >= 1) {
				$checkState = User::select('state')->where('id', $id)->where('state', $request->input('state'))->get();
				if (empty($checkState->toArray())) {
					$StateVal = User::select('state')->where('state', $request->input('state'))->get();
					//var_dump($DMcods->toArray());exit;
					if (count($StateVal->toArray()) > 0) {
						if (strtolower($StateVal[0]['state']) == strtolower($request->input('state'))) {
							$forminputcod['state'] = $request->input('state');
							$rulesCod = array('state' => 'unique:users,state');
							$validator = Validator::make($forminputcod, $rulesCod);
							$dmcodeValid = false;
						}
					} else {
						$dmcodeValid = true;
					}
				} else {
					if ($StateVal[0]['state'] != $request->input('state')) {
						$forminputcod['state'] = $request->input('state');
						$rulesCod = array('state' => 'unique:users,state');
						$validator = Validator::make($forminputcod, $rulesCod);
						$dmcodeValid = false;
					}
				}

		*/

		$input = $request->all();

		$admin_valid = true;
		if ($request->input('is_admin')) {
			$forminput['is_admin'] = $request->input('is_admin');
			$rules = array('is_admin' => 'unique:users,is_admin');
			$validator = Validator::make($forminput, $rules);
			$roles = $request->input('roles');
			$current_roles_users = User::where('id', '<>', $id)->whereHas('roles', function ($q) use ($roles) {$q->whereIn('name', $roles);})->get();
			foreach ($current_roles_users as $current_roles_user) {
				if ($current_roles_user->is_admin) {
					$admin_valid = false;
				}
			}
		}

		if ($admin_valid && $dmcodeValid) {
			if (!empty($input['password'])) {
				$input['password'] = Hash::make($input['password']);
			} else {
				$input = array_except($input, array('password'));
			}

			if (empty($input['is_admin'])) {
				$input['is_admin'] = 0;
			}

			$user = User::find($id);

			//dd($input);exit;

			$user->update($input);
			DB::table('model_has_roles')->where('model_id', $id)->delete();

			$user->assignRole($request->input('roles'));

			return redirect()->route('users.index')
				->with('success', 'User updated successfully');
		} else {
			$user = User::find($id);
			$roles = Role::where('name', '<>', 'Super Admin')->orderBy('name', 'ASC')->pluck('name', 'name')->all();
			$userRole = $user->roles->pluck('name', 'name')->all();

			//return view('users.edit', compact('user', 'roles', 'userRole'));
			return redirect()->route('users.edit', ['user' => $user, 'roles' => $roles, 'userRole' => $userRole])->withErrors($validator)->withInput();
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		User::find($id)->delete();
		return response()->json([
			'success' => 'Record deleted successfully!',
		]);
		$return_data = array();

		$return_data['response'] = 'success';
		echo json_encode($return_data);exit;
		return redirect()->route('users.index')
			->with('success', 'User deleted successfully');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroyajax($id) {
		$deleted = User::find($id)->delete();

		$return_data = array();
		$return_data['response'] = 'success';
		echo json_encode($return_data);exit;
	}

	function filterdata(Request $request) {
		$ids = $request->data;
		$explodedIds = explode(",", $ids);
		$roleColls = Role::select('name', 'id')->whereIn('id', $explodedIds)->get();
		foreach ($roleColls as $key => $roleColl) {
			$name = $roleColl->name;
			if ($name == 'Vendor') {
				$data = User::whereHas('roles', function ($q) {$q->where('name', '=', 'Vendor');})->orderBy('id', 'DESC')->paginate();
				$roles = Role::select('name', 'id')->where('id', '=', $request->data)->pluck('name', 'id');
			}
			if ($name == 'User Manager') {
				$data = User::whereHas('roles', function ($q) {$q->where('name', '=', 'User Manager');})->orderBy('id', 'DESC')->paginate();
				$roles = Role::select('name', 'id')->where('id', '=', $request->data)->pluck('name', 'id');
			}
			if ($name == 'Role Manager') {
				$data = User::whereHas('roles', function ($q) {$q->where('name', '=', 'Role Manager');})->orderBy('id', 'DESC')->paginate();
				$roles = Role::select('name', 'id')->where('id', '=', $request->data)->pluck('name', 'id');

			}
			if ($name == 'Role Creator') {
				$data = User::whereHas('roles', function ($q) {$q->where('name', '=', 'Vendor');})->orderBy('id', 'DESC')->paginate();
				$roles = Role::select('name', 'id')->where('id', '=', $request->data)->pluck('name', 'id');
			}
			if ($name == 'Gold Manager') {
				$data = User::whereHas('roles', function ($q) {$q->where('name', '=', 'Gold Manager');})->orderBy('id', 'DESC')->paginate();
				$roles = Role::select('name', 'id')->where('id', '=', $request->data)->pluck('name', 'id');

			}
			if ($name == 'Diamond Manager') {
				$data = User::whereHas('roles', function ($q) {$q->where('name', '=', 'Diamond Manager');})->orderBy('id', 'DESC')->paginate();
				$roles = Role::select('name', 'id')->where('id', '=', $request->data)->pluck('name', 'id');

			}
			if ($name == 'Super Admin') {
				$data = User::whereHas('roles', function ($q) {$q->where('name', '=', 'Diamond Manager');})->orderBy('id', 'DESC')->paginate();
				$roles = Role::select('name', 'id')->where('id', '=', $request->data)->pluck('name', 'id');

			}
			return \Response::json(['user' => $data, 'roles' => $roleColls]);
		} //exit;

	}
}