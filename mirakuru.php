<?php
session_start();
session_destroy();
header("Location: futaridakeno.php");
exit;
?>