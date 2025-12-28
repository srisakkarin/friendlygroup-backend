@extends('include.app')
@section('header')
<script src="{{ asset('asset/script/shops.js') }}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Branch Management</h4>
        <button class="btn btn-primary" id="btnAddShop">Add New Shop</button>
    </div>
    <div class="card-body">
        <table class="table table-striped w-100" id="tableShops">
            <thead>
                <tr>
                    <th>Logo</th> {{-- ✅ เพิ่มหัวตาราง Logo --}}
                    <th>Shop Name</th>
                    <th>Code</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="modalShop" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Shop</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- form ไม่ต้องเพิ่ม enctype="multipart/form-data" ก็ได้เพราะใช้ JS FormData --}}
                <form id="formShop">
                    @csrf
                    <input type="hidden" name="id" id="shopId">

                    {{-- ✅ เพิ่มส่วนอัปโหลด Logo และ Preview --}}
                    <div class="form-group text-center">
                        <label d-block>Shop Logo</label>
                        <div class="mb-2">
                            <img id="previewLogo" src="" alt="Logo Preview" style="max-width: 150px; max-height: 150px; display: none;" class="img-thumbnail rounded-circle">
                        </div>
                        <input type="file" name="logo" id="shopLogo" class="form-control" accept="image/*">
                        <small class="text-muted">Allowed JPG, PNG, GIF. Max size 2MB.</small>
                    </div>

                    <div class="form-group">
                        <label>Shop Name</label>
                        <input type="text" name="name" id="shopName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Shop Code</label>
                        <input type="text" name="code" id="shopCode" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" id="shopAddress" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" id="shopStatus" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection