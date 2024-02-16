<?php
$mysqli = new mysqli("127.0.0.1:3307","root","","iconvina_thuchi");

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}
?>