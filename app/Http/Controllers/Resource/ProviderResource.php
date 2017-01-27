<?php

namespace App\Http\Controllers\Resource;

use DB;
use App\Provider;
use Illuminate\Http\Request;
use App\UserRequests;
use App\ProviderService;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;

class ProviderResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $total_requests = UserRequests::select(DB::raw('count(*)'))
                        ->whereRaw('confirmed_provider = providers.id and status != 0');

        $accepted_requests = UserRequests::select(DB::raw('count(*)'))
                        ->whereRaw('confirmed_provider = providers.id and status in (1,2,3,4,5)');

        $providers = Provider::select(
                    'providers.*', 
                    DB::raw("(" . $total_requests->toSql() . ") as 'total_requests'"), 
                    DB::raw("(" . $accepted_requests->toSql() . ") as 'accepted_requests'"),
                    'provider_services.service_type_id as service_type_id')
                ->leftJoin('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->orderBy('providers.id', 'DESC')
                ->get();

        return view('admin.providers.index', compact('providers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.providers.create');
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
            'email' => 'required|unique:providers,email|email|max:255',
            'mobile' => 'digits_between:6,13',
            'address' => 'max:300',
            'zipcode' => 'max:300',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'gender' => 'required|in:male,female,others',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

            $provider = $request->all();

            $provider['is_available'] = 1;
            $provider['is_activated'] = 1;
            $provider['is_approved'] = 1;
            $provider['password'] = bcrypt($request->password);
            if($request->hasFile('picture')) {
                $provider['picture'] = Helper::upload_avatar($request->picture);
            }

            $provider = Provider::create($provider);

            return back()->with('flash_success','Provider Details Saved Successfully');

        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'Provider Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $provider = Provider::findOrFail($id);
            return view('admin.providers.provider-details', compact('provider'));
        } catch (Execption $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $provider = Provider::findOrFail($id);
            return view('admin.providers.edit',compact('provider'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Provider  $provider
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

            $provider = Provider::findOrFail($id);

            if($request->hasFile('picture')) {
                if($provider->picture) {
                    Helper::delete_avatar($provider->picture);
                }
                $provider->picture = Helper::upload_avatar($request->picture);
            }

            $provider->first_name = $request->first_name;
            $provider->last_name = $request->last_name;
            $provider->mobile = $request->mobile;
            $provider->address = $request->address;
            $provider->zipcode = $request->zipcode;
            $provider->gender = $request->gender;
            $provider->save();

            return redirect()->route('admin.provider.index')->with('flash_success', 'Provider Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'Provider Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Provider::find($id)->delete();
            return back()->with('message', 'Provider deleted successfully');
        } 
        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'Provider Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        Provider::where('id',$id)->update(['is_approved' => 1]);
        return back()->with('flash_success', "Provider Approved");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function decline($id)
    {
        Provider::where('id',$id)->update(['is_approved' => 0]);
        return back()->with('flash_success', "Provider Declined");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function provider_request_details($id){

        try{

            $requests = UserRequests::where('user_requests.confirmed_provider',$id)
                    ->RequestHistory()
                    ->get();

            return view('admin.request.request-history', compact('requests'));
        }

        catch (ModelNotFoundException $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }
}
