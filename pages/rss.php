<?php
if(isset($_GET['user'])) {
	if(!preg_match("/[^0-9a-zA-Z\s]/", $_GET['user'])) {
		$sql = mysqli_query($conn, "SELECT `username` FROM `users` WHERE `username` = '".mysqli_real_escape_string($conn, $_GET['user'])."'");
		$sql = mysqli_fetch_array($sql, MYSQLI_ASSOC);
		if(!empty($sql['username'])) {
			$timeline = get_timeline('profile', null, $_GET['user']);
		}
	}

	if(!isset($timeline)) {
		exit;
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>
	<rss version="2.0">
	<channel>
	<title>'.strtoupper($_GET['user']).'\'S PROFILE :: STATUS.RYSLIG.XYZ</title>
	<link>http://status.ryslig.xyz/profile?user='.$_GET['user'].'</link>
	<description>This is the description.</description>
	<language>en-us</language>
	<ttl>30</ttl>';

	foreach($timeline['timeline'] as $status) {
		echo '<item>
		<title>'.$status['author']['name'].': '.$status['status_raw'].'</title>
		<description>'.$status['status_raw'].'</description>
		<pubDate>'.$status['date']['rss_timestamp'].'</pubDate>
		<link>http://status.ryslig.xyz'.$status['author']['link'].'</link>
		<guid isPermaLink="false">status_'.$status['id'].'</guid>
		</item>';
	}

	echo '</channel>
	</rss>';
} else {
	$timeline = get_timeline('public');
	
	echo '<?xml version="1.0" encoding="UTF-8"?>
	<rss version="2.0">
	<channel>
	<title>STATUS.RYSLIG.XYZ</title>
	<link>http://status.ryslig.xyz/</link>
	<description>This is the description.</description>
	<language>en-us</language>
	<ttl>30</ttl>';

	foreach($timeline['timeline'] as $status) {
		echo '<item>
		<title>'.$status['author']['name'].': '.$status['status_raw'].'</title>
		<description>'.$status['status_raw'].'</description>
		<pubDate>'.$status['date']['rss_timestamp'].'</pubDate>
		<link>http://status.ryslig.xyz'.$status['author']['link'].'</link>
		<guid isPermaLink="false">status_'.$status['id'].'</guid>
		</item>';
	}

	echo '</channel>
	</rss>';
}

?>