<?php
$hostname = "localhost";
$username = "root";
$password = "";
$db   = "tepatkasir";

$conn = mysqli_connect($hostname, $username, $password, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>