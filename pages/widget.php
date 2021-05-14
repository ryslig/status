<!DOCTYPE HTML>
<html lang="en">
<head>
<title>STATUS.RYSLIG.XYZ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="/images/quill.gif" type="image/gif">
<link rel="shortcut icon" href="/images/quill.gif" type="image/gif">
<?php

$theme = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '".$_SESSION['username']."'"), MYSQLI_ASSOC);
echo '<style>
body {
	font-family: sans-serif;
	font-size: 12px;
	margin: 5px 0;
	background-color: '.$theme['bg_color'].';
	color: '.$theme['text_color'].';
	word-break: break-all;
}

form {
	text-align: center;
}

hr {
	border: 0;
	border-bottom: 1px dashed '.$theme['border_color'].';
}

p {
	margin: 4px 8px;
}

small {
	color: '.$theme['meta_color'].';
}

a {
	color: '.$theme['link_color'].';
}
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
<form method="post" action="/widget">
	<input type="text" name="status" placeholder="What are you doing?" maxlength="140" autocomplete="off">
	<input type="submit" value="Update">
</form>
<?php
$timeline = get_timeline('timeline');
foreach($timeline['timeline'] as $status) {
	echo '<hr><p><strong>'.$status['author']['name'].':</strong> '.$status['status'].' <small>('.$status['date']['timeago'].')</small></p>';
}
?>
</body>
</html>