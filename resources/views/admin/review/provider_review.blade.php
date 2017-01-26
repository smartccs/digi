@extends('admin.layout.base')

@section('title', 'Provider Reviews ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            
            <div class="box box-block bg-white">
                <h5 class="mb-1">Provider Reviews</h5>
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Name</th>
                            <th>Provider Name</th>
                            <th>Rating</th>
                            <th>Date & Time</th>
                            <th>Comments</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($Reviews as $index => $review)
                        <tr>
                            <td>{{$index + 1}}</td>
                            <td>{{$review->user_first_name}} {{$review->user_last_name}}</td>
                            <td>{{$review->provider_first_name}} {{$review->provider_last_name}}</td>
                            <td>{{$review->rating}}</td>
                            <td>{{$review->created_at}}</td>
                            <td>{{$review->comments}}</td>
                            <td>
                                <form action="{{ route('admin.provider-review.destroy', $review->id) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>User Name</th>
                            <th>Provider Name</th>
                            <th>Rating</th>
                            <th>Date & Time</th>
                            <th>Comments</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
@endsection