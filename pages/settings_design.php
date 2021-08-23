<?php
function color_valid($color) {
	if(ctype_xdigit(str_replace('#', '', $color)) and strlen($color) == 7) {
		return true;
	} else {
		return false;
	}
}
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	if(color_valid($_POST['bg_color']) == true) {
		if(color_valid($_POST['text_color']) == true) {
			if(color_valid($_POST['meta_color']) == true) {
				if(color_valid($_POST['border_color']) == true) {
					if(color_valid($_POST['link_color']) == true) {
						if(color_valid($_POST['highlight_color']) == true) {
							$stmt = $conn->prepare("UPDATE users SET bg_color = ?, text_color = ?, meta_color = ?, border_color = ?, link_color = ?, highlight_color = ?, home = ? WHERE username = ?;");
							$stmt->bind_param("ssssssis", $_POST['bg_color'], $_POST['text_color'], $_POST['meta_color'], $_POST['border_color'], $_POST['link_color'], $_POST['highlight_color'], $_POST['homepage_style'], $_SESSION['username']);
							$stmt->execute();
							$stmt->close();
							$_SESSION['alert'] = "Changes saved!";
						}
					}
				}
			}
		}
	}
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
			<td align="right"><label for="highlight_color">Highlight Color:</label></td>
			<td><input type="color" name="highlight_color" id="highlight_color" value="<?php echo $user_info['highlight_color']; ?>"></td>
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