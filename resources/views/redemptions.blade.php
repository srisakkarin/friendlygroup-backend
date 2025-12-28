@extends('include.app')

@section('header')
    <script src="{{ asset('asset/script/redemptions.js') }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Redemption History (ประวัติการแลกของรางวัล)</h4>
            <div class="card-header-action">
                <select id="filter_status" class="form-control">
                    <option value="all">All Status</option>
                    <option value="active">Active (ยังไม่ใช้)</option>
                    <option value="used">Used (ใช้แล้ว)</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped w-100" id="tableRedemption">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Reward</th>
                            <th>Type</th>
                            <th>Points Used</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection