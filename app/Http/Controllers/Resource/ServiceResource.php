<?php

namespace App\Http\Controllers\Resource;

use App\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Exception;

class ServiceResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $services = ServiceType::all();
        if($request->ajax()) {
            return $services;
        } else {
            return view('admin.service.index', compact('services'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.service.create');
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
            'name' => 'required|max:255',
            'provider_name' => 'required|max:255',
            'fixed' => 'required|numeric',
            'price' => 'required|numeric',
            'image' => 'mimes:ico,png'
        ]);

        try{

            $service = $request->all();

            if ($request->is_default === 'yes') {
                ServiceType::where('status', 1)->update(['status' => 0]);
                $service['status'] = 1;
            }else{
                $service['status'] = 0;
            }

            if($request->hasFile('image')) {
                $service['image'] = Helper::upload_picture($request->image);
            }

            $service = ServiceType::create($service);

            return back()->with('flash_success','Service Type Saved Successfully');

        } 

        catch (Exception $e) {
            return back()->with('flash_errors', 'Service Type Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return ServiceType::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $service = ServiceType::findOrFail($id);
            return view('admin.service.edit',compact('service'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'provider_name' => 'required|max:255',
            'fixed' => 'required|numeric',
            'price' => 'required|numeric',
            'image' => 'mimes:ico,png'
        ]);

        try {

            $service = ServiceType::findOrFail($id);

            if($request->hasFile('image')) {
                if($service->image) {
                    Helper::delete_picture($service->image);
                }
                $service->image = Helper::upload_picture($request->image);
            }

            if ($request->is_default === 'yes') {
                ServiceType::where('status', 1)->update(['status' => 0]);
                $service->status = 1;
            }

            $service->name = $request->name;
            $service->provider_name = $request->provider_name;
            $service->fixed = $request->fixed;
            $service->price = $request->price;
            $service->save();

            return redirect()->route('admin.service.index')->with('flash_success', 'Service Type Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'Service Type Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            ServiceType::find($id)->delete();
            return back()->with('message', 'Service Type deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_errors', 'Service Type Not Found');
        }
    }
}
