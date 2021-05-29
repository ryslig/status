<?php
if(isset($_GET['id'])) $timeline = get_timeline('permalink', null, null, intval($_GET['id']));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $timeline['timeline'][$_GET['id']]['author']['name'].': '.$timeline['timeline'][$_GET['id']]['status_raw']; ?></title>
	<link href="/style.css" rel="stylesheet" type="text/css">
	<script src="/app.js" type="text/javascript"></script>
	<!--[if IE]><style type="text/css">body { word-break: break-all; }</style><![endif]-->
	<style>
	.timeline {
		font-size: 1.4em;
	}
	</style>
</head>
<body>
<?php
#this sucks

if(!empty($timeline)) {
	echo '<br><br><table cellpadding="5" width="700" cellspacing="0" align="center" class="timeline">';
	foreach($timeline['timeline'] as $status) {
		$theme = mysqli_fetch_array(mysqli_query($conn, "SELECT bg_color, text_color, meta_color, border_color, link_color, home FROM `users` WHERE `username` = '".$status['author']['name']."'"), MYSQLI_ASSOC);
		echo '<style type="text/css">
		body, textarea {background-color: '.$theme['bg_color'].';color: '.$theme['text_color'].';}
		a, small a:hover {color: '.$theme['link_color'].';}
		.alert {border: 1px solid '.$theme['link_color'].';}
		label, q, small, small a {color: '.$theme['meta_color'].';}
		img.thumb, input[type=text], input[type=password], input[type=email], textarea, select {border: 1px solid '.$theme['border_color'].';}
		</style>';
		echo '<tr>
		<td width="49">
			<a href="'.$status['author']['link'].'">
				<img src="'.$status['author']['thumb'].'" width="60" height="60" class="thumb" alt="'.$status['author']['name'].'">
			</a>
		</td>
		<td>
			<strong><a href="'.$status['author']['link'].'">'.$status['author']['name'].'</a>:</strong>
			'.$status['status'].'
			<small>(<a href="'.$status['permalink'].'">'.$status['date']['timeago'].'</a>';
			if(isset($status['reply_to'])) echo ' <a href="'.$status['reply_to']['permalink'].'">in reply to '.$status['reply_to']['author'].'</a>';
			echo ')</small>';
			if($status['actions']['can_reply'] == true) {
				echo '<img src="/images/icon_reply.gif" alt="Reply" title="Reply" onclick="reply(\''.$status['id'].'\')" width="16" height="16">';
			}
			if($status['actions']['can_delete'] == true) {
				echo '<img src="/images/icon_delete.gif" alt="Delete" title="Delete" onclick="delete_status(\''.$status['id'].'\')" width="16" height="16">';
			}
		echo '</td></tr>';
	}
	echo '</table>';
} else {
	header('Location: /');
}
?>
</body>
</html>