$(document).ready(function () {

    $(".sideBarli").removeClass("activeLi");
    $(".promotionPackageSideA").addClass("activeLi");

    $(".addModalBtn").on("click", function (event) {
        event.preventDefault();
        $('#addForm')[0].reset();
    });

    $('#table-22').dataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        "aaSorting": [[0, "desc"]],
        searching: false, 
        'columnDefs': [{
            'targets': [0, 1],
            'orderable': false,
        }],
        'ajax': {
            'url': `${domainUrl}fetchAllPromotionPackages`,
            'data': function (data) {

            }
        }
    });



    $("#table-22").on("click", ".edit_cats", function (event) {
        event.preventDefault();
        $('#edit_cat')[0].reset();
        var id = $(this).attr('rel');

        $('#editId').val($(this).attr('rel'));

        var url = `${domainUrl}getPromotionPackagesById` + "/" + id;

        $.getJSON(url).done(function (data) {
            console.log(data);

            $('#title').val(data.name);
            $('#appid').val(data.app_id);
            $('#playid').val(data.play_id);
            $('#duration_days').val(data.duration_days);
            $('#description').val(data.description);
            $('#price').val(data.price);
        });
        $('#edit_cat_modal').modal('show');
    });


    $("#edit_cat").on('submit', function (event) {
        event.preventDefault();
        $('.loader').show();



        if (user_type == "1") {


            var formdata = new FormData($("#edit_cat")[0]);
            console.log(formdata);


            $.ajax({
                url: `${domainUrl}updatePromotionPackages`,
                type: 'POST',
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    console.log(response);
                    $('#table-22').DataTable().ajax.reload(null, false);
                    $('#edit_cat')[0].reset();
                    $('.loader').hide();
                    $('#edit_cat_modal').modal('hide');


                },
                error: function (err) {
                    console.log(err);

                }

            });


        } else {
            iziToast.error({
                title: app.Error,
                message: app.tester,
                position: 'topRight'
            });
        }
    });





    $("#addForm").on('submit', function (event) {
        event.preventDefault();
        $('.loader').show();


        if (user_type == "1") {

            var formdata = new FormData($("#addForm")[0]);
            console.log(formdata);


            $.ajax({
                url: `${domainUrl}addPromotionPackages`,
                type: 'POST',
                data: formdata,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    console.log(response);
                    $('#table-22').DataTable().ajax.reload(null, false);
                    $('.loader').hide();
                    $('#addcat').modal('hide');
                    $('#addForm')[0].reset();

                    if (response.status == false) {

                        iziToast.error({
                            title: app.Error,
                            message: app.minimumPackage,
                            position: 'topRight'
                        });
                    }


                },
                error: function (err) {
                    console.log(err);

                }

            });
        } else {
            iziToast.error({
                title: app.Error,
                message: app.tester,
                position: 'topRight'
            });
        }

    });

    $("#table-22").on("click", ".delete-cat", function (event) {

        event.preventDefault();



        swal({
            title: `${app.sure}`,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    swal(app.Yourpackagehasbeendeleted, {
                        icon: "success",
                    });

                    if (user_type == "1") {
                        var element = $(this).parent();

                        var cat_id = $(this).attr("rel");
                        var delete_cat_url = `${domainUrl}deletePromotionPackages` + "/" + cat_id;

                        $.getJSON(delete_cat_url).done(function (data) {
                            console.log(data);
                            $('#table-22').DataTable().ajax.reload(null, false);
                        });



                    } else {
                        iziToast.error({
                            title: app.Error,
                            message: app.tester,
                            position: 'topRight'
                        });
                    }

                } else {
                    swal(app.packageSafe);
                }
            });


    });
});
