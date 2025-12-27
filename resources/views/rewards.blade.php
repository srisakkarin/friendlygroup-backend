@extends('include.app')

@section('header')
    <script src="{{ asset('asset/script/rewards.js') }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Rewards System</h4>
            <a class="btn btn-primary addModalBtn ml-auto" data-bs-toggle="modal" data-bs-target="#addRewardModal"
                href="">Add Reward
            </a>
        </div>
        <div class="card-body">
            <table class="table table-striped w-100" id="tableReward">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Points</th>
                        <th>Discount Info</th>
                        <th>Status</th>
                        <th width="200px" style="text-align: right;">{{ __('app.Action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- ADD MODAL --}}
    <div class="modal fade" id="addRewardModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Reward</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" enctype="multipart/form-data" class="add_reward" id="formAddReward" autocomplete="off">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reward Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Required Points</label>
                                    <input type="number" name="required_points" class="form-control" min="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" name="type" id="add_type" required>
                                <option value="gift">Gift (Item)</option>
                                <option value="discount">Discount Coupon</option>
                            </select>
                        </div>

                        {{-- Discount Section (Hidden by default) --}}
                        <div id="add_discount_section" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Discount Type</label>
                                        <select class="form-control" name="discount_type">
                                            <option value="fixed">Fixed Amount (Baht)</option>
                                            <option value="percent">Percentage (%)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Discount Value</label>
                                        <input type="number" name="discount_value" class="form-control" placeholder="e.g. 50 or 10">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" style="height: 80px;"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="form-group text-right">
                            <input class="btn btn-primary mr-1" type="submit" value="Add Reward">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Reward</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" enctype="multipart/form-data" id="formEditReward" autocomplete="off">
                        @csrf
                        <input type="hidden" class="form-control" id="edit_id" name="id" value="">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reward Name</label>
                                    <input type="text" id="edit_name" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Required Points</label>
                                    <input type="number" id="edit_required_points" name="required_points" class="form-control" min="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" name="type" id="edit_type" required>
                                <option value="gift">Gift (Item)</option>
                                <option value="discount">Discount Coupon</option>
                            </select>
                        </div>

                        {{-- Discount Section (Hidden by default) --}}
                        <div id="edit_discount_section" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Discount Type</label>
                                        <select class="form-control" name="discount_type" id="edit_discount_type">
                                            <option value="fixed">Fixed Amount (Baht)</option>
                                            <option value="percent">Percentage (%)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Discount Value</label>
                                        <input type="number" name="discount_value" id="edit_discount_value" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" id="edit_description" class="form-control" style="height: 80px;"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div id="preview_image_container" class="mt-2"></div>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="is_active" id="edit_is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="form-group text-right">
                            <input type="submit" class="btn btn-primary" value="Update">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection