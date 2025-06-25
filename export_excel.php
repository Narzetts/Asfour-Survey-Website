<?php
$conn = new mysqli("localhost", "root", "", "asfourmy_asfour");
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rekap_asfour.xls");

echo "<table border='1'>";
echo "<tr>
    <th>No</th>
    <th>Yang Ngisi</th>
    <th>Angkatan</th>
    <th>Aspirasi</th>
    <th>Link Gambar</th>
    <th>Waktu</th>
</tr>";

$result = $conn->query("SELECT * FROM hasil_survei");
while ($row = $result->fetch_assoc()) {
    $foto_url = $row['foto'] ? 'http://' . $_SERVER['HTTP_HOST'] . '/uploads/' . $row['foto'] : '';

    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars($row['makanan']) . "</td>";
    echo "<td>" . htmlspecialchars($row['makan_perhari']) . "</td>";
    echo "<td>" . nl2br(htmlspecialchars($row['pengalaman'])) . "</td>";
    echo "<td>";
    if ($foto_url) {
        echo "<a href='$foto_url'>$foto_url</a>";
    } else {
        echo "-";
    }
    echo "</td>";
    echo "<td>" . ($row['waktu'] ?? '-') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
