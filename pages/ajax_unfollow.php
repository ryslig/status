<?php
if(isset($_GET['user'])) {
	if(isset($_SESSION['username'])) {
		if(!preg_match("/[^0-9a-zA-Z\s]/", $_GET['user'])) {
			if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `follows` WHERE `follower` = '".$_SESSION['username']."' AND `following` = '".$_GET['user']."';")) == 1) {
				mysqli_query($conn, "DELETE FROM `follows` WHERE `follower` = '".$_SESSION['username']."' AND `following` = '".$_GET['user']."';");
			}
		}
	}
}
?>