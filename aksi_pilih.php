<?php
$conn = new mysqli("localhost", "root", "", "asfourmy_asfour");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (!isset($_POST['pilih'])) {
    echo "<script>alert('Tidak ada data yang dipilih.'); window.location.href='admin.php';</script>";
    exit;
}

$ids = $_POST['pilih'];
$idList = implode(",", array_map('intval', $ids));

if (isset($_POST['hapus'])) {
    $sql = "DELETE FROM hasil_survei WHERE id IN ($idList)";
    $conn->query($sql);
    echo "<script>alert('Data berhasil dihapus.'); window.location.href='admin.php';</script>";
    exit;
}

if (isset($_POST['export'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=data_terpilih.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "ID\tWaktu\tMakanan\tMakan/Hari\tAspirasi\n";

    $sql = "SELECT * FROM hasil_survei WHERE id IN ($idList)";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        echo $row['id'] . "\t" .
             $row['waktu'] . "\t" .
             $row['makanan'] . "\t" .
             $row['makan_perhari'] . "\t" .
             str_replace(["\n", "\r"], ' ', $row['pengalaman']) . "\n";
    }
    exit;
}

$conn->close();
?>
