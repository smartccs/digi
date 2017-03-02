@extends('admin.layout.base')

@section('title', 'Providers ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">Providers</h5>
            <a href="{{ route('admin.provider.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Provider</a>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Total Requests</th>
                        <th>Accepted Requests</th>
                        <th>Cancelled Requests</th>
                        <th>Pending Review</th>
                        <th>Online</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($providers as $index => $provider)
                    <tr>
                        <td>{{$index + 1}}</td>
                        <td>{{$provider->first_name}} {{$provider->last_name}}</td>
                        <td>{{$provider->email}}</td>
                        <td>{{$provider->mobile}}</td>
                        <td>{{$provider->total_requests}}</td>
                        <td>{{$provider->accepted_requests}}</td>
                        <td>{{$provider->total_requests - $provider->accepted_requests }}</td>
                        <td>
                            @if($provider->pending_documents() > 0)
                                <a class="btn btn-danger label-right" href="{{route('admin.provider.document.index', $provider->id )}}">Yes <span class="btn-label">{{ $provider->pending_documents() }}</span></a>
                            @else
                                <a class="btn btn-success" href="{{route('admin.provider.document.index', $provider->id )}}">No</a>
                            @endif
                        </td>
                        <td>
                            @if($provider->service)
                                @if($provider->service->status == 'active')
                                    <label class="btn btn-primary">Yes</label>
                                @else
                                    <label class="btn btn-warning">No</label>
                                @endif
                            @else
                                <label class="btn btn-danger">N/A</label>
                            @endif
                        </td>
                        <td>
                            <div class="input-group-btn">
                                @if($provider->status == 'approved')
                                    <a class="btn btn-danger" href="{{route('admin.provider.disapprove', $provider->id )}}">Disable</a>
                                @else
                                    <a class="btn btn-success" href="{{route('admin.provider.approve', $provider->id )}}">Enable</a>
                                @endif
                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">Action
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ route('admin.provider.request', $provider->id) }}" class="btn btn-default"><i class="fa fa-search"></i> History</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.provider.edit', $provider->id) }}" class="btn btn-default"><i class="fa fa-pencil"></i> Edit</a>
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.provider.destroy', $provider->id) }}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button class="btn btn-default look-a-like" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Total Requests</th>
                        <th>Accepted Requests</th>
                        <th>Cancelled Requests</th>
                        <th>Pending Review</th>
                        <th>Online</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection