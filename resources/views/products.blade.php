@extends('include.app')
@section('header')
<script src="{{ asset('asset/script/products.js') }}"></script>
@endsection
@section('content')

<div class="card">
    <div class="card-header">
        <h4>{{ __('app.products') }}</h4>
        <!-- <a class="btn btn-primary addProductModalBtn ml-auto" data-bs-toggle="modal" data-bs-target="#addProductModal" href="">
            {{ __('app.add_product') }}
        </a> -->
    </div>
    <div class="card-body overflow-scroll">
        <table class="table table-striped w-100" id="productsTable">
            <thead>
                <tr>
                    <th> {{ __('app.Image') }}</th>
                    <th> {{ __('app.Name') }}</th>
                    <th> {{ __('app.Price') }}</th>
                    <th> {{ __('app.Minimum_Order') }}</th>
                    <th> {{ __('app.Category') }}</th>
                    <th> {{ __('app.Status') }}</th>
                    <th> {{ __('app.Visibility') }}</th>
                    <th> {{ __('app.Stock') }}</th>
                    <th> {{ __('app.Created_At') }}</th>
                    <th width="200px" style="text-align: right;"> {{ __('app.Action') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Add Product Modal --}}
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">{{ __('app.add_product') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" enctype="multipart/form-data" class="add_product_form" id="addProductForm" autocomplete="off">
                    @csrf
                    <div class="form-group">
                        <label for="pro_name">{{ __('app.Name') }}</label>
                        <input type="text" name="pro_name" id="pro_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="pro_details">{{ __('app.Description') }}</label>
                        <textarea name="pro_details" id="pro_details" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="pro_price">{{ __('app.Price') }}</label>
                        <input type="number" step="0.01" name="pro_price" id="pro_price" class="form-control" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="pro_min">{{ __('app.Minimum_Order') }}</label>
                        <input type="number" name="pro_min" id="pro_min" class="form-control" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="category_id">{{ __('app.Category') }}</label>
                        <select name="category_id" id="category_id" class="form-control">
                            <option value="">{{ __('app.Select_Category') }}</option>
                            {{-- Categories will be loaded here via JavaScript --}}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">{{ __('app.Status') }}</label>
                        <select name="status" id="status" class="form-control">
                            <option value="pending">{{ __('app.Pending') }}</option>
                            <option value="active">{{ __('app.Active') }}</option>
                            <option value="inactive">{{ __('app.Inactive') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="visibility">{{ __('app.Visibility') }}</label>
                        <select name="visibility" id="visibility" class="form-control">
                            <option value="unpublished">{{ __('app.Unpublished') }}</option>
                            <option value="published">{{ __('app.Published') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="stock">{{ __('app.Stock') }}</label>
                        <input type="number" name="stock" id="stock" class="form-control" min="0">
                    </div>
                    <div class="form-group">
                        <label for="product_images">{{ __('app.Images') }}</label>
                        <input id="product_images" class="form-control" type="file" accept="image/png, image/gif, image/jpeg" name="images[]" multiple>
                        <small class="form-text text-muted">{{ __('app.multiple_images_allowed') }}</small>
                        <div id="image_preview_add" class="mt-2 d-flex flex-wrap image-preview-container"></div>
                    </div>
                    <div class="form-group">
                        <input class="btn btn-primary mr-1" type="submit" value="{{ __('app.Submit') }}">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Product Modal --}}
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">{{ __('app.Edit_Product') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" enctype="multipart/form-data" id="editProductForm" autocomplete="off">
                    @csrf
                    <input type="hidden" id="edit_pro_id" name="pro_id"> {{-- Corrected ID for hidden input --}}
                    <div class="form-group">
                        <label for="edit_pro_name">{{ __('app.Name') }}</label> {{-- Corrected ID --}}
                        <input type="text" name="pro_name" id="edit_pro_name" class="form-control" required> {{-- Corrected ID --}}
                    </div>
                    <div class="form-group">
                        <label for="edit_pro_details">{{ __('app.Description') }}</label> {{-- Corrected ID --}}
                        <textarea name="pro_details" id="edit_pro_details" class="form-control" required></textarea> {{-- Corrected ID --}}
                    </div>
                    <div class="form-group">
                        <label for="edit_pro_price">{{ __('app.Price') }}</label> {{-- Corrected ID --}}
                        <input type="number" step="0.01" name="pro_price" id="edit_pro_price" class="form-control" required min="0"> {{-- Corrected ID --}}
                    </div>
                    <div class="form-group">
                        <label for="edit_pro_min">{{ __('app.Minimum_Order') }}</label> {{-- Corrected ID --}}
                        <input type="number" name="pro_min" id="edit_pro_min" class="form-control" required min="1"> {{-- Corrected ID --}}
                    </div>
                    <div class="form-group">
                        <label for="edit_category_id">{{ __('app.Category') }}</label> {{-- Corrected ID --}}
                        <select name="category_id" id="edit_category_id" class="form-control"> {{-- Corrected ID --}}
                            <option value="">{{ __('app.Select_Category') }}</option>
                            {{-- Categories will be loaded here via JavaScript --}}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">{{ __('app.Status') }}</label> {{-- Corrected ID --}}
                        <select name="status" id="edit_status" class="form-control"> {{-- Corrected ID --}}
                            <option value="pending">{{ __('app.Pending') }}</option>
                            <option value="active">{{ __('app.Active') }}</option>
                            <option value="inactive">{{ __('app.Inactive') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_visibility">{{ __('app.Visibility') }}</label> {{-- Corrected ID --}}
                        <select name="visibility" id="edit_visibility" class="form-control"> {{-- Corrected ID --}}
                            <option value="unpublished">{{ __('app.Unpublished') }}</option>
                            <option value="published">{{ __('app.Published') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_stock">{{ __('app.Stock') }}</label> {{-- Corrected ID --}}
                        <input type="number" name="stock" id="edit_stock" class="form-control" min="0"> {{-- Corrected ID --}}
                    </div>
                    <div class="form-group">
                        <label for="editProductImages">{{ __('app.Images') }}</label>
                        <input id="editProductImages" class="form-control" type="file" accept="image/png, image/gif, image/jpeg" name="images[]" multiple>
                        <small class="form-text text-muted">{{ __('app.multiple_images_allowed') }}</small>
                        <div id="image_preview_edit" class="mt-2 d-flex flex-wrap image-preview-container"></div>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="{{ __('app.Submit') }}">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection