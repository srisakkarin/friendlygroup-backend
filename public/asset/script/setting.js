$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");
    $(".paymentSettingSideA").removeClass("activeLi");
    $(".incomeSettingSideA").removeClass("activeLi");
    $(".otherSideA").removeClass("activeLi");
    $(".systemSettingSideA").addClass("activeLi");
    $('#setting-dropdown').css('display', 'block');

    $(document).on("change", "#is_dating", function (event) {
        event.preventDefault();

        if ($(this).prop("checked") == true) {
            var value = 1;
        } else {
            value = 0;
        }

        var updateEventStatusUrl =
            `${domainUrl}changeFromDatingAppToLivestreamApp` + "/" + value;

        $.getJSON(updateEventStatusUrl).done(function (data) {
            if (data.status) {
                iziToast.success({
                    title: "Update Successful..",
                    message: "Settings Updated Successfully !",
                    position: "topRight",
                });
            } else {
                iziToast.error({
                    title: "Failed!",
                    message: "Something went wrong!",
                    position: "topRight",
                });
            }
        });
    });
    $(document).on("change", "#is_social_media", function (event) {
        event.preventDefault();

        if ($(this).prop("checked") == true) {
            var value = 1;
        } else {
            value = 0;
        }

        var updateEventStatusUrl =
            `${domainUrl}changeFromSocialMedia` + "/" + value;

        $.getJSON(updateEventStatusUrl).done(function (data) {
            if (data.status) {
                iziToast.success({
                    title: "Update Successful..",
                    message: "Settings Updated Successfully !",
                    position: "topRight",
                });
            } else {
                iziToast.error({
                    title: "Failed!",
                    message: "Something went wrong!",
                    position: "topRight",
                });
            }
        });
    });

    $(".appdataForm").on("submit", function (event) {
        event.preventDefault();

        if (user_type == "1") {
            var formdata = new FormData($(this)[0]);

            $.ajax({
                url: `${domainUrl}updateAppdata`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    console.log(response);

                    if (response.status == true) {
                        // location.reload();
                        iziToast.success({
                            title: `${app.Success}!`,
                            message: `${app.settingUpdatedSuccessfully}`,
                            position: "topRight",
                        });
                    }
                },
                error: function (err) {
                    $(".loader").hide();

                    console.log(err);
                },
            });
        } else {
            $(".loader").hide();
            iziToast.error({
                title: `${app.Error}!`,
                message: `${app.tester}`,
                position: "topRight",
            });
        }
    });

    $(".otherForm").on("submit", function (event) {
        event.preventDefault();
        $(".loader").show();

        if (user_type == "1") {
            var formdata = new FormData($(this)[0]);

            $.ajax({
                url: `${domainUrl}updateOther`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    console.log(response);

                    if (response.status == true) {
                        location.reload();
                    }
                },
                error: function (err) {
                    $(".loader").hide();

                    console.log(JSON.stringify(err));
                },
            });
        } else {
            $(".loader").hide();
            iziToast.error({
                title: `${app.Error}!`,
                message: `${app.tester}`,
                position: "topRight",
            });
        }
    });

    // In App Image Setting
    $("#btnAddLoginImage").on("click", function (event) {
        event.preventDefault();
        $("#addLoginImageModal").modal("show");
    });

    // Image Add
    $("#addLoginForm").submit(function (e) {
        e.preventDefault();
        if (user_type == 1) {
            var formdata = new FormData($("#addLoginForm")[0]);

            console.log(formdata);
            $.ajax({
                url: `${domainUrl}addInAppImage`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    if (response.status == true) {
                        console.log(response.status);
                        window.location.href = "";
                    } else if (response.status == false) {
                        console.log(err);
                    }
                },
                error: function (err) {
                    console.log(err);
                },
            });
        } else {
            iziToast.error({
                title: "Tester Login",
                message: "you are tester",
                position: "topRight",
                timeOut: 4000,
            });
        }
    });
    $("#btnAddRegisterImage").on("click", function (event) {
        event.preventDefault();
        $("#addRegisterImageModal").modal("show");
    });

    // Image Add
    $("#addRegisterForm").submit(function (e) {
        e.preventDefault();
        if (user_type == 1) {
            var formdata = new FormData($("#addRegisterForm")[0]);

            console.log(formdata);
            $.ajax({
                url: `${domainUrl}addInAppImage`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    if (response.status == true) {
                        console.log(response.status);
                        window.location.href = "";
                    } else if (response.status == false) {
                        console.log(err);
                    }
                },
                error: function (err) {
                    console.log(err);
                },
            });
        } else {
            iziToast.error({
                title: "Tester Login",
                message: "you are tester",
                position: "topRight",
                timeOut: 4000,
            });
        }
    });
    $("#btnAddWelcomeImage").on("click", function (event) {
        event.preventDefault();
        $("#addWelcomeImageModal").modal("show");
    });

    // Image Add
    $("#addWelcomeForm").submit(function (e) {
        e.preventDefault();
        if (user_type == 1) {
            var formdata = new FormData($("#addWelcomeForm")[0]);

            console.log(formdata);
            $.ajax({
                url: `${domainUrl}addInAppImage`,
                type: "POST",
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    if (response.status == true) {
                        console.log(response.status);
                        window.location.href = "";
                    } else if (response.status == false) {
                        console.log(err);
                    }
                },
                error: function (err) {
                    console.log(err);
                },
            });
        } else {
            iziToast.error({
                title: "Tester Login",
                message: "you are tester",
                position: "topRight",
                timeOut: 4000,
            });
        }
    });

    $(document).on("click", ".btnInAppImageRemove", function (event) {
        event.preventDefault();
        var imgUrl = $(this).data("imgurl");
        var fieldName = $(this).data("fieldname"); 
        swal({
            title: app.sure,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                if (user_type == "1") {
                    var url = `${domainUrl}deleteInAppImage` + "?fieldName=" + fieldName + "&imgUrl=" + imgUrl;
                    $.getJSON(url).done(function (response) {
                        if (response.status == true) {
                            console.log(response.status);
                            location.reload();
                        } else if (response.status == false) {
                            console.log(response);
                            iziToast.error({
                                title: `${app.Error}!`,
                                message: response.message,
                                position: "topRight",
                            });
                        }
                    });
                } else {
                    iziToast.error({
                        title: `${app.Error}!`,
                        message: app.tester,
                        position: "topRight",
                    });
                }
            }
        });
    });
});
