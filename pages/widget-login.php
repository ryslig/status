<!DOCTYPE HTML>
<html lang="en">
<head>
<title>STATUS.RYSLIG.XYZ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="/images/quill.gif" type="image/gif">
<link rel="shortcut icon" href="/images/quill.gif" type="image/gif">
<style>
body {
	font-family: sans-serif;
	font-size: 12px;
	margin: 10px;
	word-break: break-all;
}
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
<?php
if(isset($_SESSION['alert'])) {
	echo '<br><div style="font-weight: bold;text-align: center;">'.$_SESSION['alert'].'</div>';
}
?>
</body>
</html>