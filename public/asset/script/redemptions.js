$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");
    $(".redemptionSideA").addClass("activeLi"); // อย่าลืมเพิ่ม class นี้ใน sidebar ถ้าต้องการ highlight

    var table = $("#tableRedemption").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "GET",
        aaSorting: [[4, "desc"]], // Sort by Date (index 4)
        ajax: {
            url: `${domainUrl}get-redemptions`,
            data: function (d) {
                d.per_page = d.length;
                d.page = (d.start / d.length) + 1;
                d.status = $("#filter_status").val();
            },
            dataSrc: function (json) {
                json.recordsTotal = json.pagination.total;
                json.recordsFiltered = json.pagination.total;
                return json.data;
            }
        },
        columns: [
            { 
                data: 'user',
                render: function(data) {
                    return data ? `${data.fullname} <br><small class="text-muted">${data.phone || data.email}</small>` : 'Unknown';
                }
            },
            { 
                data: 'reward',
                render: function(data) {
                    return data ? data.name : 'Deleted Reward';
                }
            },
            { 
                data: 'reward',
                render: function(data) {
                    if(!data) return '-';
                    return data.type === 'discount' 
                        ? '<span class="badge badge-info">Coupon</span>' 
                        : '<span class="badge badge-warning">Gift</span>';
                }
            },
            { 
                data: 'points_used',
                render: function(data) {
                    return `<span class="text-danger">-${data}</span>`;
                }
            },
            { 
                data: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleString('th-TH');
                }
            },
            { 
                data: 'is_used',
                render: function(data) {
                    return data == 1 
                        ? '<span class="badge badge-success">Used</span>' 
                        : '<span class="badge badge-light">Pending</span>';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    if (row.is_used == 0) {
                        return `<button class="btn btn-primary btn-sm mark-used" data-id="${row.id}">Mark Used</button>`;
                    }
                    return '<i class="fas fa-check text-success"></i>';
                }
            }
        ]
    });

    // Reload table when filter changes
    $("#filter_status").on("change", function() {
        $("#tableRedemption").DataTable().ajax.reload();
    });

    // Mark as Used Action
    $("#tableRedemption").on("click", ".mark-used", function (e) {
        e.preventDefault();
        var id = $(this).data("id");

        swal({
            title: "Confirm Usage?",
            text: "Mark this reward as used/delivered?",
            icon: "info",
            buttons: true,
        }).then((confirm) => {
            if (confirm) {
                $.ajax({
                    type: "POST",
                    url: `${domainUrl}mark-redemption-used`,
                    data: { id: id },
                    success: function (response) {
                        if (response.status) {
                            iziToast.success({ title: 'Success', message: response.message, position: 'topRight' });
                            $("#tableRedemption").DataTable().ajax.reload(null, false);
                        } else {
                            iziToast.error({ title: 'Error', message: response.message, position: 'topRight' });
                        }
                    }
                });
            }
        });
    });
});