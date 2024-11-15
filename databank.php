<?php
$Mysql=new mysqli("localhost","root","","groenten_webshop");

if($Mysql->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}


?>