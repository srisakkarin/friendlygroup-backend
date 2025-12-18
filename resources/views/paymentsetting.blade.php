@extends('include.app')
@section('header')
<script src="{{ asset('asset/script/paymentSetting.js') }}"></script>
@endsection

@section('content')


<div class="card ">
    <div class="card-header">
        <h4>{{ __('app.Payment_Setting') }}</h4>
        <div class="border-bottom-0 border-dark border"></div>
    </div>
    <div class="card-body">

        <form Autocomplete="off" class="form-group form-border appdataForm" action="" method="post">
            @csrf
            <div class="form-row">
                <div class="form-group col-8">
                    <label for="">{{ __('app.apikey') }}</label>
                    <input type="text" class="form-control" name="apikey" value="{{ $appdata->apikey }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-8">
                    <label for="">{{ __('app.secretkey') }}</label>
                    <input type="text" class="form-control" name="secretkey" value="{{ $appdata->secretkey }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-8">
                    <label for="">{{ __('app.merchantID') }}</label>
                    <input type="text" class="form-control" name="merchantID" value="{{ $appdata->merchantID }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-8">
                    <label for="">{{ __('app.authKey') }}</label>
                    <textarea type="text" class="form-control" name="authKey" value="{{ $appdata->authKey }}" rows="3" required></textarea>
                </div>
            </div>
            <div class="form-group-submit">
                <button class="btn btn-primary " type="submit">{{ __('app.Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection