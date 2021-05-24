<?php
$user_info = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '".$_SESSION['username']."'"), MYSQLI_ASSOC);
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	if(!empty($_POST['fullname'])) {
		if(!empty($_POST['website'])) {
			if(filter_var($_POST['website'], FILTER_VALIDATE_URL)) {
				$_SESSION['alert'] = "Changes saved!";
				mysqli_query($conn, "UPDATE users SET fullname = '".mysqli_real_escape_string($conn, trim($_POST['fullname']))."', quote = '".mysqli_real_escape_string($conn, trim($_POST['quote']))."', website = '".mysqli_real_escape_string($conn, trim($_POST['website']))."' WHERE username = '".$_SESSION['username']."'");
			} else {
				$_SESSION['alert'] = "You did not provide a valid website. Did you forget the protocol?";
				mysqli_query($conn, "UPDATE users SET fullname = '".mysqli_real_escape_string($conn, trim($_POST['fullname']))."', quote = '".mysqli_real_escape_string($conn, trim($_POST['quote']))."' WHERE username = '".$_SESSION['username']."'");
			}
		} else {
			$_SESSION['alert'] = "Changes saved!";
			mysqli_query($conn, "UPDATE users SET fullname = '".mysqli_real_escape_string($conn, trim($_POST['fullname']))."', quote = '".mysqli_real_escape_string($conn, trim($_POST['quote']))."', website = null WHERE username = '".$_SESSION['username']."'");
		}
	} else {
		$_SESSION['alert'] = "You need to enter a name!";
	}
	header('Location: /settings/profile');
}
?>
<h3>Edit Profile Information:</h3>
<form method="post" action="/settings/profile">
	<table cellpadding="0" cellspacing="8" border="0">
		<tr>
			<td align="right"><label for="fullname">Full Name:</label></td>
			<td><input type="text" name="fullname" maxlength="20" value="<?php echo htmlentities($user_info['fullname']); ?>" autocomplete="off"></td>
		</tr>
		<tr>
			<td align="right"><label for="quote">Quote:</label></td>
			<td><input type="text" name="quote" maxlength="80" value="<?php echo htmlentities($user_info['quote']); ?>" autocomplete="off"></td>
		</tr>
		<tr>
			<td align="right"><label for="website">Website:</label></td>
			<td><input type="text" name="website" maxlength="60" value="<?php echo htmlentities($user_info['website']); ?>" autocomplete="off"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Update"></td>
		</tr>
	</table>
</form>