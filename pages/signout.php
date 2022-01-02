<?php
session_destroy();
setcookie("PHPSESSID", "", 0);
header('Location: /');
?>