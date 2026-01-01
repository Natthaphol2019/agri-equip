<?php

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
            'log' => 'true', // ส่งเป็น string 'true' ตาม document บางเวอร์ชัน หรือ boolean ก็ได้
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
     * ตรวจสอบสลิปจาก QR Code String (Raw Data)
     *
     * @param string $qrData ข้อมูลดิบจาก QR Code
     * @return array ผลลัพธ์การตรวจสอบในรูปแบบ Array มาตรฐาน
     */
    public function verifyFromQrData(string $qrData): array
    {
        if (empty(trim($qrData))) {
            return [
                'success' => false,
                'code' => 'INPUT_ERROR',
                'message' => 'ข้อมูล QR Code ไม่สามารถว่างเปล่าได้'
            ];
        }

        $fields = [
            'qrcode' => $qrData,
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
     * ประมวลผล Response จาก API (Logic จาก controller.php เดิม)
     */
    private function processResponse(array $apiResult): array
    {
        $http_code = $apiResult['info']['http_code'] ?? 0;
        $response_body = json_decode($apiResult['body']);

        // ข้อมูลดิบจาก API
        $api_code = $response_body->code ?? null;
        $data = $response_body->data ?? null;
        $data_success = $data->success ?? null;
        $api_message = $response_body->message ?? '';

        // ตัวแปรสำหรับผลลัพธ์
        $isSuccess = false;
        $message = '';
        $transactionData = [];

        // Logic การตรวจสอบความถูกต้อง
        if (
            $http_code === 200 &&
            ($response_body->success ?? false) === true &&
            ($data_success === true) &&
            (($data->message ?? '') === '✅') &&
            empty($api_code) // เพิ่มเงื่อนไข: ต้องไม่มี Error Code (เพราะเคส 1014 ก็ส่ง success=true มา)
        ) {
            $isSuccess = true;
        } elseif ($api_code == 1014) {
            // กรณีบัญชีผู้รับไม่ตรง (เช่น ใช้เลขบัญชีสมัคร แต่ลูกค้าโอนเข้า PromptPay หรือเลขบัญชีธนาคาร)
            // ตรวจสอบ Proxy Value (เบอร์/เลขบัตร) หรือ Account Value (เลขบัญชี) ว่าตรงกับที่ตั้งค่าไว้หรือไม่
            $receiver_proxy_value = $data->receiver->proxy->value ?? null;
            $receiver_account_value = $data->receiver->account->value ?? null;

            if (!empty($this->promptPayNumber)) {
                // ตัดเอาเฉพาะตัวเลขและเทียบ 4 ตัวท้าย
                $expected_last4 = substr(preg_replace('/\D/', '', $this->promptPayNumber), -4);
                
                $proxy_last4 = $receiver_proxy_value ? substr(preg_replace('/\D/', '', $receiver_proxy_value), -4) : '';
                $account_last4 = $receiver_account_value ? substr(preg_replace('/\D/', '', $receiver_account_value), -4) : '';

                // ตรวจสอบว่าตรงกับ PromptPay หรือ เลขบัญชีธนาคาร
                if (($expected_last4 === $proxy_last4 || $expected_last4 === $account_last4) && $data_success === true && ($data->message ?? '') === '✅') {
                    // ตรวจสอบว่า Ref นี้เคยถูกบันทึกไปแล้วหรือยัง (ป้องกันสลิปซ้ำ)
                    if ($this->isDuplicateRef($data->transRef ?? '')) {
                        $api_code = 1012; // กำหนดรหัส error เป็น 1012 (สลิปซ้ำ)
                    } else {
                        $isSuccess = true;
                    }
                } else {
                    $api_code = 1019; // กำหนดรหัส error ใหม่สำหรับกรณีนี้
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
            $message = "เติมเงินสำเร็จ (จำนวน {$transactionData['amount']} บาท)";
        } else {
            $message = $this->getErrorMessage($api_code);
        }

        return [
            'success' => $isSuccess,
            'code' => $api_code,
            'message' => $message,
            'data' => $transactionData,
            'raw_response' => $response_body // ส่งคืนเผื่อ Debug
        ];
    }

    /**
     * ตรวจสอบว่า Ref นี้เคยถูกบันทึกไปแล้วหรือยัง (จากไฟล์ transactions.json)
     * 
     * @param string $ref รหัสอ้างอิงธุรกรรม
     * @return bool true หากพบว่าซ้ำ, false หากไม่พบ
     */
    private function isDuplicateRef($ref)
    {
        // หมายเหตุ: สามารถปรับเปลี่ยนไปใช้ Database ได้ที่นี่ (เช่น SELECT count(*) FROM transactions WHERE ref = ?)
        $logFile = __DIR__ . '/transactions.json';
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
            CURLOPT_SSL_VERIFYPEER => false, // แนะนำให้เปิดเป็น true ใน Production
        ];

        if ($fields !== null) {
            $isMultipart = false;
            foreach ($fields as $value) {
                if ($value instanceof CURLFile) {
                    $isMultipart = true;
                    break;
                }
            }

            $options[CURLOPT_POST] = 1;
            if ($isMultipart) {
                $options[CURLOPT_POSTFIELDS] = $fields;
            } else {
                $options[CURLOPT_POSTFIELDS] = http_build_query($fields);
            }
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
            1019 => 'บัญชีผู้รับเงินไม่ตรงกับร้านค้า'
        ];
        return $errors[$code] ?? 'ไม่สามารถตรวจสอบสลิปได้ หรือสลิปไม่ถูกต้อง';
    }
}
