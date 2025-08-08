<?php
header('Content-Type: application/json; charset=utf-8');

$rawData = file_get_contents('php://input');
$folder = __DIR__ . "/admin_data";
if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
}
file_put_contents($folder . "/debug_log.txt", date('Y-m-d H:i:s') . " | " . $rawData . PHP_EOL, FILE_APPEND);

$data = json_decode($rawData, true);

if (!$data || !isset($data["name"])) {
    http_response_code(400);
    echo json_encode(["message" => "Dữ liệu gửi lên không hợp lệ."]);
    exit;
}

$name            = $data["name"] ?? "";
$birthYear       = $data["birthYear"] ?? "";
$parentPhone     = $data["parentPhone"] ?? "";
$scoreText       = $data["score_text"] ?? "";
$scoreTracNghiem = $data["score_tracnghiem"] ?? "";
$scoreViet       = $data["score_viet"] ?? "";
$timeTaken       = $data["timeTaken"] ?? ""; // Lấy thời gian làm bài

// Thời điểm lưu file
$timestamp = date('d/m/Y');

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = $folder . "/results.xlsx";

try {
    if (!file_exists($file)) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(
            ["Họ tên", "Năm sinh", "SĐT phụ huynh", "Kết quả (text)", "Trắc nghiệm", "Viết", "Thời gian"],
            null,
            'A1'
        );
    } else {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $expectedHeaders = ["Họ tên", "Năm sinh", "SĐT phụ huynh", "Kết quả (text)", "Trắc nghiệm", "Viết", "Thời gian"];
        $currentHeaders = $sheet->rangeToArray('A1:G1')[0];
        if ($currentHeaders !== $expectedHeaders) {
            $sheet->fromArray($expectedHeaders, null, 'A1');
        }
    }

    $lastRow = $sheet->getHighestRow() + 1;

    $sheet->setCellValue("A{$lastRow}", $name);
    $sheet->setCellValue("B{$lastRow}", $birthYear);
    $sheet->setCellValue("C{$lastRow}", $parentPhone);
    $sheet->setCellValue("D{$lastRow}", $scoreText);
    $sheet->setCellValue("E{$lastRow}", $scoreTracNghiem);
    $sheet->setCellValue("F{$lastRow}", $scoreViet);
    $sheet->setCellValue("G{$lastRow}", $timestamp);

    $writer = new Xlsx($spreadsheet);
    $writer->save($file);

    echo json_encode(["message" => "Kết quả đã lưu thành công"]);
} catch (Exception $e) {
    http_response_code(500);
    file_put_contents($folder . "/error_log.txt", date('Y-m-d') . " | " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo json_encode([
        "message" => "Lỗi khi lưu kết quả",
        "error" => $e->getMessage()
    ]);
}
