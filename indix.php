<?php

require 'config.php';

if(!$_SESSION['user']){

header('Location:login.php');

}

echo('Hello ' . $_SESSION['user']['nom'])
?>