<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	function __construct() {
		$this->middleware('permission:role-list');
		$this->middleware('permission:role-create', ['only' => ['create', 'store']]);
		$this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
		$this->middleware('permission:role-delete', ['only' => ['destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$roles = Role::orderBy('id', 'DESC')->paginate(10);


		$totalcount =  Role::orderBy('id', 'DESC')->count();
		return view('roles.index', compact('roles','totalcount'))
			->with('i', ($request->input('page', 1) - 1) * 5);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		$permission = Permission::get();
		 $roles=Role::select();
		
		return view('roles.create', compact('permission','roles'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$this->validate($request, [
			'name' => 'required|unique:roles,name',
			'permission' => 'required',
		]);

		$role = Role::create(['name' => $request->input('name')]);
		$role->syncPermissions($request->input('permission'));

		return redirect()->route('roles.index')
			->with('success', 'Role created successfully');
	}
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$role = Role::find($id);
		$rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
			->where("role_has_permissions.role_id", $id)
			->get();

		return view('roles.show', compact('role', 'rolePermissions'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		$role = Role::find($id);
		$permission = Permission::get();
		$rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
			->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
			->all();

		return view('roles.edit', compact('role', 'permission', 'rolePermissions'));
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
			'permission' => 'required',
		]);

		$role = Role::find($id);
		$role->name = $request->input('name');
		$role->save();

		$role->syncPermissions($request->input('permission'));

		return redirect()->route('roles.index')
			->with('success', 'Role updated successfully');
	}
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		DB::table("roles")->where('id', $id)->delete();
		return response()->json([
			'success' => 'Record deleted successfully!',
		]);
		return redirect()->route('roles.index')
			->with('success', 'Role deleted successfully');
	}

	public function roleresponse(Request $request){
		//print_r($request->all());exit;

		$totalData = Role::orderBy('id', 'DESC')->count();
		$totalFiltered = $totalData;
		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $request['order'][0]['column'];
		$order_direc = strtoupper($request['order'][0]['dir']);
		if ($order == "1") {
			$order_by = 'name';
		} else {
			$order_by = 'id';
		}
		if (empty($request->input('search.value'))) {
			//$roleslist = Role::orderBy('id', 'DESC')->offset($start)->limit($limit)->get();
			$roleslist = Role::orderBy($order_by, $order_direc)->offset($start)->limit($limit)->get();
		} else {
			$search = $request->input('search.value');
			$roleslist = Role::orderBy('id', 'DESC')->where('name', 'LIKE', "%{$search}%")->offset($start)->limit($limit)->get();
			$totalFiltered = Role::orderBy('id', 'DESC')->where('name', 'LIKE', "%{$search}%")->count();
		}
		//echo "<pre>";print_r($roleslist->toArray());exit;
		$data = array();
		if (!empty($roleslist)) {
			//$count = 1;
			foreach ($roleslist as $roles) {
				
					$action = '<a class="color-content table-action-style" href="'.route('roles.edit',$roles->id).'"><i class="material-icons md-18">edit</i></a> ';
				
				
					$action .= ' <a class="color-content table-action-style" href="javascript:void(0);" onclick="event.preventDefault();deleterole('.$roles->id.', '. csrf_token() .');" data-token="{{ csrf_token() }}"><i class="material-icons md-18">delete</i></a>';

                                    
				//$data[] = array(++$start, $roles->name,$action);
				$data[] = array($roles->id, $roles->name,$action);
				
			}
		}

		$json_data = array(
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data" => $data,
		);
		echo json_encode($json_data);
	}
}