<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "asfourmy_asfour");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data POST
$makanan = isset($_POST['makanan']) ? (array)$_POST['makanan'] : [];
$makan_perhari = $_POST['makan_perhari'] ?? '';
$pengalaman = $_POST['pengalaman'] ?? '';

// Gabungkan makanan menjadi string
$makanan_str = implode(', ', $makanan);

// Upload Foto
$foto = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed_ext)) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        // Buat nama unik agar tidak tertimpa
        $foto = uniqid("img_", true) . '.' . $ext;
        $target_file = $target_dir . $foto;

        if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            echo "Gagal mengupload file.";
            $foto = '';
        }
    } else {
        echo "Format file tidak didukung.";
        $foto = '';
    }
}

// Simpan data ke database
$ip_address = $_SERVER['REMOTE_ADDR'];
$sql = "INSERT INTO hasil_survei (makanan, makan_perhari, pengalaman, foto, ip_address) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $makanan_str, $makan_perhari, $pengalaman, $foto, $ip_address);

if ($stmt->execute()) {
    // Arahkan ke index.html#thanks
    header("Location: terimakasih.html");
    exit();
} else {
    echo "Gagal menyimpan data: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
