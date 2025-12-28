$(document).ready(function () {
    $(".sideBarli").removeClass("activeLi");
    $(".pointsSideA").addClass("activeLi");

    var table = $("#tablePoints").dataTable({
        processing: true,
        serverSide: true,
        serverMethod: "GET",
        aaSorting: [[1, "desc"]], 
        ajax: {
            url: `${domainUrl}get-users-points`,
            error: function (xhr) {
                console.log("DataTables Error:", xhr);
                iziToast.error({
                    title: 'Load Error',
                    message: xhr.responseJSON ? xhr.responseJSON.error : 'Server Error',
                    position: 'topRight'
                });
            }
        },
        columns: [
            { 
                data: 'fullname', 
                name: 'fullname', 
                render: function(data, type, row) {
                    // ✅ แสดง Username และ Identity แทน
                    return `
                        <div class="font-weight-bold text-dark">${data || 'No Name'}</div>
                        <div class="small text-muted">
                            <i class="fas fa-user-tag"></i> ${row.username || '-'} | 
                            <i class="fas fa-id-card"></i> ${row.identity || '-'}
                        </div>
                    `;
                }
            },
            { 
                data: 'points',
                name: 'points',
                render: function(data) {
                    return `<h5 class="text-primary m-0">${new Intl.NumberFormat().format(data)} <small>pts</small></h5>`;
                }
            },
            {
                data: null,
                orderable: false, 
                className: 'text-right',
                render: function(data, type, row) {
                    // ใช้ row.id และ row.fullname ตามปกติ
                    return `
                        <button class="btn btn-info btn-sm btn-history" data-id="${row.id}" data-name="${row.fullname}" title="History"><i class="fas fa-history"></i></button>
                        <button class="btn btn-warning btn-sm btn-adjust" data-id="${row.id}" data-name="${row.fullname}" title="Adjust"><i class="fas fa-coins"></i> Adjust</button>
                    `;
                }
            }
        ]
    });

    // ... (ส่วน Event Listener ปุ่มต่างๆ ใช้โค้ดเดิมได้เลยครับ) ...
    // ... Copy ส่วน Action Events จากไฟล์เดิมมาแปะต่อท้ายตรงนี้ ...
    
    // 2. Open Adjust Modal
    $(document).on("click", ".btn-adjust", function() {
        var uid = $(this).data("id");
        var name = $(this).data("name");
        $("#adjust_user_id").val(uid);
        $("#adjust_user_name").text(name);
        $("#formAdjustPoints")[0].reset();
        $("#modalAdjust").modal("show");
    });

    // 3. Submit Adjust Form
    $("#formAdjustPoints").on("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: `${domainUrl}adjust-points`,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status) {
                    iziToast.success({ title: 'Success', message: response.message, position: 'topRight' });
                    $("#modalAdjust").modal("hide");
                    $("#tablePoints").DataTable().ajax.reload(null, false);
                } else {
                    iziToast.error({ title: 'Error', message: response.message, position: 'topRight' });
                }
            },
            error: function(xhr) {
                iziToast.error({ title: 'Error', message: 'Something went wrong', position: 'topRight' });
            }
        });
    });

    // 4. View History
    $(document).on("click", ".btn-history", function() {
        var uid = $(this).data("id");
        var name = $(this).data("name");
        $("#history_user_name").text(name);
        $("#historyBody").html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $("#modalHistory").modal("show");

        $.ajax({
            url: `${domainUrl}get-point-history`,
            type: "GET",
            data: { user_id: uid },
            success: function(response) {
                var html = '';
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(i, item) {
                        var isAdd = item.type === 'earn' || item.type === 'add';
                        var color = isAdd ? 'text-success' : 'text-danger';
                        var sign = isAdd ? '+' : '';
                        
                        html += `
                            <tr>
                                <td>${new Date(item.created_at).toLocaleString()}</td>
                                <td><span class="badge badge-light">${item.type.toUpperCase()}</span></td>
                                <td class="${color} font-weight-bold">${sign}${item.amount}</td>
                                <td>${item.description || '-'}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center">No transaction history found.</td></tr>';
                }
                $("#historyBody").html(html);
            }
        });
    });
});