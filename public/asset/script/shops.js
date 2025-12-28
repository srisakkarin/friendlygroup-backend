$(document).ready(function () {
    // 1. จัดการ Active Menu ใน Sidebar
    $(".sideBarli").removeClass("activeLi");
    $(".shopsSideA").addClass("activeLi");

    // 2. ตั้งค่า DataTable
    var table = $("#tableShops").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${domainUrl}fetchShops`,
            type: "POST",
            error: function (xhr, error, code) {
                console.log(xhr);
                iziToast.error({
                    title: 'Error',
                    message: 'ไม่สามารถโหลดข้อมูลได้',
                    position: 'topRight'
                });
            }
        },
        columns: [
            { 
                data: "logo", 
                orderable: false, 
                searchable: false,
                render: function(data) { 
                    return data; 
                }
            },
            { data: "name" },
            { data: "code" },
            { data: "address" },
            { data: "is_active" },
            { data: "action", orderable: false, searchable: false }
        ],
        // order: [[1, 'asc']] // เรียงตามชื่อร้านเป็นค่าเริ่มต้น
    });

    // 3. ปุ่มกดเพิ่มร้านค้า (Add Shop)
    $("#btnAddShop").click(function () {
        $("#formShop")[0].reset();      // ล้างค่าในฟอร์ม
        $("#shopId").val("");           // ล้าง ID (เพื่อให้รู้ว่าเป็น Create)
        
        // จัดการ Preview Image
        $('#previewLogo').attr('src', '').hide(); 
        $('#shopLogo').val(''); // ล้าง input file

        $("#modalTitle").text("Add Shop");
        $("#modalShop").modal("show");
    });

    // 4. ฟังชั่นก์ Preview รูปเมื่อเลือกไฟล์ (UX เพิ่มเติม)
    $("#shopLogo").change(function(){
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event){
                $('#previewLogo').attr('src', event.target.result).show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#previewLogo').hide();
        }
    });

    // 5. บันทึกข้อมูล (Save / Update)
    $("#formShop").submit(function (e) {
        e.preventDefault();
        
        // ใช้ FormData เพื่อรองรับการอัปโหลดไฟล์
        var formData = new FormData(this);

        // แสดง Loading (ถ้ามี class loader)
        // $(".loader").show(); 

        $.ajax({
            url: `${domainUrl}saveShop`,
            type: "POST",
            data: formData,
            contentType: false, // จำเป็นสำหรับ upload file
            processData: false, // จำเป็นสำหรับ upload file
            success: function (res) {
                // $(".loader").hide();
                if (res.status) {
                    $("#modalShop").modal("hide");
                    table.ajax.reload(null, false); // Reload ตารางโดยไม่เปลี่ยนหน้า
                    iziToast.success({ 
                        title: "Success", 
                        message: res.message, 
                        position: "topRight" 
                    });
                } else {
                    iziToast.error({ 
                        title: "Error", 
                        message: res.message, 
                        position: "topRight" 
                    });
                }
            },
            error: function(xhr) {
                // $(".loader").hide();
                iziToast.error({ 
                    title: "Error", 
                    message: 'Something went wrong!', 
                    position: "topRight" 
                });
            }
        });
    });

    // 6. ปุ่มแก้ไข (Edit)
    $(document).on("click", ".editShop", function () {
        var id = $(this).data("id");
        
        // ดึงข้อมูลเก่ามาแสดง
        $.get(`${domainUrl}getShop/${id}`, function (res) {
            if (res.status) {
                $("#shopId").val(res.data.id);
                $("#shopName").val(res.data.name);
                $("#shopCode").val(res.data.code);
                $("#shopAddress").val(res.data.address);
                $("#shopStatus").val(res.data.is_active);

                // แสดงรูปเก่า (ถ้ามี)
                if (res.data.logo_url) {
                    $('#previewLogo').attr('src', res.data.logo_url).show();
                } else {
                    $('#previewLogo').attr('src', '').hide();
                }

                $("#modalTitle").text("Edit Shop");
                $("#modalShop").modal("show");
            }
        });
    });

    // 7. ปุ่มลบ (Delete)
    $(document).on("click", ".deleteShop", function () {
        var id = $(this).data("id");
        
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this shop!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: `${domainUrl}deleteShop/${id}`,
                    type: "POST",
                    // ส่ง CSRF Token ไปด้วย (ถ้าไม่ได้ตั้ง global setup)
                    data: { _token: $('meta[name="csrf-token"]').attr('content') }, 
                    success: function (res) {
                        if (res.status) {
                            table.ajax.reload(null, false);
                            iziToast.success({ 
                                title: "Success", 
                                message: res.message, 
                                position: "topRight" 
                            });
                        } else {
                            iziToast.error({ 
                                title: "Error", 
                                message: res.message, 
                                position: "topRight" 
                            });
                        }
                    }
                });
            }
        });
    });
});