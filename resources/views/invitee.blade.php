@extends('include.app')
@section('header')
<script>
    var userId = {{$user->id}};
</script>
<script src="{{ asset('asset/script/invitee.js') }}"></script>
<meta name="csrf-token" content="{{ csrf_token() }}"> 
@endsection
@section('content')

<div class="card">
    <div class="card-header">
        <h4>{{ __('app.invitees') }} ({{$user->fullname}})</h4> 
    </div>
    <div class="card-body">
        <table class="table table-striped w-100" id="table-invitees">
    <thead>
        <tr>
            <th>{{ __('app.User_Image') }}</th>
            <th>{{ __('app.fullname') }}</th>
            <th>{{ __('app.Identity') }}</th>
            <th width="100px" class="text-end">{{ __('app.Action') }}</th>
        </tr>
    </thead>
</table>

    </div>
</div>



<div class="modal fade" id="viewRequest" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">

                <h4 id="request-id">{{ __('app.View_Redeem_Requests') }}</h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form action="" method="post" enctype="multipart/form-data" class="add_category" id="completeForm"
                    autocomplete="off">
                    @csrf

                    <input type="hidden" class="form-control" id="editId" name="id" value="">

                    <div class="d-flex align-items-center">
                        <img id="user-img" class="mb-2 rounded-circle" src="http://placehold.jp/150x150.png" width="70" height="70">
                        <h5 id="user-fullname" class="m-2 "></h5>
                    </div>


                    <div class="form-group">
                        <label> {{ __('app.Coin_Amount') }}</label>
                        <input id="coin_amount" type="text" name="coin_amount" class="form-control" required readonly>
                    </div>

                    <div class="form-group">
                        <label> {{ __('app.Amount_Paid') }}</label>
                        <input id="amount_paid" type="text" name="amount_paid" class="form-control" required readonly>
                    </div>

                    <div class="form-group">
                        <label> {{ __('app.Payment_Gateway') }}</label>
                        <input id="payment_gateway" type="text" name="payment_gateway" class="form-control" required readonly>
                    </div>


                    <div class="form-group">
                        <label> {{ __('app.Account_details') }}</label>
                        <textarea id="account_details" type="text" name="account_details" class="form-control" required readonly></textarea>
                    </div>

                    <div id="div-submit" class="form-group d-none">
                        <input class="btn btn-success mr-1" type="submit" value=" {{ __('app.Complete') }}">
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>




@endsection