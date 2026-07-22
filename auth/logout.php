<?php
if  (session_status() === PHP_SESSION_NONE) {
  session_start();
  echo 'run successfully';
}
session_unset();
session_destroy();
header("Location: /index.php");
exit;

echo 'run successfully';
?>