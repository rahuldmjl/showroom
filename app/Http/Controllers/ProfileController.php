<?php

namespace App\Http\Controllers;

use App\User;
use Auth;
use Hash;
use Illuminate\Http\Request;

class ProfileController extends Controller {
	public function showChangePasswordForm() {
		return view('profile.password');
	}
	public function changePassword(Request $request) {

		$this->validate($request, [
			'current-password' => 'required',
			'new-password' => 'required',
		]);
		if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
			// The passwords matches
			return redirect()->back()->with("error", Config::get('constants.message.vendor_current_password_error'));
		}
		if (strcmp($request->get('current-password'), $request->get('new-password')) == 0) {
			//Current password and new password are same
			return redirect()->back()->with("error", Config::get('constants.message.vendor_new_password_error'));
		}
		$validatedData = $request->validate([
			'current-password' => 'required',
			'new-password' => 'required|string|min:6|confirmed',
		]);
		//Change Password
		$user = Auth::user();
		$user->password = bcrypt($request->get('new-password'));
		$user->save();

		/*  return redirect()->route('Profile.changepassword')
            ->with('success', 'User created successfully');*/
		return redirect()->back()->with("success", config('constants.message.vendor_new_password_success'));
	}
}
