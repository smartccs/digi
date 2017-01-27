@extends('admin.layout.base')

@section('title', 'Provider Documents ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            
            <div class="box box-block bg-white">
                <h5 class="mb-1">Provider Documents</h5>
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>Provider ID</th>
			                <th>Provider Name</th>
			                <th>Document Type</th>
			                <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
		              @foreach($documents as $index => $doc)
		               <tr>
		                    <td>{{$provider->id}}</td>
		                    <td>{{$provider->first_name." ".$provider->last_name}}</td>
		                    <td>{{$doc->document_name}}</td>
		                    <td><a href="{{ $doc->document_url }}" target="_blank"><span class="btn btn-info btn-large">View</span></a></td>
		                </tr>
		              @endforeach
		            </tbody>
                    <tfoot>
                        <tr>
                            <th>Provider ID</th>
			                <th>Provider Name</th>
			                <th>Document Type</th>
			                <th>View</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
@endsection