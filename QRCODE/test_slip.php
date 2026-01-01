<?php

require_once 'SlipOkSDK.php';

// --- การตั้งค่า (Configuration) ---
// กรุณาใส่ Branch ID และ API Key ของคุณที่นี่
$branchId = '58085'; 
$apiKey = 'SLIPOKZZVVLFZ';
$promptPayNumber = '0809600789'; // ใส่เบอร์ PromptPay ของร้านค้าเพื่อตรวจสอบความถูกต้อง (Optional)

$result = null;
$quotaResult = null;

// ==========================================
// ส่วนจำลอง Controller (Controller Logic)
// ==========================================

// Init SDK (ใน Framework จริงมักจะทำใน Constructor หรือเรียกผ่าน Service/Dependency Injection)
$sdk = new SlipOkSDK($branchId, $apiKey, $promptPayNumber);

// [Controller Action 1]: API สำหรับตรวจสอบโควต้า (คืนค่า JSON)
if (isset($_GET['ajax_action']) && $_GET['ajax_action'] === 'check_quota') {
    header('Content-Type: application/json');
    echo json_encode($sdk->checkQuota());
    exit;
}

// [Controller Action 2]: API สำหรับตรวจสอบสลิป (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['ajax_action']) && $_GET['ajax_action'] === 'verify_slip') {
    header('Content-Type: application/json');
    $result = ['success' => false, 'message' => 'ไม่พบไฟล์ที่อัปโหลด'];

    // หมายเหตุ: ในระบบจริงควรเพิ่มมาตรการความปลอดภัยเพิ่มเติม เช่น:
    // 1. ระบบป้องกัน CSRF (Cross-Site Request Forgery) เพื่อป้องกันการโจมตีจากไซต์อื่น
    // 2. การตรวจสอบไฟล์ขั้นสูง (Advanced File Validation) เช่น ตรวจสอบ Magic Bytes, MIME Type ที่แท้จริง, และจำกัดขนาดไฟล์ให้เหมาะสม

    if (isset($_FILES['slip_image'])) {
    $file = $_FILES['slip_image'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // สร้างโฟลเดอร์ชั่วคราวสำหรับเก็บไฟล์อัปโหลด (ถ้ายังไม่มี)
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // ตั้งชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ
        $filePath = $uploadDir . uniqid() . '-' . basename($file['name']);
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // --- เรียกใช้งาน SDK เพื่อตรวจสอบสลิป ---
            $result = $sdk->verify($filePath);

            // [Business Logic]: ส่วนจัดการข้อมูลหลังตรวจสอบผ่าน (เช่น บันทึกลง Database, อัปเดตยอดเงิน user)
            // บันทึก Ref ลงไฟล์ JSON หากตรวจสอบสำเร็จ
            if ($result['success'] && !empty($result['data'])) {
                $logFile = __DIR__ . '/transactions.json';
                $logs = [];

                if (file_exists($logFile)) {
                    $logs = json_decode(file_get_contents($logFile), true) ?? [];
                }

                $logs[] = [
                    'ref' => $result['data']['ref'],
                    'amount' => $result['data']['amount'],
                    'timestamp' => date('Y-m-d H:i:s')
                ];

                file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            
            // ลบไฟล์หลังจากตรวจสอบเสร็จสิ้น (เพื่อไม่ให้เปลืองพื้นที่)
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        } else {
            $result = ['success' => false, 'message' => 'ไม่สามารถบันทึกไฟล์อัปโหลดได้'];
        }
    } else {
        $result = ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลด: ' . $file['error']];
    }
    }
    
    echo json_encode($result);
    exit;
}

