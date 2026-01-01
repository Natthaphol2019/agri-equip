<?php

namespace App\Services; // ✅ เพิ่ม namespace ให้เรียกใช้ใน Laravel ได้ง่าย

use CURLFile;
use DateTime;

class SlipOkSDK
{
    private $branchId;
    private $apiKey;
    private $promptPayNumber;
    private $apiUrl = 'https://api.slipok.com/api/line/apikey/';

    /**
     * Constructor
     *
     * @param string $branchId Branch ID จาก SlipOK
     * @param string $apiKey API Key จาก SlipOK
     * @param string|null $promptPayNumber เบอร์ PromptPay หรือ QR Code ของร้านค้า (ใช้สำหรับตรวจสอบกรณีสลิปไม่ตรงบัญชี)
     */
    public function __construct(string $branchId, string $apiKey, ?string $promptPayNumber = null)
    {
        $this->branchId = $branchId;
        $this->apiKey = $apiKey;
        $this->promptPayNumber = $promptPayNumber;
    }

    /**
     * ตรวจสอบสลิปจากไฟล์
     *
     * @param string $filePath Path ของไฟล์รูปภาพสลิป
     * @return array ผลลัพธ์การตรวจสอบในรูปแบบ Array มาตรฐาน
     */
    public function verify(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [
                'success' => false,
                'code' => 'FILE_ERROR',
                'message' => 'ไม่พบไฟล์หรือไฟล์ไม่สามารถอ่านได้'
            ];
        }

        $fields = [
            'files' => new CURLFile($filePath),
            'log' => 'true',
        ];

        $apiResult = $this->sendRequest($this->apiUrl . $this->branchId, $fields);

        if ($apiResult['body'] === false) {
            return [
                'success' => false,
                'code' => 'CURL_ERROR',
                'message' => "การเชื่อมต่อล้มเหลว: {$apiResult['error_msg']}"
            ];
        }

