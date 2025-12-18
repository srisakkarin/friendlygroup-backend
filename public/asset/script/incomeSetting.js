$(document).ready(function () {
    // Sidebar Active Handling
    $(".sideBarli").removeClass("activeLi");
    $(".paymentSettingSideA, .otherSideA, .systemSettingSideA").removeClass("activeLi");
    $(".incomeSettingSideA").addClass("activeLi");
    $('#setting-dropdown').css('display', 'block');

    // AJAX Save + Validation
    const form = $("#income-settings-form");

    form.on("submit", function (e) {
        e.preventDefault();

        let valid = true;
        $(".percent-input").each(function () {
            const val = parseFloat($(this).val());
            if (isNaN(val) || val < 0 || val > 100) {
                $(this).addClass("is-invalid");
                valid = false;
            } else {
                $(this).removeClass("is-invalid");
            }
        });

        if (!valid) {
            iziToast.error({
                title: "Invalid Input!",
                message: "กรุณากรอกตัวเลขระหว่าง 0 ถึง 100 ให้ถูกต้อง",
                position: "topRight",
            });
            return;
        }

        const formData = new FormData(this);

        fetch(`${domainUrl}income-settings`, {
            method: "POST", 
            body: formData,
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    iziToast.success({
                        title: "Update Successful",
                        message: "ตั้งค่าถูกบันทึกเรียบร้อย!",
                        position: "topRight",
                    });
                } else {
                    iziToast.error({
                        title: "Failed!",
                        message: data.message || "เกิดข้อผิดพลาด!",
                        position: "topRight",
                    });
                }
            })
            .catch(err => {
                iziToast.error({
                    title: "Failed!",
                    message: "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้",
                    position: "topRight",
                });
            });
    });
});
