<?php
$current_password = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE username = '".$_SESSION['username']."'"));
	
if(isset($_POST['password']) and isset($_POST['confirm_password'])) {
	if(password_verify($_POST['current_password'], $current_password['password'])) {
		if($_POST['password'] == $_POST['confirm_password']) {
			if(strlen($_POST['password']) >= 6) {
				if(strlen($_POST['password']) <= 64) {
					$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
					mysqli_query($conn, "UPDATE users SET password = '".mysqli_real_escape_string($conn, $password)."' WHERE username = '".$_SESSION['username']."'");
					$_SESSION['alert'] = "Password successfully changed!";
				} else { $_SESSION['alert'] = "Your password cannot be longer than 64 characters!"; }
			} else { $_SESSION['alert'] = "Your password must be longer than 6 characters!"; }
		} else { $_SESSION['alert'] = "Your passwords must match!"; }
	} else { $_SESSION['alert'] = "Password is incorrect!"; }
	header('Location: /settings/password');
}
?>
<h3>Change Your Password:</h3>
<form method="post" action="/settings/password">
	<table cellpadding="0" cellspacing="8" border="0">
		<tr>
			<td align="right"><label for="password">Current Password:</label></td>
			<td><input type="password" name="current_password" maxlength="64"></td>
		</tr>
		<tr>
			<td align="right"><label for="password">New Password:</label></td>
			<td><input type="password" name="password" maxlength="64"></td>
		</tr>
		<tr>
			<td align="right"><label for="confirm_password">Confirm New Password:</label></td>
			<td><input type="password" name="confirm_password" maxlength="64"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Save"></td>
		</tr>
	</table>
</form>