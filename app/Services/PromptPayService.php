<?php

namespace App\Services;

class PromptPayService
{
    /**
     * สร้าง Payload สำหรับ PromptPay QR Code (Thai QR Payment Standard)
     * รองรับทั้งเบอร์มือถือ (08x...), เลขบัตรประชาชน (13 หลัก), และ E-Wallet
     */
    public static function generatePayload($target, $amount = null)
    {
        $target = self::formatTarget($target);
        $targetLen = strlen($target);

        // 1. กำหนด Tag ของประเภทบัญชี (01=มือถือ, 02=บัตรปชช/Tax ID, 03=E-Wallet)
        // - ถ้าเป็นเบอร์มือถือ (แปลงเป็น 66xxx แล้ว) ความยาวจะเป็น 11 หรือ 12 หลัก -> ใช้ Tag 01
        // - ถ้าเป็นบัตรประชาชน ความยาวจะเป็น 13 หลัก -> ใช้ Tag 02
        $subTag = '01'; // ค่าเริ่มต้น (มือถือ)
        if ($targetLen === 13) $subTag = '02'; 
        if ($targetLen === 15) $subTag = '03';

        // 2. สร้างข้อมูล Merchant Account Information (Tag 29)
        $merchantInfoValue = 
            '0016A000000677010111' .             // AID Application ID
            $subTag . sprintf('%02d', $targetLen) . $target; // Account ID
        
        $merchantInfoLen = strlen($merchantInfoValue);

        // 3. ประกอบร่าง QR Code Payload
        $data = [
            '000201',       // Format Indicator
            // ถ้ามีจำนวนเงิน ใช้ '12' (Dynamic), ไม่มีใช้ '11' (Static)
            '0102' . ($amount !== null ? '12' : '11'), 
            
            // Merchant Account Info (Tag 29) - คำนวณความยาวอัตโนมัติ ✅
            '29' . sprintf('%02d', $merchantInfoLen) . $merchantInfoValue,
            
            '5802TH',       // Country Code
            '5303764',      // Transaction Currency (THB)
        ];

        // 4. ใส่จำนวนเงิน (Tag 54)
        if ($amount !== null) {
            $formattedAmount = number_format($amount, 2, '.', '');
            $data[] = '54' . sprintf('%02d', strlen($formattedAmount)) . $formattedAmount;
        }

        // 5. คำนวณ CRC16 Checksum (Tag 63)
        $dataString = implode('', $data) . '6304'; 
        $crc = self::crc16($dataString);

        return $dataString . $crc;
    }

    /**
     * จัดรูปแบบเบอร์โทรศัพท์หรือเลขบัตร
     */
    private static function formatTarget($target)
    {
        $target = preg_replace('/[^0-9]/', '', $target); // เอาเฉพาะตัวเลข

        // ถ้าเป็นเบอร์มือถือ (ขึ้นต้นด้วย 0 และยาว 10 หลัก) เปลี่ยน 0 เป็น 66
        // ตัวอย่าง: 0643086816 -> 66643086816 (ความยาว 11 หลัก)
        if (strlen($target) === 10 && substr($target, 0, 1) === '0') {
            $target = '66' . substr($target, 1);
        }
        
        return $target;
    }

    /**
     * คำนวณ Checksum (CRC16-CCITT)
     */
    private static function crc16($data)
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return strtoupper(sprintf('%04x', $crc));
    }
}