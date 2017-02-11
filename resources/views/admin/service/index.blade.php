@extends('admin.layout.base')

@section('title', 'Service Types ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            
            <div class="box box-block bg-white">
                <h5 class="mb-1">Service Types</h5>
                <a href="{{ route('admin.service.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Service</a>
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Provider Name</th>
                            <th>Fixed Price</th>
                            <th>Distance Price</th>
                            <th>Service Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($services as $index => $service)
                        <tr>
                            <td>{{$index + 1}}</td>
                            <td>{{$service->name}}</td>
                            <td>{{$service->provider_name}}</td>
                            <td>{{currency($service->fixed)}}</td>
                            <td>{{currency($service->price)}}</td>
                            <td>
                                @if($service->image) 
                                    <img src="{{$service->image}}" style="height: 50px" >
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.service.destroy', $service->id) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="DELETE">
                                    <a href="{{ route('admin.service.edit', $service->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Provider Name</th>
                            <th>Status</th>
                            <th>Service Image</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
@endsection