<?php
session_start();
$_SESSION = array(); //Очищаем сессию
session_destroy(); //Уничтожаем
header("Location: login.php");
?>
