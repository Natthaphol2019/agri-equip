# SlipOK API PHP SDK (Unofficial Example)

ตัวอย่างโค้ด PHP สำหรับเชื่อมต่อ API ของ [SlipOK](https://www.slipok.com/) เพื่อตรวจสอบสลิปโอนเงินธนาคาร (Thai QR Payment) พร้อมระบบจัดการเบื้องต้น

## คุณสมบัติ (Features)

*   **ตรวจสอบสลิป (Verify Slip):** รองรับการอัปโหลดไฟล์รูปภาพสลิปเพื่อตรวจสอบความถูกต้องผ่าน API
*   **ตรวจสอบโควต้า (Check Quota):** ดูจำนวนโควต้าคงเหลือ, โควต้าพิเศษ และยอดที่ใช้เกิน
*   **ตรวจสอบบัญชีผู้รับ (Account Validation):** รองรับกรณีที่ API แจ้งเตือนบัญชีไม่ตรง (Code 1014) โดยระบบจะตรวจสอบเลขบัญชีหรือเบอร์ PromptPay 4 ตัวท้ายให้อัตโนมัติ
*   **ป้องกันสลิปซ้ำ (Duplicate Check):** มีระบบบันทึกประวัติลงไฟล์ JSON และแจ้งเตือนหากมีการนำสลิปเดิมมาใช้ซ้ำ (Code 1012)
*   **AJAX Interface:** หน้าทดสอบ (`test_slip.php`) ทำงานแบบ AJAX ไม่ต้องรีโหลดหน้า แสดงผลลัพธ์ทันที

## โครงสร้างไฟล์ (File Structure)

*   `SlipOkSDK.php`: คลาสหลัก (Class) สำหรับจัดการการเชื่อมต่อ API, Logic การตรวจสอบ, และการจัดการ Error Code ต่างๆ
*   `test_slip.php`: ไฟล์ตัวอย่างการใช้งาน (Frontend + Controller Logic) สำหรับทดสอบระบบ มี UI ที่สร้างด้วย TailwindCSS
*   `transactions.json`: ไฟล์เก็บประวัติการตรวจสอบสลิป (ใช้แทน Database อย่างง่าย สำหรับตรวจสอบสลิปซ้ำ)

## การติดตั้งและการใช้งาน (Installation & Usage)

1.  **เตรียมไฟล์:** นำไฟล์ทั้งหมดไปวางใน Web Server ที่รองรับ PHP (เช่น XAMPP, Laragon, หรือ Hosting จริง)
2.  **ตั้งค่า API Key:**
    เปิดไฟล์ `test_slip.php` และแก้ไขส่วน Configuration ด้านบน:
    ```php
    $branchId = 'YOUR_BRANCH_ID'; 
    $apiKey = 'YOUR_API_KEY';
    $promptPayNumber = 'YOUR_PROMPTPAY_NUMBER'; // เบอร์ PromptPay หรือเลขบัญชีร้านค้า (สำหรับเช็คกรณีบัญชีไม่ตรง)
    ```
3.  **รันโปรแกรม:** เปิด Browser และเรียกไฟล์ `test_slip.php`

## การทำงานของระบบ (System Logic)

### 1. การตรวจสอบสลิป
*   ระบบจะส่งรูปภาพไปยัง SlipOK API
*   หาก API ตอบกลับว่าสำเร็จ (`success: true`) ระบบจะตรวจสอบเงื่อนไขเพิ่มเติมใน `SlipOkSDK.php`:
    *   **กรณีปกติ:** ข้อมูลถูกต้องและบัญชีตรง -> **ผ่าน**
    *   **กรณีบัญชีไม่ตรง (Code 1014):** ระบบจะเช็คเลข 4 ตัวท้ายของ `receiver.proxy.value` (เบอร์/บัตรปชช) หรือ `receiver.account.value` (เลขบัญชี) เทียบกับ `$promptPayNumber` ที่ตั้งค่าไว้
*   **เช็คสลิปซ้ำ:** หากข้อมูลถูกต้อง ระบบจะเช็ค `Ref` ในไฟล์ `transactions.json` หากมีอยู่แล้วจะแจ้งเตือนว่าสลิปซ้ำ (Code 1012)

### 2. การบันทึกข้อมูล
*   เมื่อตรวจสอบผ่าน ระบบจะบันทึก `Ref`, `Amount`, และ `Timestamp` ลงในไฟล์ `transactions.json` ทันที

## ความต้องการระบบ (Requirements)

*   PHP 7.4 หรือสูงกว่า
*   cURL Extension เปิดใช้งาน
*   Permission ในการเขียนไฟล์ (สำหรับโฟลเดอร์ `uploads/` และไฟล์ `transactions.json`)

## หมายเหตุ (Notes)

*   **Security:** โค้ดชุดนี้เป็นตัวอย่างการทำงาน (Proof of Concept) ในการใช้งานจริงควรเพิ่มระบบ CSRF Protection และตรวจสอบไฟล์อัปโหลด (MIME Type/Magic Bytes) ให้รัดกุมยิ่งขึ้น
*   **Database:** สามารถเปลี่ยนจากไฟล์ JSON เป็น MySQL/MariaDB ได้โดย:
    1. แก้ไขฟังก์ชัน `isDuplicateRef` ใน `SlipOkSDK.php`
    2. แก้ไขส่วนบันทึกข้อมูลใน `test_slip.php`

---
*Developed for educational purposes based on SlipOK API documentation.*
