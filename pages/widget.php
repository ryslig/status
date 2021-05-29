<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>STATUS.RYSLIG.XYZ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
$theme = mysqli_fetch_array(mysqli_query($conn, "SELECT bg_color, text_color, border_color, meta_color, link_color FROM users WHERE username = '".$_SESSION['username']."'"), MYSQLI_ASSOC);
echo '<style type="text/css">
body {font-family: sans-serif;font-size: 12px;margin: 5px 0;background-color: '.$theme['bg_color'].';color: '.$theme['text_color'].';word-break: break-all;}
form {text-align: center;}
hr {border: 0;border-bottom: 1px dashed '.$theme['border_color'].';}
p {margin: 4px 8px;}
small {color: '.$theme['meta_color'].';}
a {color: '.$theme['link_color'].';}
small a {text-decoration: none;color: inherit;}
small a:hover {text-decoration: underline;color: '.$theme['link_color'].';}
</style>';
?>
</head>
<body>
<?php
if(isset($_POST['status'])) {
	if(isset($_SESSION['username'])) {
		$status = trim($_POST['status']);
		if(!empty($status)) {
			if(strlen($status) > 2) {
				if($_SESSION['last_status'] !== $status) {
					$query = mysqli_query($GLOBALS['conn'], "SELECT * FROM `updates` WHERE `date` > DATE_SUB(NOW(), INTERVAL 30 SECOND) AND `author` = '".$_SESSION['username']."';");
					$rows = mysqli_num_rows($query);
					if($rows == 0) {
						$_SESSION['last_status'] = $status;
						mysqli_query($conn, "INSERT INTO updates (author, status) VALUES ('".$_SESSION['username']."', '".mysqli_real_escape_string($conn, $status)."')");
					} else { $_SESSION['alert'] = "Please wait 30 seconds before updating your status!"; }
				} else { $_SESSION['alert'] = "Stop repeating yourself!";}
			} else { $_SESSION['alert'] = "Your status must be longer than two characters"; }
		} else { $_SESSION['alert'] = "We need something here."; }
	} else { $_SESSION['alert'] = "We need something here."; }
	header('Location: /widget');
}
?>
<form method="post" action="/widget" autocomplete="off">
<input type="text" name="status" maxlength="140">
<input type="submit" value="Update">
</form>
<?php
$timeline = get_timeline('timeline');
foreach($timeline['timeline'] as $status) {
	echo '<hr><p><strong><a href="'.$status['author']['link'].'" target="_blank">'.$status['author']['name'].'</a>:</strong> '.$status['status'].' <small>(<a href="'.$status['permalink'].'" target="_blank">'.$status['date']['timeago'].'</a>';
	if(isset($status['reply_to'])) echo ' <a href="'.$status['reply_to']['permalink'].'" target="_blank">in reply to '.$status['reply_to']['author'].'</a>';
	echo ')</small></p>';
}

?>
</body>
</html>