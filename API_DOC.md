# FriendlyGroup Loyalty API Documentation

**Base URL:** `http://your-domain.com/api`
**Authentication:** ระบบนี้ใช้การส่ง parameter `user_id` ผ่าน Body Request เพื่อระบุตัวตน (ตาม Requirement ล่าสุด)

---

## 🔹 1. User Loyalty (คะแนนลูกค้า)

### 1.1 ดูแต้มสะสมและประวัติ (Get Points & History)
ดึงยอดคะแนนคงเหลือและประวัติการได้รับ/ใช้คะแนนล่าสุด 20 รายการ

* **URL:** `/user/points`
* **Method:** `POST`
* **Body Parameters:**
    ```json
    {
        "user_id": 1
    }
    ```

* **Success Response (200 OK):**
    ```json
    {
        "status": true,
        "data": {
            "total_points": 1550,
            "transactions": [
                {
                    "id": 10,
                    "amount": 50,
                    "type": "earn",
                    "description": "Store Visit (Staff: Admin)",
                    "created_at": "2025-12-28T10:30:00.000000Z"
                },
                {
                    "id": 9,
                    "amount": -100,
                    "type": "use",
                    "description": "Redeemed: คูปองส่วนลด 50 บาท",
                    "created_at": "2025-12-27T14:20:00.000000Z"
                }
            ]
        }
    }
    ```

### 1.2 สร้าง QR Code สำหรับรับแต้ม (Generate Earn Points QR)
สร้างรูปภาพ QR Code (Base64) เพื่อยื่นให้พนักงานสแกนเพิ่มแต้ม

* **URL:** `/user/generate-qr`
* **Method:** `POST`
* **Body Parameters:**
    ```json
    {
        "user_id": 1
    }
    ```

* **Success Response (200 OK):**
    ```json
    {
        "status": true,
        "data": {
            "qr_code_payload": "fg:points:1",
            "qr_image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...", 
            "type": "earn_points",
            "description": "Show this QR to staff to earn points"
        }
    }
    ```
    > **Note:** นำค่า `qr_image` ไปใส่ใน `<img src="...">` เพื่อแสดงผลได้ทันที

---

## 🔹 2. Rewards (ของรางวัล)

### 2.1 ดูรายการของรางวัลทั้งหมด (Reward Catalog)
แสดงรายการของรางวัลที่เปิดใช้งานอยู่ (Active)

* **URL:** `/rewards`
* **Method:** `GET`
* **Success Response (200 OK):**
    ```json
    {
        "status": true,
        "data": [
            {
                "id": 1,
                "name": "ส่วนลด 100 บาท",
                "description": "ใช้ลดค่าอาหาร",
                "required_points": 500,
                "type": "discount",
                "image_url": "[http://domain.com/uploads/rewards/coupon.jpg](http://domain.com/uploads/rewards/coupon.jpg)"
            }
        ]
    }
    ```

### 2.2 ดูรายละเอียดของรางวัล (Reward Detail)
ดูข้อมูลรางวัลรายตัว (ใช้สำหรับหน้า Catalog ก่อนตัดสินใจกดแลก)

* **URL:** `/rewards/{id}`
* **Method:** `GET`
* **Success Response (200 OK):**
    ```json
    {
        "status": true,
        "data": {
            "id": 1,
            "name": "ส่วนลด 100 บาท",
            "required_points": 500,
            "image_url": "..."
            // ... fields อื่นๆ
        }
    }
    ```

### 2.3 แลกของรางวัล (Redeem Reward)
ทำการตัดแต้มและสร้างบันทึกการแลกรางวัล

* **URL:** `/rewards/redeem`
* **Method:** `POST`
* **Body Parameters:**
    ```json
    {
        "user_id": 1,
        "reward_id": 5
    }
    ```

* **Success Response (200 OK):**
    ```json
    {
        "status": true,
        "message": "Redemption successful!",
        "data": {
            "redemption_id": 15,
            "qr_code": "fg:redeem:15",
            "reward_name": "ส่วนลด 100 บาท"
        }
    }
    ```

---

## 🔹 3. My Coupons (กระเป๋าคูปองของฉัน)

### 3.1 ดูรายการคูปองที่แลกแล้ว (My Rewards History)
ดูประวัติการแลก สามารถกรองสถานะได้ (Active/Used)

* **URL:** `/user/rewards`
* **Method:** `POST`
* **Body Parameters:**
    ```json
    {
        "user_id": 1,
        "status": "active"  // active = ยังไม่ใช้, used = ใช้แล้ว (ถ้าไม่ส่งมาจะแสดงทั้งหมด)
    }
    ```

* **Success Response (200 OK):**
    ```json
    {
        "status": true,
        "data": [
            {
                "redemption_id": 15,
                "reward_name": "ส่วนลด 100 บาท",
                "image_url": "...",
                "is_used": false,
                "redeemed_at": "2025-12-28 10:00"
            }
        ]
    }
    ```

### 3.2 ดูรายละเอียดคูปองเพื่อใช้งาน (Use Coupon / QR)
แสดงรายละเอียดพร้อม **QR Code Image (Base64)** สำหรับให้พนักงานสแกน

* **URL:** `/user/rewards-detail/{id}`  *(id คือ redemption_id)*
* **Method:** `POST`
* **Body Parameters:**
    ```json
    {
        "user_id": 1
    }
    ```

* **Success Response (200 OK):**
    ```json
    {
        "status": true,
        "data": {
            "redemption_id": 15,
            "qr_code_payload": "fg:redeem:15",
            "qr_image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...", 
            "status": "Active",
            "reward_details": {
                "name": "ส่วนลด 100 บาท",
                "value_display": "100 THB",
                "type": "discount",
                "image_url": "..."
            },
            "expiry_info": "Valid at all branches"
        }
    }
    ```
    > **Note:** นำค่า `qr_image` ไปใส่ใน `<img src="...">` เพื่อแสดงผล

---

## 🔹 4. Staff Operations (สำหรับพนักงาน)

### 4.1 สแกน QR Code (Scan)
ใช้สำหรับพนักงานสแกน QR ของลูกค้า (ทั้งรับแต้ม และใช้คูปอง)

* **URL:** `/staff/scan`
* **Method:** `POST`
* **Body Parameters:**
    ```json
    {
        "user_id": 99,         // ID ของพนักงานที่ทำการสแกน (Role ต้องเป็น staff)
        "qr_code": "fg:points:1", // หรือ "fg:redeem:15" (ได้จากการอ่าน QR)
        "total_price": 500     // (Optional) ยอดเงินรวม ใส่กรณีใช้คูปองส่วนลดแบบ %
    }
    ```

* **Success Response (กรณีให้แต้ม - Earn Points):**
    ```json
    {
        "status": true,
        "message": "Added 10 points to Customer Name",
        "data": {
            "current_points": 1560
        }
    }
    ```

* **Success Response (กรณีใช้คูปอง - Redeem):**
    ```json
    {
        "status": true,
        "message": "Reward redeemed successfully!",
        "data": {
            "reward_type": "discount",
            "reward_name": "ส่วนลด 10%",
            "customer_name": "Somchai",
            "original_price": 500,
            "discount_amount": 50,
            "final_price": 450
        }
    }
    ```