@extends('include.app')
@section('header')
<script src="{{ asset('asset/script/setting.js') }}"></script>
<link rel="stylesheet" href="{{ asset('asset/style/addFakeUser.css') }}">
@endsection

@section('content')


<div class="card  ">
    <div class="card-header">
        <h4>{{ __('app.Appdata') }}</h4>
        <div class="border-bottom-0 border-dark border"></div>
    </div>
    <div class="card-body">

        <form Autocomplete="off" class="form-group form-border appdataForm" action="" method="post">
            @csrf
            <div class="form-row ">
                <div class="form-group col-md-3">
                    <label for="">{{ __('app.App_name') }}</label>
                    <input type="text" class="form-control" name="app_name" id="app_name" value="{{ $appdata->app_name }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="">{{ __('app.Currency') }}</label>
                    <input type="text" class="form-control" name="currency" value="{{ $appdata->currency }}" required>
                </div>

                <div class="form-group col-md-3">
                    <label for="">{{ __('app.Minimum_Threshold') }}</label>
                    <input type="text" class="form-control" name="min_threshold" value="{{ $appdata->min_threshold }}" required>
                </div>

                <div class="form-group col-md-3">
                    <label for="">{{ __('app.Coin_Rate') }}</label>
                    <input type="number" step="0.000001" class="form-control" name="coin_rate" value="{{ $appdata->coin_rate }}" required>
                </div>


            </div>

            <div class="form-row">

                <div class="form-group col-md-6">
                    <label for="">{{ __('app.Minimum_users_needed') }}</label>
                    <input type="text" class="form-control" name="min_user_live" value="{{ $appdata->min_user_live }}" required>
                </div>

                <div class="form-group col-md-6">
                    <label for="">{{ __('app.Maximum_minutes_for_live') }}</label>
                    <input type="text" class="form-control" name="max_minute_live" value="{{ $appdata->max_minute_live }}" required>
                </div>

            </div>


            <div class="form-row">

                <div class="form-group col-md-3">
                    <label for="">{{ __('app.Message_price') }}</label>
                    <input type="number" class="form-control" name="message_price" value="{{ $appdata->message_price }}" pattern="[0-9]" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="">{{ __('app.Reverse_swipe_price') }}</label>
                    <input type="number" class="form-control" name="reverse_swipe_price" value="{{ $appdata->reverse_swipe_price }}" pattern="[0-9]" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="">{{ __('app.Live_watching_price') }}</label>
                    <input type="number" class="form-control" name="live_watching_price" value="{{ $appdata->live_watching_price }}" pattern="[0-9]" required>
                </div>
            </div>
            <div class="form-row">


                <div class="form-group col-md-3">
                    <label class="d-block" for="">{{ __('app.Dating_or_livestream') }}</label>
                    <label class="switch ml-1">
                        <input type="checkbox" name="is_dating" id="is_dating" {{ $appdata->is_dating == 1 ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="form-group col-md-3">
                    <label class="d-block" for="">{{ __('app.socialMedia') }}</label>
                    <label class="switch ml-1">
                        <input type="checkbox" name="is_social_media" id="is_social_media" {{ $appdata->is_social_media == 1 ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label class="d-block" for="">{{ __('app.postDescriptionLimit') }}</label>
                    <input type="text" class="form-control" name="post_description_limit" value="{{ $appdata->post_description_limit }}">
                </div>
                <div class="form-group col-md-3">
                    <label class="d-block" for="">{{ __('app.postUploadImageLimit') }}</label>
                    <input type="text" class="form-control" name="post_upload_image_limit" value="{{ $appdata->post_upload_image_limit }}">
                </div>
            </div>
            <div class="my-4">
                <h5 class="text-dark">{{ __('Admob Ad Units') }}</h5>
            </div>
            <div class="form-row ">
                <div class="form-group col-md-4">
                    <label for="">Admob Banner Ad Unit : Android</label>
                    <input type="text" class="form-control" name="admob_banner" value="{{ $appdata->admob_banner }}">
                </div>
                <div class="form-group col-md-4">
                    <label for="">Admob Interstitial Ad Unit : Android</label>
                    <input type="text" class="form-control" name="admob_int" value="{{ $appdata->admob_int }}">
                </div>
            </div>
            <div class="form-row ">
                <div class="form-group col-md-4">
                    <label for="">Admob Banner Ad Unit : iOS</label>
                    <input type="text" class="form-control" name="admob_banner_ios" value="{{ $appdata->admob_banner_ios }}">
                </div>
                <div class="form-group col-md-4">
                    <label for="">Admob Interstitial Ad Unit : iOS</label>
                    <input type="text" class="form-control" name="admob_int_ios" value="{{ $appdata->admob_int_ios }}">
                </div>
            </div>
            <div class="my-4">
                <h5 class="text-dark">
                    {{__('Image setting')}}
                </h5>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-4">
                        {{__('Login Page Background')}}
                    </div>
                    <div class="position-relative d-inline-block mb-4" data-href="">
                        <div class="filenameupload2">
                            <img class="rounded " src="{{ env('image') }}{{ $appdata->loginPageImage ?? 'https://placehold.co/600' }}" width="130" height="130">
                            <div data-imgUrl="{{ $appdata->loginPageImage ?? null }}" data-fieldName="loginPageImage" class="middle btnInAppImageRemove"><i class="material-icons remove_img2">cancel</i>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <button id="btnAddLoginImage" class="btn btn-primary">Add Image</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-4">
                        {{__('Register Page Background')}}
                    </div>
                    <div class="position-relative d-inline-block mb-4" data-href="">
                        <div class="filenameupload2">
                            <img class="rounded " src="{{ env('image') }}{{ $appdata->registerPageImage ?? 'https://placehold.co/600' }}" width="130" height="130">
                            <div data-imgUrl="{{ $appdata->registerPageImage ?? null }}" data-fieldName="registerPageImage" class="middle btnInAppImageRemove"><i class="material-icons remove_img2">cancel</i>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <button id="btnAddRegisterImage" class="btn btn-primary">Add Image</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-4">
                        {{__('Welcome Page')}}
                    </div>
                    <div class="position-relative d-inline-block mb-4" data-href="">
                        <div class="filenameupload2">
                            <img class="rounded " src="{{ env('image') }}{{ $appdata->welcomePageImage ?? 'https://placehold.co/600' }}" width="130" height="130">
                            <div data-imgUrl="{{ $appdata->welcomePageImage ?? null }}" data-fieldName="welcomePageImage" class="middle btnInAppImageRemove"><i class="material-icons remove_img2">cancel</i>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <button id="btnAddWelcomeImage" class="btn btn-primary">Add Image</button>
                    </div>
                </div>
            </div>
            <div class="form-group-submit">
                <button class="btn btn-primary " type="submit">{{ __('app.Save') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- ADD IMAGE MODAL -->
<div class="modal fade" id="addLoginImageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">

                <h5>{{ __('app.Add_Image') }}</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form action="" method="post" enctype="multipart/form-data" class="add_category" id="addLoginForm" autocomplete="off">
                    @csrf
                    <input class=" form-control" readonly name="formName" value="addLogin" type="text" id="formName" hidden>

                    <div class="form-group">
                        <div class="mb-3">
                            <label for="gift_image" class="form-label">{{ __('Image') }}</label>
                            <input id="gift_image" class="form-control" type="file" accept="image/png, image/gif, image/jpeg" name="image" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <input class="btn btn-primary mr-1" type="submit" value=" {{ __('app.Submit') }}">
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="addRegisterImageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">

                <h5>{{ __('app.Add_Image') }}</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form action="" method="post" enctype="multipart/form-data" class="add_category" id="addRegisterForm" autocomplete="off">
                    @csrf
                    <input class=" form-control" readonly name="formName" value="addRegister" type="text" id="formName" hidden>

                    <div class="form-group">
                        <div class="mb-3">
                            <label for="gift_image" class="form-label">{{ __('Image') }}</label>
                            <input id="gift_image" class="form-control" type="file" accept="image/png, image/gif, image/jpeg" name="image" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <input class="btn btn-primary mr-1" type="submit" value=" {{ __('app.Submit') }}">
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="addWelcomeImageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">

                <h5>{{ __('app.Add_Image') }}</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form action="" method="post" enctype="multipart/form-data" class="add_category" id="addWelcomeForm" autocomplete="off">
                    @csrf
                    <input class=" form-control" readonly name="formName" value="addWelcome" type="text" id="formName" hidden>

                    <div class="form-group">
                        <div class="mb-3">
                            <label for="gift_image" class="form-label">{{ __('Image') }}</label>
                            <input id="gift_image" class="form-control" type="file" accept="image/png, image/gif, image/jpeg" name="image" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <input class="btn btn-primary mr-1" type="submit" value=" {{ __('app.Submit') }}">
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

@endsection