// ==========================================
// ส่วนจำลอง View (HTML Display)
// ==========================================
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SlipOk SDK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-900 text-white p-6 font-sans">
    <div class="max-w-2xl mx-auto bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h1 class="text-2xl font-bold mb-6 text-blue-500 text-center">ทดสอบ SlipOk SDK</h1>
        
        <!-- ส่วนตรวจสอบโควต้า -->
        <div class="mb-8 p-4 bg-gray-700 rounded-lg">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">ตรวจสอบโควต้า (Check Quota)</h2>
                <button type="button" id="btn-check-quota" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition flex items-center gap-2">
                    <span>ตรวจสอบ</span>
                </button>
            </div>
            <div id="quota-result-container"></div>
        </div>

        <!-- ส่วนอัปโหลดสลิป -->
        <form id="form-verify-slip" class="space-y-4">
            <div class="border-2 border-dashed border-gray-600 rounded-lg p-6 text-center hover:border-blue-500 transition-colors">
                <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
                <label class="block text-sm font-medium mb-2 text-gray-300">อัปโหลดรูปสลิป (JPG, PNG)</label>
                <input type="file" name="slip_image" accept="image/*" class="block w-full text-sm text-gray-400
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-600 file:text-white
                    hover:file:bg-blue-700
                " required>
            </div>
            
            <button type="submit" id="btn-verify-slip" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded transition duration-200 flex justify-center items-center gap-2">
                <span>ตรวจสอบสลิป (Verify Slip)</span>
            </button>
        </form>

        <div id="verify-result-container" class="mt-8"></div>
    </div>

    <script>
        document.getElementById('btn-check-quota').addEventListener('click', function() {
            const btn = this;
            const container = document.getElementById('quota-result-container');
            
            // Loading State
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> กำลังตรวจสอบ...';
            container.innerHTML = '';

            fetch('?ajax_action=check_quota')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    
                    if (!data.success) {
                        html = `
                            <div class="mt-4 p-3 bg-red-900/50 border border-red-700 rounded text-red-200 text-sm">
                                ขออภัย ไม่สามารถตรวจสอบโควต้าได้ (${data.message || 'Unknown Error'})
                            </div>`;
                    } else if (data.data) {
                        // Helper function for number format
                        const numFormat = (n) => new Intl.NumberFormat().format(n || 0);
                        
                        html = `
                            <div class="mt-4 grid grid-cols-3 gap-4">
                                <div class="bg-gray-800 p-3 rounded border border-gray-600 text-center">
                                    <div class="text-gray-400 text-xs mb-1">โควต้าคงเหลือ (Quota)</div>
                                    <div class="text-xl font-bold text-green-400">${numFormat(data.data.quota)}</div>
                                </div>
                                <div class="bg-gray-800 p-3 rounded border border-gray-600 text-center">
                                    <div class="text-gray-400 text-xs mb-1">โควต้าพิเศษ (Special)</div>
                                    <div class="text-xl font-bold text-yellow-400">${numFormat(data.data.specialQuota)}</div>
                                </div>
                                <div class="bg-gray-800 p-3 rounded border border-gray-600 text-center">
                                    <div class="text-gray-400 text-xs mb-1">ใช้เกินโควต้า (Over Quota)</div>
                                    <div class="text-xl font-bold text-red-400">${numFormat(data.data.overQuota)}</div>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-gray-900 rounded border border-gray-600 overflow-x-auto">
                                <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap">${JSON.stringify(data, null, 4)}</pre>
                            </div>`;
                    }
                    
                    container.innerHTML = html;
                })
                .catch(error => {
                    container.innerHTML = `<div class="mt-4 text-red-400 text-sm">เกิดข้อผิดพลาด: ${error.message}</div>`;
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<span>ตรวจสอบ</span>';
                });
        });

        // AJAX Verify Slip
        document.getElementById('form-verify-slip').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const btn = document.getElementById('btn-verify-slip');
            const container = document.getElementById('verify-result-container');
            const formData = new FormData(form);

            // Loading State
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> กำลังตรวจสอบ...';
            container.innerHTML = '';

            fetch('?ajax_action=verify_slip', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const numFormat = (n) => new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n || 0);
                const escapeHtml = (unsafe) => {
                    return (unsafe || '-').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
                };

                let detailsHtml = '';
                if (data.success && data.data) {
                    detailsHtml = `
                        <div class="mb-6">
                            <h3 class="text-gray-400 text-sm font-semibold mb-3 border-l-4 border-blue-500 pl-2">รายละเอียดธุรกรรม</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-800 p-4 rounded border border-gray-600">
                                    <div class="text-gray-400 text-xs mb-1">จำนวนเงิน</div>
                                    <div class="text-2xl font-bold text-green-400">${numFormat(data.data.amount)} ฿</div>
                                </div>
                                <div class="bg-gray-800 p-4 rounded border border-gray-600">
                                    <div class="text-gray-400 text-xs mb-1">ธนาคารผู้โอน</div>
                                    <div class="text-lg font-semibold text-white">
                                        ${escapeHtml(data.data.bank_full)} 
                                        <span class="text-sm text-gray-400">(${escapeHtml(data.data.bank_short)})</span>
                                    </div>
                                </div>
                                <div class="bg-gray-800 p-4 rounded border border-gray-600">
                                    <div class="text-gray-400 text-xs mb-1">ผู้โอน</div>
                                    <div class="text-lg font-semibold text-white">${escapeHtml(data.data.sender)}</div>
                                </div>
                                <div class="bg-gray-800 p-4 rounded border border-gray-600">
                                    <div class="text-gray-400 text-xs mb-1">วัน-เวลาทำรายการ</div>
                                    <div class="text-lg font-semibold text-white">${escapeHtml(data.data.datetime)}</div>
                                </div>
                                <div class="bg-gray-800 p-4 rounded border border-gray-600 md:col-span-2">
                                    <div class="text-gray-400 text-xs mb-1">รหัสอ้างอิง (Ref)</div>
                                    <div class="text-lg font-mono text-yellow-400 tracking-wider">${escapeHtml(data.data.ref)}</div>
                                </div>
                            </div>
                        </div>`;
                }

                const statusColor = data.success ? 'bg-green-900/50 border-green-700' : 'bg-red-900/50 border-red-700';
                const statusIcon = data.success ? '✅' : '❌';
                const statusTextClass = data.success ? 'text-green-400' : 'text-red-400';
                const statusText = data.success ? 'ตรวจสอบสำเร็จ' : 'ตรวจสอบไม่สำเร็จ';

                container.innerHTML = `
                    <h2 class="font-bold mb-2 text-gray-300 text-lg border-b border-gray-700 pb-2">ผลลัพธ์ (Result):</h2>
                    
                    <div class="mb-4 p-4 rounded-lg border ${statusColor}">
                        <div class="flex items-center gap-3">
                            <div class="text-2xl">${statusIcon}</div>
                            <div>
                                <div class="font-bold ${statusTextClass}">${statusText}</div>
                                <div class="text-sm text-gray-300">${escapeHtml(data.message)}</div>
                            </div>
                        </div>
                    </div>

                    ${detailsHtml}

                    <h3 class="text-gray-400 text-sm font-semibold mb-2 border-l-4 border-gray-500 pl-2">ข้อมูลดิบ (Debug)</h3>
                    <div class="p-4 bg-gray-950 rounded border border-gray-700 overflow-x-auto">
                        <pre class="text-xs text-blue-400 font-mono whitespace-pre-wrap">${JSON.stringify(data, null, 4)}</pre>
                    </div>
                `;
            })
            .catch(error => {
                container.innerHTML = `<div class="mt-4 text-red-400 text-sm">เกิดข้อผิดพลาด: ${error.message}</div>`;
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<span>ตรวจสอบสลิป (Verify Slip)</span>';
            });
        });
    </script>
</body>
</html>