        return $this->processResponse($apiResult);
    }

    /**
     * ตรวจสอบโควต้าการใช้งาน
     */
    public function checkQuota()
    {
        $apiResult = $this->sendRequest($this->apiUrl . $this->branchId . '/quota');
        return json_decode($apiResult['body'], true);
    }

    /**
     * ประมวลผล Response จาก API
     */
    private function processResponse(array $apiResult): array
    {
        $http_code = $apiResult['info']['http_code'] ?? 0;
        $response_body = json_decode($apiResult['body']);

        // ข้อมูลดิบจาก API
        $api_code = $response_body->code ?? null;
        $data = $response_body->data ?? null;
        $data_success = $data->success ?? null;
        
        $isSuccess = false;
        $message = '';
        $transactionData = [];

        // Logic การตรวจสอบความถูกต้อง
        if (
            $http_code === 200 &&
            ($response_body->success ?? false) === true &&
            ($data_success === true) &&
            (($data->message ?? '') === '✅') &&
            empty($api_code) 
        ) {
            $isSuccess = true;
        } elseif ($api_code == 1014) {
            // กรณีบัญชีผู้รับไม่ตรง: ตรวจสอบ Proxy Value หรือ Account Value เทียบกับ PromptPay ที่ตั้งไว้
            $receiver_proxy_value = $data->receiver->proxy->value ?? null;
            $receiver_account_value = $data->receiver->account->value ?? null;

            if (!empty($this->promptPayNumber)) {
                $expected_last4 = substr(preg_replace('/\D/', '', $this->promptPayNumber), -4);
                
                $proxy_last4 = $receiver_proxy_value ? substr(preg_replace('/\D/', '', $receiver_proxy_value), -4) : '';
                $account_last4 = $receiver_account_value ? substr(preg_replace('/\D/', '', $receiver_account_value), -4) : '';

                if (($expected_last4 === $proxy_last4 || $expected_last4 === $account_last4) && $data_success === true) {
                    // ตรวจสอบสลิปซ้ำ
                    if ($this->isDuplicateRef($data->transRef ?? '')) {
                        $api_code = 1012; 
                    } else {
                        $isSuccess = true;
                    }
                } else {
                    $api_code = 1019;
                }
            }
        }

        // จัดเตรียมข้อมูล Transaction หากสำเร็จ
        if ($isSuccess) {
            $transactionData = [
                'amount' => $data->amount ?? 0,
                'bank_short' => $this->getBankName($data->sendingBank ?? '', 'short'),
                'bank_full' => $this->getBankName($data->sendingBank ?? '', 'full'),
                'ref' => $data->transRef ?? '',
                'datetime' => $this->formatThaiDateTime($data->transDate ?? '', $data->transTime ?? ''),
                'sender' => $data->sender->displayName ?? 'Unknown',
                'raw_data' => $data
            ];
            $message = "ตรวจสอบสลิปสำเร็จ";
        } else {
            $message = $this->getErrorMessage($api_code);
        }

        return [
            'success' => $isSuccess,
            'code' => $api_code,
            'message' => $message,
            'data' => $transactionData
        ];
    }

    /**
     * ตรวจสอบว่า Ref นี้เคยถูกบันทึกไปแล้วหรือยัง (ใช้ไฟล์ JSON แทน Database)
     */
    private function isDuplicateRef($ref)
    {
        // ใช้ path ของ storage ใน Laravel แทน __DIR__
        $logFile = storage_path('app/transactions.json');
        
        if (!file_exists($logFile)) {
            return false;
        }

        $logs = json_decode(file_get_contents($logFile), true);
        if (!is_array($logs)) {
            return false;
        }

        foreach ($logs as $log) {
            if (isset($log['ref']) && (string)$log['ref'] === (string)$ref) {
                return true;
            }
        }
        return false;
    }

    private function sendRequest(string $url, ?array $fields = null): array
    {
        $headers = ['x-authorization: ' . $this->apiKey];
        $curl = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false, 
        ];

        if ($fields !== null) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $fields;
        }

        curl_setopt_array($curl, $options);
        $response_body = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error_msg = curl_error($curl);
        curl_close($curl);

        return [
            'body' => $response_body,
            'info' => $info,
            'error_msg' => $error_msg,
        ];
    }

    private function getBankName($code, $type = 'full')
    {
        $banks = [
            '002' => ['short' => 'BBL',   'full' => 'กรุงเทพ'],
            '004' => ['short' => 'KBANK', 'full' => 'กสิกรไทย'],
            '006' => ['short' => 'KTB',   'full' => 'กรุงไทย'],
            '011' => ['short' => 'TTB',   'full' => 'ทหารไทยธนชาต'],
            '014' => ['short' => 'SCB',   'full' => 'ไทยพาณิชย์'],
            '025' => ['short' => 'BAY',   'full' => 'กรุงศรีอยุธยา'],
            '069' => ['short' => 'KKP',   'full' => 'เกียรตินาคินภัทร'],
            '022' => ['short' => 'CIMBT', 'full' => 'ซีไอเอ็มบีไทย'],
            '067' => ['short' => 'TISCO', 'full' => 'ทิสโก้'],
            '024' => ['short' => 'UOBT',  'full' => 'ยูโอบี'],
            '030' => ['short' => 'GSB',   'full' => 'ออมสิน'],
            '033' => ['short' => 'GHB',   'full' => 'อาคารสงเคราะห์'],
            '034' => ['short' => 'BAAC',  'full' => 'ธ.ก.ส.'],
        ];
        return $banks[$code][$type] ?? $code;
    }

    private function formatThaiDateTime($date, $time)
    {
        if (!$date || !$time) return '-';
        $dateTime = DateTime::createFromFormat('Ymd H:i:s', $date . ' ' . $time);
        if ($dateTime) {
            $buddhistYear = (int)$dateTime->format('Y') + 543;
            return $dateTime->format('d/m/') . $buddhistYear . $dateTime->format(' H:i:s');
        }
        return "$date $time";
    }

    private function getErrorMessage($code)
    {
        $errors = [
            1000 => 'ข้อมูลไม่ครบถ้วน',
            1001 => 'ไม่พบข้อมูลสาขา',
            1003 => 'API หมดอายุ',
            1004 => 'API เกินโควต้า',
            1005 => 'ไฟล์ไม่ใช่รูปภาพ',
            1006 => 'รูปภาพไม่ถูกต้อง/อ่านไม่ได้',
            1007 => 'ไม่พบ QR Code ในรูป',
            1012 => 'สลิปนี้ถูกใช้ไปแล้ว (ซ้ำ)',
            1014 => 'บัญชีผู้รับเงินไม่ตรงกับร้านค้า',
            1019 => 'บัญชีผู้รับเงินไม่ตรง (เช็ค 4 ตัวท้าย)'
        ];
        return $errors[$code] ?? 'ไม่สามารถตรวจสอบสลิปได้ หรือสลิปไม่ถูกต้อง';
    }
}