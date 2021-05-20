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
	header('Location: /home');
}
?>
<h2>what are you doing?</h2>
<form method="post" action="/home">
	<textarea name="status" maxlength="140" autocomplete="off" rows="3"></textarea>
	<br><br>
	<input type="submit" value="update">
</form>
<br><br>
<?php
switch($type) {
	case 'mentions':
		$timeline = get_timeline('mentions', $_GET['page']);
		$header = "people who have mentioned you recently:";
		break;
	case 'public':
		$timeline = get_timeline('public', $_GET['page']);
		$header = "what everyone is doing:";
		break;
	default:
		$header = "what your friends are doing:";
		if($theme['home'] == 1) {
			$timeline = get_timeline('timeline', $_GET['page']);
		} else {
			$timeline = get_timeline('currently', $_GET['page']);
		}
		break;
}

echo '<h2>'.$header.'</h2>
<ul class="nav">
	<li><a href="/home">Home</a></li>
	<li><a href="/home/mentions">Mentions</a></li>
	<li class="last"><a href="/home/public">Public</a></li>
</ul>
<br>';

if(!empty($timeline)) {
	echo '<table cellpadding="5" cellspacing="0" width="100%" class="timeline">';
	foreach($timeline['timeline'] as $status) {
		echo '<tr>
		<td width="49">
			<a href="'.$status['author']['link'].'">
				<img src="'.$status['author']['thumb'].'" width="45" height="45" class="thumb" alt="'.$status['author']['name'].'">
			</a>
		</td>
		<td>
			<strong><a href="'.$status['author']['link'].'">'.$status['author']['name'].'</a>:</strong>
			'.$status['status'].'
			<small title="'.$status['date']['timestamp'].'">('.$status['date']['timeago'].')</small>';
			if($status['actions']['can_delete'] == true) echo '<img src="/images/icon_delete.gif" onclick="delete_status(\''.$status['id'].'\')" width="16" height="16">';
		echo '</td></tr>';
	}
	echo '</table><br>';
	if($timeline['pagination']['newer'] == true) {
		if(isset($type)) {
			echo '<a href="/home/'.$type.'?page='.(intval($_GET['page'])-1).'" rel="prev"><button>&larr; Newer</button></a>';
		} else {
			echo '<a href="/home?page='.(intval($_GET['page'])-1).'" rel="prev"><button>&larr; Newer</button></a>';
		}
	}
	if($timeline['pagination']['older'] == true) {
		if(isset($type)) {
			echo '<a href="/home/'.$type.'?page='.(intval($_GET['page'])+1).'" rel="next"><button style="float: right">Older &rarr;</button></a>';
		} else {
			echo '<a href="/home?page='.(intval($_GET['page'])+1).'" rel="next"><button style="float: right">Older &rarr;</button></a>';
		}
	}
} else {
	echo "<h3>Welcome! Update your status, or find someone to follow!</h3>";
}
?>