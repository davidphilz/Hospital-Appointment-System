<?php
session_start();
session_destroy();

//if (isset($_SESSION['admin'])){
  //  unset($_SESSION['admin']);

   // header("Location:../index.php");
//} else {
    header("Location:../index.php");
    exit();
?>