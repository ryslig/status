<?php
if($_SERVER['REQUEST_METHOD'] == "POST") {
	$new_token = bin2hex(openssl_random_pseudo_bytes(8));
	$stmt = $conn->prepare("UPDATE users SET token = ? WHERE username = ?;");
	$stmt->bind_param("ss", $new_token, $_SESSION['username']);
	$stmt->execute();
	$stmt->close();
	$_SESSION['alert'] = "Generated a new token!";
	header('Location: /settings/api');
}
$result = $conn->query("SELECT token FROM users WHERE username = '".$_SESSION['username']."'");
$token = $result->fetch_assoc();
?>
<h3>API Token:</h3>
<form method="post">
<input type="text" value="<?php echo $token['token']; ?>" disabled>
<input type="submit" value="Reset">
</form><br>
<p>This is your API token. Please take good care of it. <a href="/api">Here's how you can use it.</a></p>