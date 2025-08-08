<?php
$zipFile = 'vendor.zip';
$extractTo = __DIR__ . '/vendor'; // Thư mục sẽ chứa thư viện sau khi giải nén

if (!file_exists($zipFile)) {
    die("❌ Không tìm thấy file zip: $zipFile");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo "✅ Giải nén thành công vào thư mục 'vendor/'!";
} else {
    echo "❌ Giải nén thất bại.";
}
?>
