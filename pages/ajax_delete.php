<?php
if(isset($_GET['id']) && isset($_SESSION['username'])) {
	$stmt = $conn->prepare("DELETE FROM updates WHERE id = ? AND author = ?");
	$stmt->bind_param('is', $_GET['id'], $_SESSION['username']);
	$stmt->execute();
}