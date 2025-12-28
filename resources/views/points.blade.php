@extends('include.app')

@section('header')
    <script src="{{ asset('asset/script/points.js') }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>User Points Management</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped w-100" id="tablePoints">
                    <thead>
                        <tr>
                            <th>User Info</th>
                            <th>Current Points</th>
                            <th width="150px" class="text-right">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL ADJUST POINTS --}}
    <div class="modal fade" id="modalAdjust" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Points</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formAdjustPoints">
                        @csrf
                        <input type="hidden" name="user_id" id="adjust_user_id">
                        <div class="form-group">
                            <label>User: <span id="adjust_user_name" class="text-primary font-weight-bold"></span></label>
                        </div>
                        <div class="form-group">
                            <label>Action Type</label>
                            <select class="form-control" name="type">
                                <option value="add">Add Points (+)</option>
                                <option value="deduct">Deduct Points (-)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" name="amount" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Reason / Description</label>
                            <input type="text" name="description" class="form-control" required placeholder="e.g. Compensation, Special Gift">
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Save Adjustment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL HISTORY --}}
    <div class="modal fade" id="modalHistory" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Point History: <span id="history_user_name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-bordered" id="tableHistory">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody id="historyBody">
                            {{-- Content loaded via JS --}}
                        </tbody>
                    </table>
                    {{-- Pagination for history if needed can be simple prev/next buttons --}}
                </div>
            </div>
        </div>
    </div>
@endsection