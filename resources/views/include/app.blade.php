<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{!! Session::get('app_name') !!}</title>
    {{-- Jquery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    @yield('header')
    <link rel='shortcut icon' type='image/x-icon' href="{{ asset('asset/img/favicon.png') }}" style="width: 2px !important;" />
    <link rel="stylesheet" href="{{ asset('asset/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/bundles/codemirror/lib/codemirror.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/bundles/codemirror/theme/duotone-dark.css') }} ">
    <link rel="stylesheet" href="{{ asset('asset/bundles/jquery-selectric/selectric.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/cdncss/iziToast.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ asset('asset/style/app.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">

</head>
<style>
    .flag-icon {
        height: 28px !important;
    }
</style>

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li>
                            <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                                <i data-feather="align-justify"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <ul class="navbar-nav navbar-right">
                    <!-- lang switch -->
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <span class="d-sm-none d-lg-inline-flex gap-2 btn btn-light">
                                <span class="flag-icon flag-icon-{{ app()->getLocale() == 'en' ? 'us' : app()->getLocale() }}"></span> {{ strtoupper(app()->getLocale()) }}
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <a href="{{ route('change-language', 'en') }}" class="dropdown-item d-lg-inline-flex gap-2">
                                <span class="flag-icon flag-icon-us"></span> English
                            </a>
                            <a href="{{ route('change-language', 'th') }}" class="dropdown-item d-lg-inline-flex gap-2">
                                <span class="flag-icon flag-icon-th"></span> ภาษาไทย
                            </a>
                        </div>
                    </li>

                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <span class="d-sm-none d-lg-inline-block btn btn-light"> {{ __('app.Logout') }} </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <a href="{{ route('logout') }}" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i>
                                {{ __('app.Logout') }}
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <a href="{{ route('index') }}" class="navbar-brand">
                            <img src="{{ asset('asset/img/favicon.png') ? asset('asset/img/favicon.png') : 'https://placehold.co/400' }}" id="navbar-logo" alt="" width="50" height="50" class="d-inline-block" alt="">
                            <!-- <span class="logo-name"> {!! Session::get('app_name') !!} </span> -->
                            {!! Session::get('app_name') !!}
                        </a>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">{{ __('app.Main') }}</li>
                        <li class="sideBarli indexSideA">
                            <a href="{{ route('index') }}" class="nav-link">
                                <i class="fas fa-tachometer-alt pt-1"></i>
                                <span> {{ __('app.Dashboard') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli usersSideA">
                            <a href="{{ route('users') }}" class="nav-link">
                                <i class="fas fa-users"></i>
                                <span>{{ __('app.Users') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli postSideA">
                            <a href="{{ route('posts') }}" class="nav-link">
                                <i class="fas fa-image"></i>
                                <span> {{ __('app.Posts') }} </span>
                            </a>
                        </li>
                        <li class="sideBarli storySideA">
                            <a href="{{ route('viewStories') }}" class="nav-link">
                                <i class="fas fa-compact-disc"></i>
                                <span> {{ __('stories') }} </span>
                            </a>
                        </li>
                        <li class="sideBarli liveapplicationSideA">
                            <a href="{{ route('liveapplication') }}" class="nav-link">
                                <i class="fas fa-rss"></i>
                                <span>{{ __('app.Live_applications') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli livehistorySideA">
                            <a href="{{ route('livehistory') }}" class="nav-link">
                                <i class="fas fa-rss"></i>
                                <span>{{ __('app.Live_History') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli redeemrequestsSideA">
                            <a href="{{ route('redeemrequests') }}" class="nav-link"><i class="fas fa-university"></i><span>{{ __('app.Redeem_Requests') }}</span></a>
                        </li>
                        {{-- <li class="sideBarli packageSideA">
                            <a href="{{ route('package') }}" class="nav-link">
                        <i class="fas fa-box"></i>
                        <span>{{ __('app.Subscriptions') }}</span>
                        </a>
                        </li> --}}
                        {{-- <li class="sideBarli packageSideA">
                            <a href="{{ route('packages') }}" class="nav-link">
                                <i class="fas fa-crown"></i>
                                <span>{{ __('app.packages') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli promotionPackageSideA">
                            <a href="{{ route('promotionPackages') }}" class="nav-link">
                                <i class="fas fa-ad"></i>
                                <span>{{ __('app.promotion_packages') }}</span>
                            </a>
                        </li> --}}
                        <li class="sideBarli diamondpackSideA">
                            <a href="{{ route('diamondpacks') }}" class="nav-link">
                                <i class="fas fa-coins"></i>
                                <span>{{ __('app.Diamond_packs') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli pointsSideA">
                            <a href="{{ route('points') }}" class="nav-link">
                                <i class="far fa-star"></i>
                                <span>{{ __('app.points') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli rewardSideA">
                            <a href="{{ route('rewards') }}" class="nav-link">
                                <i class="fas fa-gifts"></i>
                                <span>{{ __('app.rewards') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli redemptionSideA">
                            <a href="{{ route('redemptions') }}" class="nav-link">
                               <i class="far fa-bookmark"></i>
                                <span>{{ __('app.redemptions') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli giftSideA">
                            <a href="{{ route('gifts') }}" class="nav-link">
                                <i class="fas fa-gift"></i>
                                <span>{{ __('app.Gifts') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli verificationRequestSideA">
                            <a href="{{ route('verificationrequests') }}" class="nav-link">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ __('app.Verification_requests') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli reportSideA">
                            <a href="{{ route('report') }}" class="nav-link">
                                <i class="fas fa-question"></i>
                                <span>{{ __('app.Report') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli notificationSideA">
                            <a href="{{ route('notifications') }}" class="nav-link">
                                <i class="fas fa-bell"></i>
                                <span>{{ __('app.Notifications') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli shopsSideA">
                            <a href="{{ route('shops') }}" class="nav-link">
                                <i class="fas fa-store"></i>
                                <span>{{ __('app.shops') }}</span>
                            </a>
                            <li class="sideBarli notificationSideA">
                            <a href="{{ route('notifications') }}" class="nav-link">
                                <i class="fas fa-bell"></i>
                                <span>{{ __('app.Notifications') }}</span>
                            </a>
                        </li>
                            <!-- </li>
                        <li class="sideBarli otherSideA">
                            <a href="{{ route('setting') }}">
                                <i class="fas fa-cog pt-1"></i>
                                <span>{{ __('app.Setting') }}</span>
                            </a>
                        </li> -->
                        <!--
                        <li class="sideBarli otherSideA">
                            <a href="#" class="nav-link has-dropdown">
                                <i class="fas fa-layer-group"></i>
                                <span>{{ __('app.category') }}</span>
                            </a>
                            <ul class="dropdown-menu" id="category-dropdown">
                                <li class="sideBarli jobCategoriesSideA">
                                    <a href="{{ route('jobCategories') }}" class="nav-link">
                                        <i class="fas fa-table"></i>
                                        <span>{{ __('app.job_categories') }}</span>
                                    </a>
                                </li>
                                <li class="sideBarli productCategoriesSideA">
                                    <a href="{{ route('productCategories') }}" class="nav-link">
                                        <i class="fas fa-table"></i>
                                        <span>{{ __('app.product_categories') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="sideBarli productListSideA">
                            <a href="{{ route('products.index') }}" class="nav-link">
                                <i class="fas fa-box"></i>
                                <span>{{ __('app.product_list') }}</span>
                            </a>
                        </li>
                        -->
                        <li class="sideBarli otherSideA">
                            <a href="#" class="nav-link has-dropdown">
                                <i class="fas fa-cog pt-1"></i>
                                <span>{{ __('app.Setting') }}</span>
                            </a>
                            <ul class="dropdown-menu" id="setting-dropdown">
                                <li class="sideBarli incomeSettingSideA">
                                    <a href="{{ route('income-settings.index') }}" class="nav-link">
                                        <i class="fas fa-credit-card"></i>
                                        <span>{{ __('app.income_settings') }}</span>
                                    </a>
                                </li>
                                <li class="sideBarli paymentSettingSideA">
                                    <a href="{{ route('paymentSetting') }}" class="nav-link">
                                        <i class="fas fa-credit-card"></i>
                                        <span>{{ __('app.Payment_Setting') }}</span>
                                    </a>
                                </li>
                                <li class="sideBarli systemSettingSideA">
                                    <a href="{{ route('setting') }}" class="nav-link">
                                        <i class="fas fa-tools"></i>
                                        <span>{{ __('app.System_Setting') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="menu-header">{{ __('Pages') }}</li>
                        <li class="sideBarli  privacySideA">
                            <a href="{{ route('viewPrivacy') }}" class="nav-link">
                                <i class="fas fa-info"></i>
                                <span>{{ __('Privacy Policy') }}</span>
                            </a>
                        </li>
                        <li class="sideBarli  termsSideA">
                            <a href="{{ route('viewTerms') }}" class="nav-link">
                                <i class="fas fa-info"></i>
                                <span>{{ __('Terms Of Use') }}</span>
                            </a>
                        </li>
                    </ul>
                </aside>
            </div>
            <!-- Main Content -->
            <div class="main-content">
                @yield('content')
                <form action="">
                    <input type="hidden" id="user_type" value="{{ session('user_type') }}">
                </form>
            </div>
        </div>
    </div>


    <input type="hidden" value="{{ env('APP_URL')}}" id="appUrl">

    <script src="{{ asset('asset/cdnjs/iziToast.min.js') }}"></script>
    <script src="{{ asset('asset/cdnjs/sweetalert.min.js') }}"></script>
    <script src="{{ asset('asset/script/env.js') }}"></script>
    <script src="{{ asset('asset/js/app.min.js ') }}"></script>
    <script src="{{ asset('asset/bundles/datatables/datatables.min.js ') }}"></script>
    <script src="{{ asset('asset/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('asset/bundles/jquery-ui/jquery-ui.min.js ') }}"></script>
    <script src="{{ asset('asset/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="{{ asset('asset/js/page/datatables.js') }}"></script>
    <script src="{{ asset('asset/js/scripts.js') }}"></script>
    <script src="{{ asset('asset/script/app.js') }}"></script>

    <!-- include summernote css/js -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script>
        $('#app_name').keyup(function() {
            let appName = $(this).val();
            $('.logo-name').text(appName);
            document.title = appName;
        });
    </script>


    <script src="{{ asset('asset/Trumbowyg/dist/trumbowyg.js') }}"></script>
    <!-- 
    <script src="/dist/langs/fr.min.js"></script>
    <script src="/dist/plugins/base64/trumbowyg.base64.js"></script>
    <script src="/dist/plugins/colors/trumbowyg.colors.js"></script>
    <script src="/dist/plugins/noembed/trumbowyg.noembed.js"></script>
    <script src="/dist/plugins/pasteimage/trumbowyg.pasteimage.js"></script>
    <script src="/dist/plugins/template/trumbowyg.template.js"></script>
    <script src="/dist/plugins/preformatted/trumbowyg.preformatted.js"></script>
    <script src="/dist/plugins/ruby/trumbowyg.ruby.js"></script>
    <script src="/dist/plugins/upload/trumbowyg.upload.js"></script> 
    -->
</body>

</html>