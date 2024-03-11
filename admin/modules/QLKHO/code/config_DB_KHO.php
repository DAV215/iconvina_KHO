<?php
$mysqli_kho = new mysqli("127.0.0.1:3307","root","","iconvina_kho");

// Check connection
if ($mysqli_kho -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli_kho -> connect_error;
  exit();
}
?>