@extends('admin.layout.base')

@section('title', 'Provider Documents ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            
            <div class="box box-block bg-white">
                <h5 class="mb-1">Provider Service Type Allocation</h5>
                <form action="{{ route('admin.provider.document.store', $Provider->id) }}" method="POST">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-xs-12">
                            @if($ProviderService->count() > 0)
                                <hr>
                                <h6>Allocated Services : 
                                @foreach($ProviderService as $service)
                                    <span>{{$service->service_type->name}}</span>,
                                @endforeach
                                </h6>
                                <br>
                            @endif
                        </div>
                        <div class="col-xs-8">
                            <select class="form-control input" name="service_type">
                                @forelse($ServiceTypes as $Type)
                                <option value="{{ $Type->id }}">{{ $Type->name }}</option>
                                @empty
                                <option>- Please Create a Service Type -</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-xs-4">
                            <button class="btn btn-primary btn-block" type="submit">Update</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="box box-block bg-white">
                <h5 class="mb-1">Provider Documents</h5>
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Document Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($Provider->documents as $Index => $Document)
                        <tr>
                            <td>{{ $Index + 1 }}</td>
                            <td>{{ $Document->document->name }}</td>
                            <td>{{ $Document->status }}</td>
                            <td>
                                <div class="input-group-btn">
                                    <a href="{{ route('admin.provider.document.edit', [$Provider->id, $Document->id]) }}"><span class="btn btn-success btn-large">View</span></a>
                                    <button class="btn btn-danger btn-large" form="form-delete">Delete</button>
                                    <form action="{{ route('admin.provider.document.destroy', [$Provider->id, $Document->id]) }}" method="POST" id="form-delete">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Document Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
@endsection