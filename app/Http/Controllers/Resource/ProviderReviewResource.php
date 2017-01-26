<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\ProviderRating;

class ProviderReviewResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Reviews = ProviderRating::leftJoin('providers', 'provider_ratings.provider_id', '=', 'providers.id')
                ->leftJoin('users', 'provider_ratings.user_id', '=', 'users.id')
                ->select('provider_ratings.*', 'users.first_name as user_first_name', 'users.last_name as user_last_name', 
                            'providers.first_name as provider_first_name', 'providers.last_name as provider_last_name')
                ->orderBy('provider_ratings.created_at', 'desc')
                ->get();

        return view('admin.review.provider_review', compact('Reviews'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            ProviderRating::find($id)->delete();
            return back()->with('message', 'Rating deleted successfully');
        } 
        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'Rating Not Found');
        }
    }
}
