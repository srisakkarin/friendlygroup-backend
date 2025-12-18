$(document).ready(function () {
    // --- Sidebar Active State ---
    $(".sideBarli").removeClass("activeLi");
    $(".productListSideA").addClass("activeLi");

    // --- Function to Populate Categories Dropdown ---
    function populateCategories(selectElementId, selectedCategoryId = null) {
        $.ajax({
            type: "GET",
            url: `${domainUrl}getAllProductCategory`,
            success: function (response) {
                const selectElement = $(`#${selectElementId}`);
                selectElement.empty();
                selectElement.append($('<option value="">').text(app.Select_Category)); // Assuming app.Select_Category exists

                if (response.status === true && response.data) {
                    response.data.forEach(function (category) {
                        const option = $('<option>').val(category.id).text(category.name);
                        if (selectedCategoryId && category.id == selectedCategoryId) {
                            option.attr('selected', 'selected');
                        }
                        selectElement.append(option);
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching categories:", error);
                iziToast.show({
                    title: `${app.Error}!`,
                    message: app.failed_to_load_categories, // Assuming this message exists
                    color: app.redToast,
                    position: app.toastPosition,
                    transitionIn: app.transitionInAction,
                    transitionOut: app.transitionOutAction,
                    timeout: app.timeout,
                    animateInside: false,
                    iconUrl: app.cancleIcon,
                });
            }
        });
    }

    // --- Open Add Product Modal ---
    $(".addProductModalBtn").on("click", function (event) {
        event.preventDefault();
        $("#addProductForm")[0].reset();
        $("#image_preview_add").empty(); // Clear image preview for add
        // Clear file input value to allow selecting same file after closing
        $('#product_images').val('');
        populateCategories('category_id');
    });

    // --- DataTables Initialization ---
    const productsTable = $("#productsTable").DataTable({ // Assign to a variable for easier access
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[8, "desc"]], // Sort by the 9th column (index 8) which is 'pro_create'
        columns: [
            { data: 'product_images', name: 'product_images', orderable: false, searchable: false }, // Column 0: Image (no server sorting/searching)
            { data: 'pro_name', name: 'pro_name' }, // Column 1: Name
            { data: 'pro_price', name: 'pro_price' }, // Column 2: Price
            { data: 'pro_min', name: 'pro_min' }, // Column 3: Minimum Order
            { data: 'category_name', name: 'category_id' }, // Column 4: Category Name (name should be 'category_id' for sorting)
            { data: 'status', name: 'status' }, // Column 5: Status
            { data: 'visibility', name: 'visibility' }, // Column 6: Visibility
            { data: 'stock_qty', name: 'stock_qty' }, // Column 7: Stock (name should be 'stock_qty' for sorting)
            { data: 'pro_create', name: 'pro_create' }, // Column 8: Created At (name should be 'pro_create' for sorting)
            { data: 'pro_id', name: 'action', orderable: false, searchable: false } // Column 9: Action (data is pro_id to use in render)
        ],
        columnDefs: [
            {
                targets: 0, // Image column (data: 'product_images' - which is the first image URL from controller)
                render: function (data, type, row) {
                    if (data) {
                        // Ensure the URL is absolute or correctly prefixed for display
                        const imageUrl = data.startsWith('http') ? data : `${domainUrl}storage/${data}`;
                        return `<img src="${imageUrl}" height="50" width="50" class="rounded">`;
                    }
                    return '';
                },
            },
            {
                targets: 4, // Category column (data: 'category_name')
                // No render needed here if Controller sends the name directly
            },
            {
                targets: 5, // Status column (data: 'status')
                render: function (data, type, row) {
                    let badgeClass = '';
                    switch (data) {
                        case 'active': badgeClass = 'badge-success'; break;
                        case 'inactive': badgeClass = 'badge-danger'; break;
                        case 'pending': badgeClass = 'badge-warning'; break;
                        default: badgeClass = 'badge-secondary';
                    }
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            {
                targets: 6, // Visibility column (data: 'visibility')
                render: function (data, type, row) {
                    let badgeClass = '';
                    switch (data) {
                        case 'published': badgeClass = 'badge-info'; break;
                        case 'unpublished': badgeClass = 'badge-secondary'; break;
                        default: badgeClass = 'badge-light';
                    }
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            {
                targets: 7, // Stock column (data: 'stock_qty')
                // No specific render needed unless formatting
            },
            {
                targets: 8, // Created At column (data: 'pro_create')
                // No specific render needed unless formatting
            },
            {
                targets: 9, // Action column (data: 'pro_id')
                render: function (data, type, row) {
                    const stockValue = row.stock_qty || 0;
                    // product_images_raw will be an array of image objects: [{id: 1, product_id: 1, image: 'path/to/image.jpg'}, ...]
                    const imagesData = row.product_images_raw ? JSON.stringify(row.product_images_raw) : '[]';

                    return `
                        <div class="d-flex justify-content-end">
                            <a href="#" class="btn btn-primary shadow btn-xs sharp me-1 editProductBtn"
                                data-bs-toggle="modal" data-bs-target="#editProductModal"
                                data-id="${row.pro_id}"
                                data-name="${row.pro_name}"
                                data-details="${row.pro_details}"
                                data-price="${row.pro_price}"
                                data-min="${row.pro_min}"
                                data-category="${row.category_id}"
                                data-status="${row.status}"
                                data-visibility="${row.visibility}"
                                data-stock="${stockValue}"
                                data-images='${imagesData}'
                            >
                                <i class="fa fa-pencil-alt"></i>
                            </a>
                            <a href="#" class="btn btn-danger shadow btn-xs sharp deleteProductBtn" rel="${row.pro_id}">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    `;
                },
            },
        ],
        ajax: {
            url: `${domainUrl}fetchAllProducts`,
            type: "POST", // Specify the method as POST
            data: function (d) {
                // Any extra data can be added here
            },
        },
    });

    // --- Image Preview for Add Product ---
    $("#product_images").on("change", function () {
        const previewContainer = $("#image_preview_add");
        previewContainer.empty(); // Clear previous previews

        if (this.files && this.files[0]) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = $('<img>')
                        .attr('src', e.target.result)
                        .addClass('rounded m-1')
                        .css({ 'height': '80px', 'width': '80px', 'object-fit': 'cover' });
                    previewContainer.append(img);
                };
                reader.readAsDataURL(file); // Read file as Data URL (Base64)
            });
        }
    });

    // --- Submit Add Product Form ---
    $(document).on("submit", "#addProductForm", function (e) {
        e.preventDefault();
        const formData = new FormData(this); // Use FormData to handle file uploads

        // Append image files to FormData
        const imageFiles = $('#product_images')[0].files;
        for (let i = 0; i < imageFiles.length; i++) {
            formData.append(`images[${i}]`, imageFiles[i]); // Append each file
        }

        // Remove the regular 'images' input if it's there, as we're appending files manually
        formData.delete('images'); // Important if you have a name="images" attribute on input type="file"

        $.ajax({
            type: "POST",
            url: `${domainUrl}addProduct`, // Assuming this is your API endpoint for creating products
            data: formData,
            processData: false, // Don't process the data
            contentType: false, // Don't set content type (FormData handles it)
            success: function (response) {
                if (response.status) {
                    iziToast.success({
                        title: `${app.Success}!`,
                        message: response.message,
                        position: app.toastPosition,
                        transitionIn: app.transitionInAction,
                        transitionOut: app.transitionOutAction,
                        timeout: app.timeout,
                        animateInside: false,
                        iconUrl: app.checkIcon,
                    });
                    $("#addProductModal").modal("hide");
                    productsTable.ajax.reload(); // Reload DataTables
                } else {
                    iziToast.error({
                        title: `${app.Error}!`,
                        message: response.message,
                        position: app.toastPosition,
                        transitionIn: app.transitionInAction,
                        transitionOut: app.transitionOutAction,
                        timeout: app.timeout,
                        animateInside: false,
                        iconUrl: app.cancleIcon,
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Error creating product:", error);
                iziToast.error({
                    title: `${app.Error}!`,
                    message: app.Something_went_wrong, // Assuming this message exists
                    position: app.toastPosition,
                    transitionIn: app.transitionInAction,
                    transitionOut: app.transitionOutAction,
                    timeout: app.timeout,
                    animateInside: false,
                    iconUrl: app.cancleIcon,
                });
            },
        });
    });

    // --- Open Edit Product Modal ---
    $("#productsTable").on("click", ".editProductBtn", function (e) {
        e.preventDefault();
        // Clear previous previews and input
        $("#image_preview_edit").empty();
        $('#editProductImages').val('');

        const id = $(this).data("id");
        const name = $(this).data("name");
        const details = $(this).data("details");
        const price = $(this).data("price");
        const min = $(this).data("min");
        const category = $(this).data("category");
        const status = $(this).data("status");
        const visibility = $(this).data("visibility");
        const stock = $(this).data("stock");
        const productImagesRaw = $(this).data("images"); // This is already parsed if set via data attribute directly, but let's re-parse to be safe

        // Populate form fields
        $("#edit_pro_id").val(id);
        $("#edit_pro_name").val(name);
        $("#edit_pro_details").val(details);
        $("#edit_pro_price").val(price);
        $("#edit_pro_min").val(min);
        $("#edit_stock").val(stock);
        $("#edit_status").val(status);
        $("#edit_visibility").val(visibility);

        // Populate categories for edit form
        populateCategories('edit_category_id', category);

        // Display existing images
        const editImagePreview = $("#image_preview_edit");
        if (productImagesRaw && productImagesRaw.length > 0) {
            productImagesRaw.forEach(function (imgObj) { // imgObj is like {id: ..., product_id: ..., image: 'path'}
                const imageUrl = imgObj.image.startsWith('http') ? imgObj.image : `${domainUrl}storage/${imgObj.image}`;
                const img = $('<img>').attr('src', imageUrl).addClass('rounded m-1').css({ 'height': '80px', 'width': '80px', 'object-fit': 'cover' });
                editImagePreview.append(img);
            });
        }
    });

    // --- Image Preview for Edit Product ---
    $("#editProductImages").on("change", function () {
        const previewContainer = $("#image_preview_edit");
        previewContainer.empty(); // Clear existing previews (even if there were old ones)

        if (this.files && this.files[0]) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = $('<img>')
                        .attr('src', e.target.result)
                        .addClass('rounded m-1')
                        .css({ 'height': '80px', 'width': '80px', 'object-fit': 'cover' });
                    previewContainer.append(img);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    // --- Submit Edit Product Form ---
    $(document).on("submit", "#editProductForm", function (e) {
        e.preventDefault();
        const formData = new FormData(this); // Use FormData for file uploads

        // Append new image files to FormData
        const imageFiles = $('#editProductImages')[0].files;
        for (let i = 0; i < imageFiles.length; i++) {
            formData.append(`images[${i}]`, imageFiles[i]);
        }
        formData.delete('images'); // Remove the original images input if it's there

        // For `_method` spoofing in Laravel for PUT/PATCH requests
        // If your Laravel route is Route::post('/productUpdate', ...) then you don't need this.
        // If it's Route::put('/productUpdate/{id}', ...) or Route::patch('/productUpdate/{id}', ...)
        // then you might need to append _method. Assuming you're posting to productUpdate with ID in payload.
        // formData.append('_method', 'PUT'); // Uncomment if your route expects PUT/PATCH

        $.ajax({
            type: "POST", // Or PUT/PATCH depending on your Laravel route setup
            url: `${domainUrl}updateProduct`, // Assuming this is your API endpoint for updating products
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status) {
                    iziToast.success({
                        title: `${app.Success}!`,
                        message: response.message,
                        position: app.toastPosition,
                        transitionIn: app.transitionInAction,
                        transitionOut: app.transitionOutAction,
                        timeout: app.timeout,
                        animateInside: false,
                        iconUrl: app.checkIcon,
                    });
                    $("#editProductModal").modal("hide");
                    productsTable.ajax.reload(); // Reload DataTables
                } else {
                    iziToast.error({
                        title: `${app.Error}!`,
                        message: response.message,
                        position: app.toastPosition,
                        transitionIn: app.transitionInAction,
                        transitionOut: app.transitionOutAction,
                        timeout: app.timeout,
                        animateInside: false,
                        iconUrl: app.cancleIcon,
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Error updating product:", error);
                iziToast.error({
                    title: `${app.Error}!`,
                    message: app.Something_went_wrong,
                    position: app.toastPosition,
                    transitionIn: app.transitionInAction,
                    transitionOut: app.transitionOutAction,
                    timeout: app.timeout,
                    animateInside: false,
                    iconUrl: app.cancleIcon,
                });
            },
        });
    });

    // --- Delete Product ---
    $("#productsTable").on("click", ".deleteProductBtn", function (e) {
        e.preventDefault();
        const productId = $(this).attr("rel");
        swal({
            title: app.sure,
            icon: "error",
            buttons: true,
            dangerMode: true,
            buttons: ["Cancel", "Yes"],
        }).then((deleteValue) => {
            if (deleteValue) {
                if (deleteValue == true) {
                    $.ajax({
                        type: "POST", // Assuming POST for delete operation in Laravel
                        url: `${domainUrl}deleteProduct`, // Your delete endpoint
                        data: { pro_id: productId }, // Send product ID
                        success: function (response) {
                            if (response.status) {
                                iziToast.success({
                                    title: `${app.Success}!`,
                                    message: response.message,
                                    position: app.toastPosition,
                                    transitionIn: app.transitionInAction,
                                    transitionOut: app.transitionOutAction,
                                    timeout: app.timeout,
                                    animateInside: false,
                                    iconUrl: app.checkIcon,
                                });
                                productsTable.ajax.reload(); // Reload DataTables
                            } else {
                                iziToast.error({
                                    title: `${app.Error}!`,
                                    message: response.message,
                                    position: app.toastPosition,
                                    transitionIn: app.transitionInAction,
                                    transitionOut: app.transitionOutAction,
                                    timeout: app.timeout,
                                    animateInside: false,
                                    iconUrl: app.cancleIcon,
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Error deleting product:", error);
                            iziToast.error({
                                title: `${app.Error}!`,
                                message: app.Something_went_wrong,
                                position: app.toastPosition,
                                transitionIn: app.transitionInAction,
                                transitionOut: app.transitionOutAction,
                                timeout: app.timeout,
                                animateInside: false,
                                iconUrl: app.cancleIcon,
                            });
                        },
                    });
                }
            }
        });
    });

    // --- Clear form on modal close ---
    $('#addProductModal, #editProductModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset(); // Reset the form inside the modal
        $(this).find('.image-preview-container').empty(); // Clear any image previews
        // Clear file input value to allow selecting same file again
        $(this).find('input[type="file"]').val('');
    });
});