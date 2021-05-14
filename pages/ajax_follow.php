<?php
if(isset($_GET['user'])) {
	if(isset($_SESSION['username'])) {
		if(strtolower($_GET['user']) !== strtolower($_SESSION['username'])) {
			if(!preg_match("/[^0-9a-zA-Z\s]/", $_GET['user'])) {
				if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `follows` WHERE `follower` = '".$_SESSION['username']."' AND `following` = '".$_GET['user']."';")) == 0) {
					if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '".$_GET['user']."';")) == 1) {
						mysqli_query($conn, "INSERT INTO follows (follower, following) VALUES ('".$_SESSION['username']."', '".$_GET['user']."')");
					}
				}
			}
		}
	}
}
?>