@extends('admin.layout.base')

@section('title', 'Dispatcher ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <h4>Dispatcher</h4>                     

        <nav class="navbar navbar-light bg-white b-a mb-2">
            <button class="navbar-toggler hidden-md-up" type="button" data-toggle="collapse" data-target="#exCollapsingNavbar2" aria-controls="exCollapsingNavbar2" aria-expanded="false" aria-label="Toggle navigation"></button>

            <form class="form-inline navbar-item ml-1 float-xs-right">
                <div class="input-group">
                    <input type="text" class="form-control b-a" placeholder="Search for...">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary b-a">
                            <i class="ti-search"></i>
                        </button>
                    </span>
                </div>
            </form> 

            <ul class="nav navbar-nav float-xs-right">
                <li class="nav-item">
                    <button type="button" class="btn btn-success btn-md label-right b-a-0 waves-effect waves-light">
                        <span class="btn-label"><i class="ti-plus"></i></span>
                        ADD
                    </button>
                </li>
            </ul>


            <div class="collapse navbar-toggleable-sm" id="exCollapsingNavbar2">
                <ul class="nav navbar-nav dispatcher-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="#">All</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">My</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Warning</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Scheduled</a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-uppercase"><b>List</b></div>
                    <div class="items-list">
                        <div class="il-item">
                            <a class="text-black" href="#">
                                <div class="media">                                             
                                    <div class="media-body">
                                        <h6 class="media-heading">Chennai</h6>
                                        <p class="mb-0-5">John <span class="float-xs-right">Paul</span></p>
                                        <progress class="progress progress-success progress-sm" value="75" max="100">100%</progress>
                                        <span class="text-muted">Auto Search : 00:00:30</span>
                                    </div>
                                </div>                                              
                            </a>
                        </div>

                        <div class="il-item">
                            <a class="text-black" href="#">
                                <div class="media">                                             
                                    <div class="media-body">
                                        <h6 class="media-heading">Chennai</h6>
                                        <p class="mb-0-5">John <span class="float-xs-right">Paul</span></p>
                                        <progress class="progress progress-info progress-sm" value="75" max="100">100%</progress>
                                        <span class="text-muted">Auto Search : 00:00:30</span>
                                    </div>
                                </div>                                              
                            </a>
                        </div>

                        <div class="il-item">
                            <a class="text-black" href="#">
                                <div class="media">                                             
                                    <div class="media-body">
                                        <h6 class="media-heading">Chennai</h6>
                                        <p class="mb-0-5">John <span class="float-xs-right">Paul</span></p>
                                        <progress class="progress progress-warning progress-sm" value="75" max="100">100%</progress>
                                        <span class="text-muted">Auto Search : 00:00:30</span>
                                    </div>
                                </div>                                              
                            </a>
                        </div>

                        <div class="il-item">
                            <a class="text-black" href="#">
                                <div class="media">                                             
                                    <div class="media-body">
                                        <h6 class="media-heading">Chennai</h6>
                                        <p class="mb-0-5">John <span class="float-xs-right">Paul</span></p>
                                        <progress class="progress progress-danger progress-sm" value="75" max="100">100%</progress>
                                        <span class="text-muted">Auto Search : 00:00:30</span>
                                    </div>
                                </div>                                              
                            </a>
                        </div>
                    </div>                                      
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-block" id="create-ride">
                    <h3 class="card-title text-uppercase">Ride Details</h3>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" id="email" placeholder="Email">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pickup_address">Pickup Address</label>
                                <input type="text" class="form-control" name="pickup_address" id="pickup_address" placeholder="Pickup Address">
                                <input type="hidden" name="pickup_latitude"></input>
                                <input type="hidden" name="pickup_longitude"></input>
                            </div>
                            <div class="form-group">
                                <label for="dropoff_address">Dropoff Address</label>
                                <input type="text" class="form-control" name="dropoff_address" id="dropoff_address" placeholder="Dropoff Address">
                                <input type="hidden" name="dropoff_latitude"></input>
                                <input type="hidden" name="dropoff_longitude"></input>
                            </div>
                            <div class="form-group">
                                <label for="schedule_time">Schedule Time</label>
                                <input type="text" class="form-control" name="schedule_time" id="schedule_time" placeholder="Date">
                            </div>
                            <div class="form-group">
                                <label for="provider_auto_assign">Auto Assign Provider</label>
                                <input type="checkbox" id="provider_auto_assign" name="provider_auto_assign" class="js-switch" data-color="#f59345" checked>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <button type="button" class="btn btn-lg btn-success btn-block label-right waves-effect waves-light">
                                SUBMIT
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card my-card">
                    <div class="card-header text-uppercase"><b>MAP</b></div>
                    <div class="items-list">
                        <div class="il-item">
                            <div id="default" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.5.0/react.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.5.0/react-dom.js"></script>
<script src="https://unpkg.com/babel-standalone@6.24.0/babel.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    $("body").addClass("compact-sidebar");
    $('#schedule_time').datetimepicker({
        minDate: "{{ \Carbon\Carbon::today()->format('Y-m-d\TH:i') }}",
        maxDate: "{{ \Carbon\Carbon::today()->addDays(30)->format('Y-m-d\TH:i') }}"
    });
});
</script>

<script type="text/babel" src="{{ asset('asset/js/dispatcher.js') }}"></script>
<script type="text/javascript">
    
</script>
@endsection

@section('styles')
<style type="text/css">
    .my-card input{
        margin-bottom: 10px;
    }
    .my-card label.checkbox-inline{
        margin-top: 10px;
        margin-right: 5px;
        margin-bottom: 0;
    }
    .my-card label.checkbox-inline input{
        position: relative;
        top: 3px;
        margin-right: 3px;
    }
    .my-card .card-header .btn{
        font-size: 10px;
        padding: 3px 7px;   
    }
    .tag.my-tag{
        padding: 10px 15px;
        font-size: 11px;
    }

    .add-nav-btn{
        padding: 5px 15px;
        min-width: 0;
    }

    .dispatcher-nav li a{
        background-color: transparent;
        color: #000!important;
        padding: 5px 12px;
    }

    .dispatcher-nav li a:hover,.dispatcher-nav li a:focus,.dispatcher-nav li a:active{
        background-color: #20b9ae;
        color: #fff!important;
        padding: 5px 12px;
    }

    .dispatcher-nav li.active a,.dispatcher-nav li a:hover,.dispatcher-nav li a:focus,.dispatcher-nav li a:active{
        background-color: #20b9ae;
        color: #fff!important;
        padding: 5px 12px;
    }
</style>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />
@endsection