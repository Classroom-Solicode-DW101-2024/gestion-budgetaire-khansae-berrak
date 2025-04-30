<?php
session_start()
if (!isset($_SESSION['user']['id'])) {
   header(location:login.php)
   exit();
}
$iduser = $_session['user']['id'];

?>