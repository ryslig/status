<!DOCTYPE HTML>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>STATUS.RYSLIG.XYZ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {font-family: sans-serif;font-size: 12px;margin: 10px;}
</style>
<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	if(isset($_POST['username']) and isset($_POST['password'])) {
		if(!preg_match("/[^0-9a-zA-Z\s]/", $_POST['username'])) {
			$sql = mysqli_fetch_array(mysqli_query($conn, "SELECT username, password FROM users WHERE username = '".$_POST['username']."'"), MYSQLI_ASSOC);
			if(isset($sql['password'])) {
				if(password_verify($_POST['password'], $sql['password'])) {
					$_SESSION['username'] = $sql['username'];
				} else { $_SESSION['alert'] = "That password is incorrect!"; }
			} else { $_SESSION['alert'] = "That user does not exist in our database!"; }
		} else { $_SESSION['alert'] = "Please enter a valid username!"; }
	} else { $_SESSION['alert'] = "Please enter your credentials!"; }
	header('Location: /widget');
}
?>
</head>
<body>
<form method="post" action="/widget">
	<fieldset>
		<legend>Sign In</legend>
		Username:<br><input type="text" maxlength="16" name="username"><br><br>
		Password:<br><input type="password" maxlength="64" name="password"><br><br>
		<input type="submit" value="Sign In">
	</fieldset>
</form>
<center>
<?php
if(isset($_SESSION['alert'])) {
	echo '<p><strong>'.$_SESSION['alert'].'</strong></p>';
}
?>
<p>Don't have an account? <a href="/signup">Create one!</a> (not mobile friendly)</p>
</center>
</body>
</html>