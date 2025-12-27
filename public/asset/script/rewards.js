$(document).ready(function () {
    // 1. ตั้งค่า Sidebar Active
    $(".sideBarli").removeClass("activeLi");
    $(".rewardSideA").addClass("activeLi");

    // 2. Clear Form เมื่อกดปุ่มเพิ่ม
    $(".btn-primary[data-target='#modalAdd']").on("click", function (event) {
        $("#formAddReward")[0].reset();
        $("#add_discount_section").hide();
        $("#preview_image_container").empty();
    });

    // 3. จัดการ DataTables
    $("#tableReward").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "GET",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            { targets: [0, 4, 6], orderable: false },
        ],
        ajax: {
            url: `${domainUrl}get-rewards`,
            data: function (d) {
                d.per_page = d.length;
                d.page = (d.start / d.length) + 1;
            },
            dataSrc: function (json) {
                json.recordsTotal = json.pagination.total;
                json.recordsFiltered = json.pagination.total;
                return json.data;
            }
        },
        columns: [
            { 
                data: 'image',
                render: function(data) {
                    return data ? `<img src="${data}" width="50" class="img-thumbnail">` : '-';
                }
            },
            { data: 'name' },
            { 
                data: 'type',
                render: function(data) {
                    return data === 'discount' 
                        ? '<span class="badge badge-info">Discount</span>' 
                        : '<span class="badge badge-success">Gift</span>';
                }
            },
            { data: 'required_points' },
            { 
                data: null,
                render: function(data, type, row) {
                    if (row.type === 'discount') {
                        return row.discount_type === 'percent' 
                            ? `<span class="text-danger">${row.discount_value}%</span>` 
                            : `<span class="text-success">${row.discount_value} THB</span>`;
                    }
                    return '-';
                }
            },
            { 
                data: 'is_active',
                render: function(data) {
                    return data == 1 
                        ? '<span class="badge badge-success">Active</span>' 
                        : '<span class="badge badge-secondary">Inactive</span>';
                }
            },
            {
                data: null,
                className: 'text-right',
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm edit" data-id="${row.id}" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm delete" data-id="${row.id}" title="Delete"><i class="fas fa-trash"></i></button>
                    `;
                }
            }
        ]
    });

    // 4. Toggle Discount Fields
    $("#add_type, #edit_type").on("change", function() { // เพิ่ม #edit_type ให้ทำงานด้วย
        let targetId = $(this).attr('id') === 'add_type' ? '#add_discount_section' : '#edit_discount_section';
        if ($(this).val() === "discount") {
            $(targetId).slideDown();
        } else {
            $(targetId).slideUp();
        }
    });

    // 5. Add Reward
    $(document).on("submit", "#formAddReward", function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            type: "POST",
            url: `${domainUrl}add-reward`,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.status) {
                    iziToast.success({
                        title: 'Success',
                        message: response.message,
                        position: 'topRight'
                    });
                    $("#tableReward").DataTable().ajax.reload(null, false);
                    $("#modalAdd").modal("hide");
                } else {
                    iziToast.error({ title: 'Error', message: response.message, position: 'topRight' });
                }
            }
        });
    });

    // 6. Click Edit (Fetch Data)
    $("#tableReward").on("click", ".edit", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $("#formEditReward")[0].reset();
        $("#preview_image_container").empty();

        $.ajax({
            type: "GET", // เปลี่ยนเป็น GET ตาม Route: Route::get('/get-rewards'...) หรือสร้าง Route ใหม่สำหรับ get-by-id
            // หมายเหตุ: ใน code เดิมคุณใช้ POST เพื่อ get-by-id แต่ปกติควรเป็น GET
            // ถ้า Route ของคุณคือ Route::post('/get-reward-by-id'...) ให้ใช้ POST เหมือนเดิม
            // แต่ถ้ายังไม่มี Route นี้ ต้องเพิ่มใน web.php ด้วย: Route::post('/get-reward-by-id', [RewardController::class, 'getRewardById']);
            url: `${domainUrl}get-reward-by-id`, 
            type: "POST", // ใช้ POST ตามที่คุณน่าจะตั้งไว้ใน controller
            data: { id: id },
            dataType: "json",
            success: function(response) {
                if (response.status) {
                    let reward = response.data;
                    $("#edit_id").val(reward.id);
                    $("#edit_name").val(reward.name);
                    $("#edit_required_points").val(reward.required_points);
                    $("#edit_type").val(reward.type).trigger('change');
                    $("#edit_is_active").val(reward.is_active);
                    $("#edit_description").val(reward.description);

                    if (reward.type === 'discount') {
                        $("#edit_discount_type").val(reward.discount_type);
                        $("#edit_discount_value").val(reward.discount_value);
                    }
                    if (reward.image) {
                        $("#preview_image_container").html(`<img src="${reward.image}" width="100" class="img-thumbnail mt-2">`);
                    }
                    $("#modalEdit").modal("show");
                }
            }
        });
    });

    // 8. Update Reward
    $(document).on("submit", "#formEditReward", function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        // formData จะดึงค่าจาก <input name="id"> ในฟอร์มไปด้วยโดยอัตโนมัติ

        $.ajax({
            type: "POST",
            url: `${domainUrl}update-reward`,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.status) {
                    iziToast.success({
                        title: 'Success',
                        message: response.message,
                        position: 'topRight'
                    });
                    $("#tableReward").DataTable().ajax.reload(null, false);
                    $("#modalEdit").modal("hide");
                } else {
                    iziToast.error({ title: 'Error', message: response.message, position: 'topRight' });
                }
            }
        });
    });

    // 9. Delete Reward (จุดที่แก้หลัก)
    $("#tableReward").on("click", ".delete", function (e) {
        e.preventDefault();
        var id = $(this).data("id");

        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this reward!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    type: "POST",
                    // แก้ไขตรงนี้: ต้องใส่ ID ต่อท้าย URL ให้ตรงกับ Route parameter {id}
                    url: `${domainUrl}delete-reward/${id}`, 
                    dataType: "json",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') // ส่ง CSRF Token ไปด้วย (ถ้าไม่ได้ตั้ง global)
                    },
                    success: function (response) {
                        if (response.status) {
                            iziToast.success({
                                title: 'Success',
                                message: response.message,
                                position: 'topRight'
                            });
                            $("#tableReward").DataTable().ajax.reload(null, false);
                        } else {
                            swal("Error!", response.message, "error");
                        }
                    },
                    error: function(xhr) {
                        // เพิ่มการแสดง Error ถ้ามีปัญหา 404 หรือ 500
                        swal("Error!", "Something went wrong", "error");
                        console.log(xhr.responseText);
                    }
                });
            }
        });
    });
});