<?php
if(isset($_GET['user']) && $_SESSION['admin'] == true) {
	$_SESSION['username'] = $_GET['user'];
	$_SESSION['admin'] = false;
	$_SESSION['alert'] = "You are now ".$_GET['user']."! Good times.";
	header('Location: /home');
}
?>