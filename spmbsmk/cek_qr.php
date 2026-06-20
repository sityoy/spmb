<?php
// 1. Cek Status Session
$ch1 = curl_init('http://127.0.0.1:3000/api/sessions');
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_USERPWD, "smkpb1:Smkpb123");
$status_res = curl_exec($ch1);
curl_close($ch1);

// 2. Tarik QR
$ch2 = curl_init('http://127.0.0.1:3000/api/sessions/default/auth/qr');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_USERPWD, "smkpb1:Smkpb123");
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$qr_res = curl_exec($ch2);
curl_close($ch2);

$qr_data = json_decode($qr_res, true);

echo "<h2>Pendeteksi Kerusakan WAHA</h2>";

echo "<h3>1. Status Mesin Saat Ini:</h3>";
echo "<textarea style='width:100%; height:100px; background:#f4f4f4;'>$status_res</textarea>";

echo "<h3>2. Respon Asli Tarik QR:</h3>";
echo "<textarea style='width:100%; height:100px; background:#f4f4f4;'>$qr_res</textarea>";

if(isset($qr_data['base64']) && $qr_data['base64'] != "") {
    echo '<h3>3. Gambar QR:</h3>';
    echo '<img src="'.$qr_data['base64'].'" style="width:300px; border:2px solid #000; padding:10px;">';
}
?>