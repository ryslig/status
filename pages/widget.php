<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>STATUS.RYSLIG.XYZ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
$theme = mysqli_fetch_array(mysqli_query($conn, "SELECT bg_color, text_color, border_color, meta_color, link_color FROM users WHERE username = '".$_SESSION['username']."'"), MYSQLI_ASSOC);
echo '<style type="text/css">
body {font-family: sans-serif;font-size: 12px;margin: 0;background-color: '.$theme['bg_color'].';color: '.$theme['text_color'].';word-break: break-word;}
form {text-align: center;}
hr {border: 0;border-bottom: 1px dashed '.$theme['border_color'].';}
p {margin: 4px 8px;}
small {color: '.$theme['meta_color'].';}
small a {word-break: inherit;}
a {color: '.$theme['link_color'].';word-break: break-all;}
small a {text-decoration: none;color: inherit;}
small a:hover {text-decoration: underline;color: '.$theme['link_color'].';}
img[onclick] {vertical-align: middle;display: inline-block;}
td input[type=text] {width: 100%;}
hr.first {margin-top: 0;}
</style>';
?>
<script>
function reply(id) {
	document.getElementById("reply").value = id;
	document.getElementById("update").value = "Reply";
	document.getElementById("status").focus();
}
</script>
</head>
<body>
<form method="post" action="/widget" autocomplete="off">
	<table cellpadding="6" cellspacing="0" width="100%">
		<tr>
			<td width="100%"><input type="text" name="status" id="status" maxlength="140"><input type="hidden" name="reply" id="reply"></td>
			<td><input type="submit" id="update" value="Update"></td>
		</tr>
	</table>
</form>
<hr class="first">
<?php
$timeline = new Timeline;
$timeline->type = "timeline";
$timeline->paging = false;
$timeline->display(2);
?>
<br>
</body>
</html>