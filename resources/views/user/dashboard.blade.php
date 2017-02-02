@extends('user.layout.base')

@section('title', 'Dashboard ')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">Ride Now</h4>
            </div>
        </div>

        <div class="row no-margin">
            <div class="col-md-6">
                <form action="user-fare-calulate.html">
                <div class="input-group dash-form">
                    <input type="text" class="form-control"  placeholder="Enter pickup location" >                               
                </div>

                <div class="input-group dash-form">
                    <input type="text" class="form-control"  placeholder="Enter drop location" >                               
                </div>                           

                <div class="car-detail">

                    <div class="car-radio">
                        <input type="radio" name="fare" id="bmw" checked="checked">
                        <label for="bmw">
                            <div class="car-radio-inner">
                                <div class="img"><img src="img/car-icon-select.png"></div>
                                <div class="name"><span>BMW</span></div>
                                <div class="rate">Rs.1500</div>
                            </div>
                        </label>
                    </div>

                    <div class="car-radio">
                        <input type="radio" name="fare" id="audi">
                        <label for="audi">
                            <div class="car-radio-inner">
                                <div class="img"><img src="img/car-icon-select.png"></div>
                                <div class="name"><span>Audi</span></div>
                                <div class="rate">Rs.2500</div>
                            </div>
                        </label>
                    </div>

                    <div class="car-radio">
                        <input type="radio" name="fare" id="bmww">
                        <label for="bmww">
                            <div class="car-radio-inner">
                                <div class="img"><img src="img/car-icon-select.png"></div>
                                <div class="name"><span>BMW</span></div>
                                <div class="rate">Rs.1500</div>
                            </div>
                        </label>
                    </div>

                    <div class="car-radio">
                        <input type="radio" name="fare" id="audii">
                        <label for="audii">
                            <div class="car-radio-inner">
                                <div class="img"><img src="img/car-icon-select.png"></div>
                                <div class="name"><span>Audi</span></div>
                                <div class="rate">Rs.2500</div>
                            </div>
                        </label>
                    </div>

                    <div class="car-radio">
                        <input type="radio" name="fare" id="bmwww">
                        <label for="bmwww">
                            <div class="car-radio-inner">
                                <div class="img"><img src="img/car-icon-select.png"></div>
                                <div class="name"><span>BMW</span></div>
                                <div class="rate">Rs.1500</div>
                            </div>
                        </label>
                    </div>

                    <div class="car-radio">
                        <input type="radio" name="fare" id="audiii">
                        <label for="audiii">
                            <div class="car-radio-inner">
                                <div class="img"><img src="img/car-icon-select.png"></div>
                                <div class="name"><span>Audi</span></div>
                                <div class="rate">Rs.2500</div>
                            </div>
                        </label>
                    </div>


                </div>

                <button type="submit" class="full-primary-btn fare-btn">RIDE NOW</button>

                </form>
            </div>

            <div class="col-md-6">
                <div class="map-responsive">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d386950.6511603643!2d-73.70231446529533!3d40.738882125234106!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNueva+York!5e0!3m2!1ses-419!2sus!4v1445032011908" width="600" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
                </div> 
            </div>
        </div>

    </div>
</div>

@endsection