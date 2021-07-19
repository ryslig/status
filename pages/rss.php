<?php
$timeline = new Timeline;
$timeline->paging = false;
$timeline->limit = 15;

if(isset($_GET['user'])) {
	$timeline->type = "profile";
	if(!preg_match("/[^0-9a-zA-Z\s]/", $_GET['user'])) {
		$sql = mysqli_fetch_array(mysqli_query($conn, "SELECT `username` FROM `users` WHERE `username` = '".mysqli_real_escape_string($conn, $_GET['user'])."'"), MYSQLI_ASSOC);
		if(!empty($sql['username'])) {
			$timeline->user = $sql['username'];
		}
	}
	echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>'.strtoupper($_GET['user']).'\'S PROFILE :: STATUS.RYSLIG.XYZ</title><link>http://status.ryslig.xyz/profile?user='.$_GET['user'].'</link><description>This is the description.</description><language>en-us</language><ttl>30</ttl>';
	$timeline->display(4);
	echo '</channel></rss>';
} else {
	$timeline->type ="public";
	echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>STATUS.RYSLIG.XYZ</title><link>http://status.ryslig.xyz/</link><description>This is the description.</description><language>en-us</language><ttl>30</ttl>';
	$timeline->display(4);
	echo '</channel></rss>';
}
?>