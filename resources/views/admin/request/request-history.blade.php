@extends('admin.layout.base')

@section('title', 'Request History ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            
            <div class="box box-block bg-white">
                <h5 class="mb-1">Request History</h5>
                @if(count($requests) != 0)
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Request Id</th>
                            <th>User Name</th>
                            <th>Provider Name</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Payment Mode</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($requests as $index => $request)
                        <tr>
                            <td>{{$index + 1}}</td>

                            <td>{{$request->id}}</td>
                            <td>{{$request->user->first_name}} {{$request->user->last_name}}</td>
                            <td>
                                @if($request->provider_id)
                                    {{$request->provider->first_name}} {{$request->provider->last_name}}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{$request->created_at}}</td>
                            <td>
                                {{$request->status}}
                            </td>
                            <td>
                                @if($request->payment != "")
                                    {{currency($request->payment->total)}}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{$request->payment_mode}}</td>
                            <td>
                                @if($request->paid)
                                    Paid
                                @else
                                    Not Paid
                                @endif
                            </td>
                            <td>
                                <div class="input-group-btn">
                                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">Action
                                    <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ route('admin.request.details', $request->id) }}" class="btn btn-default"><i class="fa fa-search"></i> More Details</a>
                                    </li>
                                    @if($request->status == 5)
                                    <li>
                                        <a href="{{ route('admin.chat', $request->id) }}" class="btn btn-default"><i class="fa fa-comments"></i> Chat History</a>
                                    </li>
                                    @endif
                                  </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Request Id</th>
                            <th>User Name</th>
                            <th>Provider Name</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Payment Mode</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
                @else
                    <h6 class="no-result">No results found</h6>
                @endif 
            </div>
            
        </div>
    </div>
@endsection