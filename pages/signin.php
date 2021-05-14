<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	if(isset($_POST['username']) and isset($_POST['password'])) {
		if(!preg_match("/[^0-9a-zA-Z\s]/", $_POST['username'])) {
			$sql = mysqli_fetch_array(mysqli_query($conn, "SELECT username, password FROM users WHERE username = '".$_POST['username']."'"), MYSQLI_ASSOC);
			if(isset($sql['password'])) {
				if(password_verify($_POST['password'], $sql['password'])) {
					$_SESSION['username'] = $sql['username'];
					header('Location: /home');
				} else { $_SESSION['alert'] = "That password is incorrect!"; }
			} else { $_SESSION['alert'] = "That user does not exist in our database!"; }
		} else { $_SESSION['alert'] = "Please enter a valid username!"; }
	} else { $_SESSION['alert'] = "Please enter your credentials!"; }
	if(isset($_SESSION['alert'])) {
		header('Location: /signin');
	}
}
?>
<h2>welcome back!</h2>
<form method="post" action="/signin">
	<table cellpadding="0" cellspacing="8" border="0">
		<tr>
			<td align="right"><label for="username">Username:</td>
			<td><input type="text" maxlength="16" name="username"></td>
		</tr>
		<tr>
			<td align="right"><label for="password">Password:</td>
			<td><input type="password" maxlength="64" name="password"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Sign In"></td>
		</tr>
	</table>
</form>