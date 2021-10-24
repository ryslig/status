<?php
if(isset($_GET['user']) && isset($_SESSION['username'])) {
	$stmt = $conn->prepare("DELETE FROM follows WHERE follower = ? AND following = ?");
	$stmt->bind_param('ss', $_SESSION['username'], $_GET['user']);
	$stmt->execute();
}