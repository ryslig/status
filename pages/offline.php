<h2>welcome to the website. <a href="/signup">sign up</a> or <a href="/signin">sign in</a>.</h2>
<p>or you can check out what's going on right now!</p>
<table cellpadding="5" width="100%" class="timeline">
<?php
	$timeline = get_timeline('offline');
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
			<small>(<a href="'.$status['permalink'].'">'.$status['date']['timeago'].'</a>';
			if(isset($status['reply_to'])) {
				echo ' <a href="'.$status['reply_to']['permalink'].'">in reply to '.$status['reply_to']['author'].'</a>';
			}
			echo ')</small>
		</td>
		</tr>';
	}
?>
</table>