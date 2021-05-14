<?php
if($_SERVER['REQUEST_METHOD'] == "POST") {
	# SCREEN NAME
	if(isset($_POST['username'])) {
		if(strlen($_POST['username']) >= 3) {
			if(strlen($_POST['username']) <= 16) {
				if(!preg_match("/[^0-9a-zA-Z\s]/", $_POST['username'])) {
					if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '".$_POST['username']."';")) == 0) {
						$username = mb_convert_encoding($_POST['username'], 'ASCII');
					} else { $_SESSION['alert'] = "That username is already taken!"; }
				} else { $_SESSION['alert'] = "Username cannot contain special characters!"; }
			} else { $_SESSION['alert'] = "Username cannot be longer than 16 characters!"; }
		} else { $_SESSION['alert'] = "Username must be longer than 3 characters!"; }
	} else { $_SESSION['alert'] = "Please enter a username!"; }
	# FULL NAME
	if(isset($_POST['fullname'])) {
		if(strlen(trim($_POST['fullname'])) >= 2) {
			if(strlen(trim($_POST['fullname'])) <= 20) {
				$fullname = mb_convert_encoding(trim($_POST['fullname']), 'ASCII');
			} else { $_SESSION['alert'] = "Your full name cannot be longer than 20 characters!"; }
		} else { $_SESSION['alert'] = "Your full name must be longer than 2 characters!"; }
	} else { $_SESSION['alert'] = "Please enter your full name! Or you can just make something up."; }
	# PASSWORD AND CONFIRM PASSWORD
	if(isset($_POST['password']) and isset($_POST['confirm_password'])) {
		if($_POST['password'] == $_POST['confirm_password']) {
			if(strlen($_POST['password']) >= 6) {
				if(strlen($_POST['password']) <= 64) {
					$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
				} else { $_SESSION['alert'] = "Your password cannot be longer than 64 characters!"; }
			} else { $_SESSION['alert'] = "Your password must be longer than 6 characters!"; }
		} else { $_SESSION['alert'] = "Your passwords must match!"; }
	} else { $_SESSION['alert'] = "Please enter a password!"; }
	# INSERT INTO DATABASE
	if(!isset($_SESSION['alert'])) {
		mysqli_query($conn, "INSERT INTO users (username, fullname, password) VALUES ('".mysqli_real_escape_string($conn, $username)."', '".mysqli_real_escape_string($conn, $fullname)."', '".mysqli_real_escape_string($conn, $password)."')");
		$_SESSION['username'] = $username;
		$_SESSION['alert'] = "Account successfully created!";
		header('Location: /home');
	} else {
		header('Location: /signup');
	}
}
?>
<h2>create an account:</h2>
<p>note: passwords <b>are</b> hashed! i'm not stupid</p>
<form method="post" action="/signup">
	<table cellpadding="0" cellspacing="8" border="0">
		<tr>
			<td align="right"><label for="username">Username:</label></td>
			<td><input type="text" name="username" maxlength="16"></td>
		</tr>
		<tr>
			<td align="right"><label for="fullname">Full Name:</label></td>
			<td><input type="text" name="fullname" maxlength="20"></td>
		</tr>
		<tr>
			<td align="right"><label for="password">Create Password:</label></td>
			<td><input type="password" name="password" maxlength="64"></td>
		</tr>
		<tr>
			<td align="right"><label for="confirm_password">Confirm Password:</label></td>
			<td><input type="password" name="confirm_password" maxlength="64"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Sign Up"></td>
		</tr>
	</table>
</form>