<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	if(ctype_xdigit(str_replace('#', '', $_POST['bg_color'])) and strlen($_POST['bg_color']) == 7) {
		mysqli_query($conn, "UPDATE users SET bg_color = '".$_POST['bg_color']."' WHERE username = '".$_SESSION['username']."'");
	}
	if(ctype_xdigit(str_replace('#', '', $_POST['text_color'])) and strlen($_POST['text_color']) == 7) {
		mysqli_query($conn, "UPDATE users SET text_color = '".$_POST['text_color']."' WHERE username = '".$_SESSION['username']."'");
	}
	if(ctype_xdigit(str_replace('#', '', $_POST['meta_color'])) and strlen($_POST['meta_color']) == 7) {
		mysqli_query($conn, "UPDATE users SET meta_color = '".$_POST['meta_color']."' WHERE username = '".$_SESSION['username']."'");
	}
	if(ctype_xdigit(str_replace('#', '', $_POST['border_color'])) and strlen($_POST['border_color']) == 7) {
		mysqli_query($conn, "UPDATE users SET border_color = '".$_POST['border_color']."' WHERE username = '".$_SESSION['username']."'");
	}
	if(ctype_xdigit(str_replace('#', '', $_POST['link_color'])) and strlen($_POST['link_color']) == 7) {
		mysqli_query($conn, "UPDATE users SET link_color = '".$_POST['link_color']."' WHERE username = '".$_SESSION['username']."'");
	}
	mysqli_query($conn, "UPDATE users SET home = '".intval($_POST['homepage_style'])."' WHERE username = '".$_SESSION['username']."'");
	$_SESSION['alert'] = "Changes saved!";
	header('Location: /settings/design');
}

$user_info = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '".$_SESSION['username']."'"), MYSQLI_ASSOC);
?>
<h3>Edit Design:</h3>
<form method="post" action="/settings/design">
	<table cellpadding="0" cellspacing="8" border="0">
		<tr>
			<td align="right"><label for="bg_color">Background:</label></td>
			<td><input type="color" name="bg_color" id="bg_color" value="<?php echo $user_info['bg_color']; ?>"></td>
		</tr>
		<tr>
			<td align="right"><label for="text_color">Text Color:</label></td>
			<td><input type="color" name="text_color" id="text_color" value="<?php echo $user_info['text_color']; ?>"></td>
		</tr>
		<tr>
			<td align="right"><label for="meta_color">Meta Text Color:</label></td>
			<td><input type="color" name="meta_color" id="meta_color" value="<?php echo $user_info['meta_color']; ?>"></td>
		</tr>
		<tr>
			<td align="right"><label for="border_color">Border Color:</label></td>
			<td><input type="color" name="border_color" id="border_color" value="<?php echo $user_info['border_color']; ?>"></td>
		</tr>
		<tr>
			<td align="right"><label for="link_color">Accent Color:</label></td>
			<td><input type="color" name="link_color" id="link_color" value="<?php echo $user_info['link_color']; ?>"></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td align="right"><label for="homepage_style">Homepage Style:</label></td>
			<td>
				<select name="homepage_style">
					<?php
					if($user_info['home'] == 0) {
						echo '<option value="0" selected>Currently</option>
						<option value="1">Timeline</option>';
					} else {
						echo '<option value="0">Currently</option>
						<option value="1" selected>Timeline</option>';
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Save"> or <button onclick="reset_theme()">Reset</button></td>
		</tr>
	</table>
</form>
<script>
function reset_theme() {
	if (confirm("Are you sure you want to reset your theme colors?")) {
		document.getElementById("bg_color").value = "#FFFFFF";
		document.getElementById("text_color").value = "#000000";
		document.getElementById("meta_color").value = "#808080";
		document.getElementById("border_color").value = "#D3D3D3";
		document.getElementById("link_color").value = "#0000FF";
	}
}
</script>