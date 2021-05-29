<?php
$profile = mysqli_fetch_array(mysqli_query($conn, "SELECT username, fullname, quote, website FROM `users` WHERE `username` = '".$_GET['user']."';"), MYSQLI_ASSOC);
?>
<h2><?php echo $profile['username'] ?>'s latest updates <a href="/rss?user=<?php echo $profile['username'] ?>"><img src="/images/feed.png" width="16" height="16" alt="RSS Feed"></a></h2>
<?php
	$timeline = get_timeline('profile', $_GET['page'], $profile['username']);
?>
<div style="margin: 1em">
	<?php
		foreach($timeline['timeline'] as $status) {
			echo '<p>'.$status['status'].' <small>(<a href="'.$status['permalink'].'">'.$status['date']['timeago'].'</a>';
			if(isset($status['reply_to'])) {
				echo ' <a href="'.$status['reply_to']['permalink'].'">in reply to '.$status['reply_to']['author'].'</a>';
			}
			echo ')</small>';
			if($status['actions']['can_reply'] == true) echo '<img src="/images/icon_reply.gif" alt="Reply" title="Reply" onclick="reply(\''.$status['id'].'\')" width="16" height="16">';
			if($status['actions']['can_delete'] == true) echo '<img src="/images/icon_delete.gif" alt="Delete" title="Delete" onclick="delete_status(\''.$status['id'].'\')" width="16" height="16">';
			'</p>';
		}
	?>
</div>
<br>
<?php
	if($timeline['pagination']['newer'] == true) echo '<a href="/profile?user='.$profile['username'].'&page='.(intval($_GET['page'])-1).'" rel="prev"><button>&larr; Newer</button></a>';
	if($timeline['pagination']['older'] == true) echo '<a href="/profile?user='.$profile['username'].'&page='.(intval($_GET['page'])+1).'" rel="next"><button style="float: right">Older &rarr;</button></a>';
?>
</td>
<td width="100" align="center" valign="top">
<a href="/images/profiles/<?php echo $profile['username'] ?>.gif"><img src="/images/profiles/<?php echo $profile['username'] ?>.gif" width="70" height="70" alt="<?php echo $profile['username'] ?>" class="thumb"></a>
<h2><?php
	if(!empty($profile['website'])) {
		echo '<a href="'.htmlspecialchars($profile['website']).'">'.htmlspecialchars($profile['fullname']).'</a>';
	} else {
		echo htmlspecialchars($profile['fullname']);
	}
?></h2>
<?php
	if(!empty($profile['quote'])) echo '<q>'.htmlspecialchars($profile['quote']).'</q><br>';
?>
<br>
<?php
	if(isset($_SESSION['username'])) {
		if($_SESSION['username'] !== $profile['username']) {
			$follow_exists = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `follows` WHERE `follower` = '".$_SESSION['username']."' AND `following` = '".$profile['username']."';"));
			if($follow_exists == 1) {
				echo '<button onclick="unfollow_user(\''.$profile['username'].'\')">Unfollow</button>';
			} else {
				echo '<button onclick="follow_user(\''.$profile['username'].'\')">Follow</button>';
			}
		} else {
			echo '<button disabled>Follow</button>';
		}
	}
?>