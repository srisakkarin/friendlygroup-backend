$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");
    $(".usersSideA").addClass("activeLi");

    // ฟังก์ชันสำหรับโหลดตาราง Users ตาม Role
    function loadUsersTable(role = 'all') {
        $("#UsersTable").dataTable({
            destroy: true, // สำคัญ! ต้อง destroy ตัวเก่าก่อนโหลดใหม่
            processing: true,
            serverSide: true,
            serverMethod: "post",
            aaSorting: [[0, "desc"]],
            columnDefs: [
                {
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    orderable: false,
                },
            ],
            ajax: {
                url: `${domainUrl}fetchAllUsers`,
                data: function (data) {
                    data.role = role; // ส่งค่า Role ไปที่ Controller
                },
                error: (error) => {
                    console.log(error);
                },
            },
        });
    }

    // โหลดครั้งแรก (All Users)
    loadUsersTable('all');

    // เมื่อกด Tab เปลี่ยน Role
    $(".role-filter").on("click", function () {
        var role = $(this).data("role");
        loadUsersTable(role);
    });

    // ส่วนของ Fake Users Table (โหลดเมื่อกด Tab Fake Users)
    $("#fakeUserTab").on("click", function () {
        $("#FakeUsersTable").dataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            serverMethod: "post",
            aaSorting: [[0, "desc"]],
            columnDefs: [
                {
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                    orderable: false,
                },
            ],
            ajax: {
                url: `${domainUrl}fetchFakeUsers`,
                data: function (data) { },
                error: (error) => {
                    console.log(error);
                },
            },
        });
    });

    // --- ส่วนจัดการอื่นๆ (Add Coins, Block, Unblock) คงเดิม ---

    $("#UsersTable").on("click", ".addCoins", function (e) {
        e.preventDefault();
        var user_id = $(this).attr("data-id");
        $("#userId").val(user_id);
        $("#addCoinsModal").modal("show");
    });

    $(document).on("submit", "#addCoinsForm", function (e) {
        e.preventDefault();
        var formdata = new FormData($("#addCoinsForm")[0]);
        $(".loader").show();

        $.ajax({
            url: `${domainUrl}addCoinsToUserWalletFromAdmin`,
            type: "POST",
            data: formdata,
            dataType: "json",
            contentType: false,
            cache: false,
            processData: false,
            success: function (data) {
                $(".loader").hide();
                $("#addCoinsModal").modal("hide");
                if (data.success == 1) {
                    $("#addCoinsForm")[0].reset();
                    $("#UsersTable").DataTable().ajax.reload(null, false);
                    iziToast.success({
                        title: "Success!",
                        message: "Changes applied successfully!",
                        position: "topRight",
                    });
                } else {
                    iziToast.error({
                        title: "Error!",
                        message: data.message,
                        position: "topRight",
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            },
        });
    });

    $(document).on("click", ".block", function (e) {
        e.preventDefault();
        if (typeof user_type !== 'undefined' && user_type == 1) {
            var id = $(this).attr("rel");
            swal({
                title: app.sure,
                icon: "error",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancel", "Yes"],
            }).then((deleteValue) => {
                if (deleteValue) {
                    $.ajax({
                        type: "POST",
                        url: `${domainUrl}blockUser`,
                        dataType: "json",
                        data: { user_id: id },
                        success: function (response) {
                            if (response.status == true) {
                                iziToast.show({
                                    title: app.Success,
                                    message: app.thisUserHasBeenBlocked,
                                    color: app.greenToast,
                                    position: app.toastPosition,
                                    iconUrl: app.checkCircleIcon,
                                });
                                $("#UsersTable").DataTable().ajax.reload(null, false);
                                $("#FakeUsersTable").DataTable().ajax.reload(null, false);
                            }
                        },
                    });
                }
            });
        } else {
            iziToast.show({
                title: `${app.Error}!`,
                message: app.tester,
                color: app.redToast,
                position: app.toastPosition,
                iconUrl: app.cancleIcon,
            });
        }
    });

    $(document).on("click", ".unblock", function (e) {
        e.preventDefault();
        if (typeof user_type !== 'undefined' && user_type == 1) {
            var id = $(this).attr("rel");
            swal({
                title: app.sure,
                icon: "error",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancel", "Yes"],
            }).then((deleteValue) => {
                if (deleteValue) {
                    $.ajax({
                        type: "POST",
                        url: `${domainUrl}unblockUser`,
                        dataType: "json",
                        data: { user_id: id },
                        success: function (response) {
                            if (response.status == true) {
                                iziToast.show({
                                    title: app.Success,
                                    message: app.thisUserHasBeenUnblocked,
                                    color: app.greenToast,
                                    position: app.toastPosition,
                                    iconUrl: app.checkCircleIcon,
                                });
                                $("#UsersTable").DataTable().ajax.reload(null, false);
                                $("#FakeUsersTable").DataTable().ajax.reload(null, false);
                            }
                        },
                    });
                }
            });
        } else {
            iziToast.show({
                title: `${app.Error}!`,
                message: app.tester,
                color: app.redToast,
                position: app.toastPosition,
                iconUrl: app.cancleIcon,
            });
        }
    });
});