<?php

namespace App\Http\Controllers\Resource;

use App\User;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;

class UserResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('created_at' , 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|unique:users,email|email|max:255',
            'mobile' => 'digits_between:6,13',
            'address' => 'max:300',
            'zipcode' => 'max:300',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'gender' => 'required|in:male,female,others',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

            $user = $request->all();

            $user['payment_mode'] = 'cod';
            $user['password'] = bcrypt($request->password);
            if($request->hasFile('picture')) {
                $user['picture'] = Helper::upload_avatar($request->picture);
            }

            $user = User::create($user);

            return back()->with('flash_success','User Details Saved Successfully');

        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'User Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return User::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.edit',compact('user'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
            'address' => 'max:300',
            'zipcode' => 'max:300',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'gender' => 'required|in:male,female,others',
        ]);

        try {

            $user = User::findOrFail($id);

            if($request->hasFile('picture')) {
                if($user->picture) {
                    Helper::delete_avatar($user->picture);
                }
                $user->picture = Helper::upload_avatar($request->picture);
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->mobile = $request->mobile;
            $user->address = $request->address;
            $user->zipcode = $request->zipcode;
            $user->gender = $request->gender;
            $user->save();

            return redirect()->route('admin.user.index')->with('flash_success', 'User Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'User Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            User::find($id)->delete();
            return back()->with('message', 'User deleted successfully');
        } 
        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'User Not Found');
        }
    }

}
