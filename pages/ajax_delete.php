<?php
if(isset($_GET['id'])) {
	if(isset($_SESSION['username'])) {
		$row = mysqli_fetch_array(mysqli_query($GLOBALS['conn'], "SELECT * FROM `updates` WHERE `id` = '".intval($_GET['id'])."';"), MYSQLI_ASSOC);
		if($_SESSION['username'] == $row['author']) {
			mysqli_query($GLOBALS['conn'], "DELETE FROM `updates` WHERE `id` = '".intval($_GET['id'])."';");
		}
	}
}
?>