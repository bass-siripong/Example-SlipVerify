<?php
session_start();
include 'conn.php';

$clientId = 'o2sj72dozf4szxnl';
$clientSecret = 'b2j6evh4efisuc7ln3dg0ucthmbqqwwm';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = __DIR__ . '/slips/';
    $file = $_FILES['slip_image'];
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $tempPath = $file['tmp_name'];

    $ch = curl_init('https://suba.rdcw.co.th/v2/inquiry');
    curl_setopt($ch, CURLOPT_USERPWD, "{$clientId}:{$clientSecret}");
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'file' => new CURLFile($tempPath, $file['type'])
    ]);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = @json_decode(curl_exec($ch));
    curl_close($ch);

    if ($response && isset($response->data)) {
        $transRef    = $response->data->transRef ?? '';
        $sendingBank = $response->data->sendingBank ?? '';
        $transDate   = $response->data->transDate ?? '';
        $transTime   = $response->data->transTime ?? '';

        $cleanTransRef = preg_replace('/[^A-Za-z0-9]/', '', $transRef);
        $filename = 'slip_' . $cleanTransRef . '.' . $fileExtension;
        $destination = $uploadDir . $filename;
        $webPath = 'slips/' . $filename;

        if (move_uploaded_file($tempPath, $destination)) {
            $_SESSION['uploaded_image'] = $webPath;

            $check = $conn->prepare("SELECT slip_id FROM slip WHERE transRef = :transRef");
            $check->execute([':transRef' => $transRef]);

            if ($check->rowCount() === 0) {
                $stmt = $conn->prepare("
                    INSERT INTO slip (transRef, sendingBank, transDate, transTime)
                    VALUES (:transRef, :sendingBank, :transDate, :transTime)
                ");
                if ($stmt->execute([
                    ':transRef'    => $transRef,
                    ':sendingBank' => $sendingBank,
                    ':transDate'   => $transDate,
                    ':transTime'   => $transTime
                ])) {
                    $_SESSION['responseData'] = "✅ บันทึกลงฐานข้อมูลสำเร็จ";
                } else {
                    $errorInfo = $stmt->errorInfo();
                    $_SESSION['responseData'] = "❌ เกิดข้อผิดพลาด: " . $errorInfo[2];
                }
            } else {
                $_SESSION['responseData'] = "⚠️ ข้อมูลนี้มีอยู่แล้ว";
            }
        } else {
            $_SESSION['responseData'] = "❌ ไม่สามารถบันทึกสลิปได้";
        }
    } else {
        $_SESSION['responseData'] = "❌ ไม่สามารถอ่านข้อมูลจาก API ได้";
    }

    $responseRaw = curl_exec($ch);  // รับ raw string
    $response = @json_decode($responseRaw);
    curl_close($ch);

    $_SESSION['api_response_raw'] = $responseRaw;  // เก็บผลลัพธ์ JSON

    header("Location: index.php");
    exit();
}
