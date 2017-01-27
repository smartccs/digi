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
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($requests as $index => $request)
                        <tr>
                            <td>{{$index + 1}}</td>

                            <td>{{$request->id}}</td>
                            <td>{{$request->user_first_name}} {{$request->user_last_name}}</td>
                            <td>
                                @if($request->confirmed_provider)
                                    {{$request->provider_first_name}} {{$request->provider_last_name}}
                                @endif
                            </td>
                            <td>{{$request->created_at}}</td>
                            <td>
                                @if($request->status == 0) 
                                      New
                                @elseif($request->status == 1)
                                      Waiting
                                @elseif($request->status == 2)

                                  @if($request->provider_status == 0)
                                      Provider Not Found
                                  @elseif($request->provider_status == 1)
                                      Provider Accepted
                                  @elseif($request->provider_status == 2)
                                      Provider Started
                                  @elseif($request->provider_status == 3)
                                      Provider Arrived
                                  @elseif($request->provider_status == 4)
                                      Service Started
                                  @elseif($request->provider_status == 5)
                                      Service Completed
                                  @elseif($request->provider_status == 6)
                                      Provider Rated
                                  @endif

                                  @elseif($request->status == 3)

                                        Payment Pending
                                  @elseif($request->status == 4)

                                        Request Rating
                                  @elseif($request->status == 5)

                                        Request Completed
                                  @elseif($request->status == 6)

                                        Request Cancelled
                                  @elseif($request->status == 7)

                                        Provider Not Available
                                  @endif
                            </td>
                            <td>{{currency($request->amount)}}</td>
                            <td>
                                @if($request->payment_status == 0)
                                    Not Paid
                                @else
                                    Paid
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