<h2>what are you doing? <small><span id="counter">140</span></small></h2>
<form method="post" action="/home">
	<textarea name="status" id="status" maxlength="140" oninput="count_it()" autocomplete="off" rows="3"></textarea>
	<br><br>
	<input type="submit" value="update">
</form>
<br><br>
<?php
if($type == 'mentions') {
	$timeline = get_timeline('mentions', $_GET['page']);
	$header = "people who have mentioned you recently:";
} elseif($type == 'public') {
	$timeline = get_timeline('public', $_GET['page']);
	$header = "what everyone is doing:";
} else {
	$header = "what your friends are doing:";
	if($theme['home'] == 1) {
		$timeline = get_timeline('timeline', $_GET['page']);
	} else {
		$timeline = get_timeline('currently', $_GET['page']);
	}
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
			<strong><a href="'.$status['author']['link'].'">'.$status['author']['name'].'</a>:</strong> '.$status['status'].' <small>(<a href="'.$status['permalink'].'">'.$status['date']['timeago'].'</a>';
			if(isset($status['reply_to'])) echo ' <a href="'.$status['reply_to']['permalink'].'">in reply to '.$status['reply_to']['author'].'</a>';
			echo ')</small> ';
			if($status['actions']['can_reply'] == true) echo '<img src="/images/icon_reply.gif" alt="Reply" title="Reply" onclick="reply(\''.$status['id'].'\')" width="16" height="16">';
			if($status['actions']['can_delete'] == true) echo '<img src="/images/icon_delete.gif" alt="Delete" title="Delete" onclick="delete_status(\''.$status['id'].'\')" width="16" height="16">';
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