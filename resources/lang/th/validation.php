<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | บรรทัดภาษาเหล่านี้ประกอบไปด้วยข้อความแสดงข้อผิดพลาดเริ่มต้น
    | ที่ใช้โดยคลาสการตรวจสอบความถูกต้อง (Validator)
    | สามารถปรับแต่งข้อความเหล่านี้ให้เหมาะสมกับแอปพลิเคชันได้
    |
    */

    'accepted' => ':attribute ต้องได้รับการยอมรับ',
    'accepted_if' => ':attribute ต้องได้รับการยอมรับเมื่อ :other เป็น :value',
    'active_url' => ':attribute ไม่ใช่ URL ที่ถูกต้อง',
    'after' => ':attribute ต้องเป็นวันที่หลังจาก :date',
    'after_or_equal' => ':attribute ต้องเป็นวันที่หลังหรือเท่ากับ :date',
    'alpha' => ':attribute ต้องประกอบด้วยตัวอักษรเท่านั้น',
    'alpha_dash' => ':attribute ต้องประกอบด้วยตัวอักษร ตัวเลข ขีดกลาง และขีดล่างเท่านั้น',
    'alpha_num' => ':attribute ต้องประกอบด้วยตัวอักษรและตัวเลขเท่านั้น',
    'array' => ':attribute ต้องเป็นอาร์เรย์',
    'before' => ':attribute ต้องเป็นวันที่ก่อน :date',
    'before_or_equal' => ':attribute ต้องเป็นวันที่ก่อนหรือเท่ากับ :date',
    'between' => [
        'numeric' => ':attribute ต้องอยู่ระหว่าง :min ถึง :max',
        'file' => ':attribute ต้องมีขนาดระหว่าง :min ถึง :max กิโลไบต์',
        'string' => ':attribute ต้องมีความยาวระหว่าง :min ถึง :max ตัวอักษร',
        'array' => ':attribute ต้องมีระหว่าง :min ถึง :max รายการ',
    ],
    'boolean' => 'ฟิลด์ :attribute ต้องเป็น true หรือ false',
    'confirmed' => ':attribute การยืนยันไม่ตรงกัน',
    'current_password' => 'รหัสผ่านไม่ถูกต้อง',
    'date' => ':attribute ไม่ใช่วันที่ที่ถูกต้อง',
    'date_equals' => ':attribute ต้องเป็นวันที่เท่ากับ :date',
    'date_format' => ':attribute ไม่ตรงกับรูปแบบ :format',
    'different' => ':attribute และ :other ต้องแตกต่างกัน',
    'digits' => ':attribute ต้องเป็นตัวเลข :digits หลัก',
    'digits_between' => ':attribute ต้องอยู่ระหว่าง :min ถึง :max หลัก',
    'dimensions' => ':attribute มีขนาดรูปภาพที่ไม่ถูกต้อง',
    'distinct' => 'ฟิลด์ :attribute มีค่าที่ซ้ำกัน',
    'email' => ':attribute ต้องเป็นที่อยู่อีเมลที่ถูกต้อง',
    'ends_with' => ':attribute ต้องลงท้ายด้วยหนึ่งในสิ่งต่อไปนี้: :values',
    'exists' => ':attribute ที่เลือกไม่ถูกต้อง',
    'file' => ':attribute ต้องเป็นไฟล์',
    'filled' => 'ฟิลด์ :attribute ต้องมีค่า',
    'gt' => [
        'numeric' => ':attribute ต้องมากกว่า :value',
        'file' => ':attribute ต้องมีขนาดมากกว่า :value กิโลไบต์',
        'string' => ':attribute ต้องมีความยาวมากกว่า :value ตัวอักษร',
        'array' => ':attribute ต้องมีมากกว่า :value รายการ',
    ],
    'gte' => [
        'numeric' => ':attribute ต้องมากกว่าหรือเท่ากับ :value',
        'file' => ':attribute ต้องมีขนาดมากกว่าหรือเท่ากับ :value กิโลไบต์',
        'string' => ':attribute ต้องมีความยาวมากกว่าหรือเท่ากับ :value ตัวอักษร',
        'array' => ':attribute ต้องมี :value รายการขึ้นไป',
    ],
    'image' => ':attribute ต้องเป็นรูปภาพ',
    'in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'in_array' => 'ฟิลด์ :attribute ไม่มีอยู่ใน :other',
    'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
    'ip' => ':attribute ต้องเป็นที่อยู่ IP ที่ถูกต้อง',
    'ipv4' => ':attribute ต้องเป็นที่อยู่ IPv4 ที่ถูกต้อง',
    'ipv6' => ':attribute ต้องเป็นที่อยู่ IPv6 ที่ถูกต้อง',
    'json' => ':attribute ต้องเป็นสตริง JSON ที่ถูกต้อง',
    'lt' => [
        'numeric' => ':attribute ต้องน้อยกว่า :value',
        'file' => ':attribute ต้องมีขนาดน้อยกว่า :value กิโลไบต์',
        'string' => ':attribute ต้องมีความยาวน้อยกว่า :value ตัวอักษร',
        'array' => ':attribute ต้องมีน้อยกว่า :value รายการ',
    ],
    'lte' => [
        'numeric' => ':attribute ต้องน้อยกว่าหรือเท่ากับ :value',
        'file' => ':attribute ต้องมีขนาดน้อยกว่าหรือเท่ากับ :value กิโลไบต์',
        'string' => ':attribute ต้องมีความยาวน้อยกว่าหรือเท่ากับ :value ตัวอักษร',
        'array' => ':attribute ต้องมีไม่เกิน :value รายการ',
    ],
    'max' => [
        'numeric' => ':attribute ต้องไม่มากกว่า :max',
        'file' => ':attribute ต้องไม่ใหญ่กว่า :max กิโลไบต์',
        'string' => ':attribute ต้องไม่ยาวกว่า :max ตัวอักษร',
        'array' => ':attribute ต้องไม่มีมากกว่า :max รายการ',
    ],
    'mimes' => ':attribute ต้องเป็นไฟล์ประเภท: :values',
    'mimetypes' => ':attribute ต้องเป็นไฟล์ประเภท: :values',
    'min' => [
        'numeric' => ':attribute ต้องไม่น้อยกว่า :min',
        'file' => ':attribute ต้องมีขนาดไม่น้อยกว่า :min กิโลไบต์',
        'string' => ':attribute ต้องมีความยาวไม่น้อยกว่า :min ตัวอักษร',
        'array' => ':attribute ต้องมีไม่น้อยกว่า :min รายการ',
    ],
    'multiple_of' => ':attribute ต้องเป็นจำนวนที่หาร :value ลงตัว',
    'not_in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'not_regex' => 'รูปแบบของ :attribute ไม่ถูกต้อง',
    'numeric' => ':attribute ต้องเป็นตัวเลข',
    'password' => 'รหัสผ่านไม่ถูกต้อง',
    'present' => 'ฟิลด์ :attribute ต้องมีอยู่',
    'regex' => 'รูปแบบของ :attribute ไม่ถูกต้อง',
    'required' => 'ฟิลด์ :attribute จำเป็นต้องกรอก',
    'required_if' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อ :other เป็น :value',
    'required_unless' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเว้นแต่ :other จะอยู่ใน :values',
    'required_with' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อ :values มีค่า',
    'required_with_all' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อ :values มีค่าครบ',
    'required_without' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อ :values ไม่มีค่า',
    'required_without_all' => 'ฟิลด์ :attribute จำเป็นต้องกรอกเมื่อไม่มีค่าใดใน :values',
    'prohibited' => 'ฟิลด์ :attribute ถูกห้ามใช้งาน',
    'prohibited_if' => 'ฟิลด์ :attribute ถูกห้ามเมื่อ :other เป็น :value',
    'prohibited_unless' => 'ฟิลด์ :attribute ถูกห้ามเว้นแต่ :other จะอยู่ใน :values',
    'prohibits' => 'ฟิลด์ :attribute ห้ามไม่ให้ :other มีค่า',
    'same' => ':attribute และ :other ต้องตรงกัน',
    'size' => [
        'numeric' => ':attribute ต้องมีขนาด :size',
        'file' => ':attribute ต้องมีขนาด :size กิโลไบต์',
        'string' => ':attribute ต้องมีความยาว :size ตัวอักษร',
        'array' => ':attribute ต้องมี :size รายการ',
    ],
    'starts_with' => ':attribute ต้องเริ่มต้นด้วยหนึ่งในสิ่งต่อไปนี้: :values',
    'string' => ':attribute ต้องเป็นสตริง',
    'timezone' => ':attribute ต้องเป็นเขตเวลาที่ถูกต้อง',
    'unique' => ':attribute ถูกใช้งานแล้ว',
    'uploaded' => ':attribute อัปโหลดไม่สำเร็จ',
    'url' => ':attribute ต้องเป็น URL ที่ถูกต้อง',
    'uuid' => ':attribute ต้องเป็น UUID ที่ถูกต้อง',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | สามารถระบุข้อความการตรวจสอบความถูกต้องที่กำหนดเองสำหรับ
    | กฎแต่ละรายการได้ที่นี่
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'ข้อความกำหนดเอง',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | บรรทัดเหล่านี้จะใช้แทนชื่อฟิลด์ที่เป็นตัวแปรให้เป็นชื่อที่อ่านง่าย
    | และแสดงในข้อความแสดงข้อผิดพลาด
    |
    */

    'attributes' => [],

];
