@extends('user.layout.base')

@section('title', 'Payment')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">@lang('user.payment')</h4>
            </div>
        </div>
        @include('common.notify')
        <div class="row no-margin payment">
            <div class="col-md-12">
                <h5 class="btm-border"><strong>@lang('user.payment_method')</strong> <a href="#" class="sub-right pull-right" data-toggle="modal" data-target="#add-card-modal">@lang('user.card.add_card')</a></h5>

                <div class="pay-option">
                    <h6><img src="{{asset('asset/img/cash-icon.png')}}"> @lang('user.cash') </h6>
                </div>
                @foreach($cards as $card)
                <div class="pay-option">
                    <h6>
                        <img src="{{asset('asset/img/card-icon.png')}}"> {{$card->brand}} **** **** **** {{$card->last_four}} 
                        @if($card->is_default)
                            <a href="#" class="default">@lang('user.card.default')</a>
                        @endif 
                        <form action="{{url('card/destory')}}" method="POST" class="pull-right">
                            {{ csrf_field() }}
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="card_id" value="{{$card->card_id}}">
                            <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-sm" >@lang('user.card.delete')</button>
                        </form>
                    </h6>
                </div>
                @endforeach

                <!-- <div class="pay-option">
                    <h6><img src="img/paypal-icon.png"> Paypal <a href="#" class="set-default">Set Default</a></h6>
                </div> -->

            </div>
        </div>

    </div>
</div>


    <!-- Add Card Modal -->
    <div id="add-card-modal" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content" >
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">@lang('user.card.add_card')</h4>
          </div>
            <form id="payment-form" action="{{ route('card.store') }}" method="POST" >
                {{ csrf_field() }}
          <div class="modal-body">
            <div class="row no-margin" id="card-payment">
                <div class="form-group col-md-12 col-sm-12">
                    <label>@lang('user.card.fullname')</label>
                    <input data-stripe="name" autocomplete="false" required type="text" class="form-control" placeholder="@lang('user.card.fullname')">
                </div>
                <div class="form-group col-md-12 col-sm-12">
                    <label>@lang('user.card.card_no')</label>
                    <input data-stripe="number" type="number" required size="16" autocomplete="false" maxlength="16" class="form-control" placeholder="@lang('user.card.card_no')">
                </div>
                <div class="form-group col-md-4 col-sm-12">
                    <label>@lang('user.card.month')</label>
                    <input type="number" maxlength="2" size="2" required autocomplete="false" class="form-control" data-stripe="exp-month" placeholder="MM">
                </div>
                <div class="form-group col-md-4 col-sm-12">
                    <label>@lang('user.card.year')</label>
                    <input type="number" maxlength="2" size="2" required autocomplete="false" data-stripe="exp-year" class="form-control" placeholder="YY">
                </div>
                <div class="form-group col-md-4 col-sm-12">
                    <label>@lang('user.card.cvv')</label>
                    <input type="number" data-stripe="cvc" size="4" required autocomplete="false" maxlength="4" class="form-control" placeholder="@lang('user.card.cvv')">
                </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-default">@lang('user.card.add_card')</button>
          </div>
        </form>

        </div>

      </div>
    </div>

@endsection

@section('scripts')
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

    <script type="text/javascript">
        Stripe.setPublishableKey('{{ env("STRIPE_KEY")}}');

         var stripeResponseHandler = function (status, response) {
            var $form = $('#payment-form');

            console.log(response);

            if (response.error) {
                // Show the errors on the form
                $form.find('.payment-errors').text(response.error.message);
                $form.find('button').prop('disabled', false);
                alert('error');

            } else {
                // token contains id, last4, and card type
                var token = response.id;
                // Insert the token into the form so it gets submitted to the server
                $form.append($('<input type="hidden" id="stripeToken" name="stripe_token" />').val(token));
                jQuery($form.get(0)).submit();
            }
        };
                
        $('#payment-form').submit(function (e) {
            
            if ($('#stripeToken').length == 0)
            {
                console.log('ok');
                var $form = $(this);
                $form.find('button').prop('disabled', true);
                console.log($form);
                Stripe.card.createToken($form, stripeResponseHandler);
                return false;
            }
        });

    </script>
@endsection