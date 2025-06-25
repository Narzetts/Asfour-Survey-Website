<?php
$conn = new mysqli("localhost", "root", "", "asfourmy_asfour");
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM hasil_survei WHERE id = $id");
}
header("Location: dorimutaimu.php");
exit;
?>
