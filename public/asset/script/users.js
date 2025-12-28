$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");
    $(".usersSideA").addClass("activeLi");

    $("#UsersTable").dataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5, 6, 7, 8,9,10],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchAllUsers`,
            data: function (data) { },
            error: (error) => {
                console.log(error);
            },
        },
    });

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

    $("#allUserTab").on("click", function (e) {
        // alert('allUserTab Click !!!');
        $("#UsersTable").dataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            serverMethod: "post",
            aaSorting: [[0, "desc"]],
            columnDefs: [
                {
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8,9,10],
                    orderable: false,
                },
            ],
            ajax: {
                url: `${domainUrl}fetchAllUsers`,
                data: function (data) { },
                error: (error) => {
                    console.log(error);
                },
            },
        });
    });

    $("#StreamersTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5, 6, 7,8,9],
                orderable: false,
            },
        ],
        ajax: {
            url: `${domainUrl}fetchStreamerUsers`,
            data: function (data) { },
            error: (error) => {
                console.log(error);
            },
        },
    });

    $("#FakeUsersTable").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "post",
        aaSorting: [[0, "desc"]],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5, 6, 7,8,9],
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

    $(document).on("click", ".block", function (e) {
        e.preventDefault();
        if (user_type == 1) {
            var id = $(this).attr("rel");
            swal({
                title: app.sure,
                icon: "error",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancel", "Yes"],
            }).then((deleteValue) => {
                if (deleteValue) {
                    if (deleteValue == true) {
                        console.log(true);
                        $.ajax({
                            type: "POST",
                            url: `${domainUrl}blockUser`,
                            dataType: "json",
                            data: {
                                user_id: id,
                            },
                            success: function (response) {
                                if (response.status == false) {
                                    console.log(response.message);
                                } else if (response.status == true) {
                                    iziToast.show({
                                        title: app.Success,
                                        message: app.thisUserHasBeenBlocked,
                                        color: app.greenToast,
                                        position: app.toastPosition,
                                        transitionIn: app.fadeInAction,
                                        transitionOut: app.fadeOutAction,
                                        timeout: app.timeout,
                                        animateInside: false,
                                        iconUrl: app.checkCircleIcon,
                                    });
                                    $("#UsersTable").DataTable().ajax.reload(null, false);
                                    $("#FakeUsersTable").DataTable().ajax.reload(null, false);
                                    $("#StreamersTable").DataTable().ajax.reload(null, false);
                                }
                            },
                        });
                    }
                }
            });
        } else {
            iziToast.show({
                title: `${app.Error}!`,
                message: app.tester,
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

    $(document).on("click", ".unblock", function (e) {
        e.preventDefault();
        if (user_type == 1) {
            var id = $(this).attr("rel");
            swal({
                title: app.sure,
                icon: "error",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancel", "Yes"],
            }).then((deleteValue) => {
                if (deleteValue) {
                    if (deleteValue == true) {
                        console.log(true);
                        $.ajax({
                            type: "POST",
                            url: `${domainUrl}unblockUser`,
                            dataType: "json",
                            data: {
                                user_id: id,
                            },
                            success: function (response) {
                                if (response.status == false) {
                                    console.log(response.message);
                                } else if (response.status == true) {
                                    iziToast.show({
                                        title: app.Success,
                                        message: app.thisUserHasBeenUnblocked,
                                        color: app.greenToast,
                                        position: app.toastPosition,
                                        transitionIn: app.fadeInAction,
                                        transitionOut: app.fadeOutAction,
                                        timeout: app.timeout,
                                        animateInside: false,
                                        iconUrl: app.checkCircleIcon,
                                    });
                                    $("#UsersTable").DataTable().ajax.reload(null, false);
                                    $("#FakeUsersTable").DataTable().ajax.reload(null, false);
                                    $("#StreamersTable").DataTable().ajax.reload(null, false);
                                }
                            },
                        });
                    }
                }
            });
        } else {
            iziToast.show({
                title: `${app.Error}!`,
                message: app.tester,
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

    async function fetchPackages() {
        const url = `${domainUrl}getPackages`;
        try {
            const response = await fetch(url);
            const json = await response.json();
            if (json.status === true) {
                console.log(json.data);
                const myTab = document.getElementById('myTab');

                // Start appending new <li> elements after the existing ones
                if (json.data.length > 0) {
                    json.data.forEach((item, index) => {
                        // Create a new <li> element with the required attributes
                        const li = document.createElement("li");
                        li.setAttribute("role", "presentation");
                        li.classList.add("nav-item");

                        // Create an <a> element inside the <li>
                        const a = document.createElement("a");
                        a.classList.add("nav-link", "pointer");
                        a.setAttribute("href", "#Section1"); // Dynamic href starting from Section4
                        a.setAttribute("role", "tab");
                        a.setAttribute("data-toggle", "tab");

                        // Set the text content for the <a> tag
                        a.textContent = item.name || `Item ${index + 1}`;  // Assuming 'name' is a property in your data

                        // Add a badge inside the <a> tag
                        const badge = document.createElement("span");
                        badge.classList.add("badge", "badge-transparent", "total_close_complaint");
                        badge.textContent = item.badgeCount || '';  // Assuming 'badgeCount' is a property in your data

                        // Append the badge to the <a> tag
                        a.appendChild(badge);

                        // Append the <a> tag to the <li>
                        li.appendChild(a);

                        // Append the <li> to the parent <ul>
                        myTab.appendChild(li);

                        // Add click event listener to fetch data when the tab is clicked
                        a.addEventListener('click', async (event) => {
                            event.preventDefault(); // Prevent default anchor behavior 
                            $("#UsersTable").dataTable({
                                destroy: true,
                                processing: true,
                                serverSide: true,
                                serverMethod: "post",
                                aaSorting: [[0, "desc"]],
                                columnDefs: [
                                    {
                                        targets: [0, 1, 2, 3, 4, 5, 6, 7, 8,9,10],
                                        orderable: false,
                                    },
                                ],
                                ajax: {
                                    url: `${domainUrl}fetchAllUsers`,
                                    data: function (data) {
                                        data.package_id = item.id
                                    },
                                    error: (error) => {
                                        console.log(error);
                                    },
                                },
                            });
                        });
                    });
                }
            } else {
                console.error("Failed to fetch packages: ", json.message || "Unknown error");
            }
        } catch (error) {
            console.error("Error fetching packages: ", error);
        }
    }
    setTimeout(() => {
        // fetchPackages();
    }, 500);

});
