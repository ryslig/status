<h2>what are you doing? <small><span id="counter">200</span><?php
$updatecount = mysqli_fetch_assoc($GLOBALS['conn']->query("SELECT COUNT(id) AS count FROM updates WHERE author = '".$_SESSION['username']."' AND date > DATE_SUB(NOW(), INTERVAL 24 HOUR)"));
$olduser = mysqli_fetch_assoc($GLOBALS['conn']->query("SELECT COUNT(*) AS count FROM users WHERE username = '".$_SESSION['username']."' AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK)"));
if($olduser['count'] == 1) {
	echo ", ".(10 - intval($updatecount['count']))." updates left.";
}
?></small></h2>
<form method="post" action="/home">
	<textarea name="status" id="status" maxlength="200" oninput="count_it()" autocomplete="off" rows="3"></textarea>
	<br><br>
	<input type="submit" value="update">
</form>
<br><br>
<?php
$timeline = new Timeline;

switch($type) {
	case 'mentions':
		$header = "people who have mentioned you recently:";
		$timeline->type = "mentions";
		break;
	case 'public':
		$header = "what everyone is doing:";
		$timeline->type = "public";
		break;
	default:
		$header = "what your friends are doing:";
		if($theme['home'] == 1): $timeline->type = "timeline";
		else: $timeline->type = "currently";
		endif;
}
?>
<h2><?php echo $header; ?></h2>
<ul class="nav">
	<li><a href="/home">Home</a></li>
	<li><a href="/home/mentions">Mentions</a></li>
	<li class="last"><a href="/home/public">Public</a></li>
</ul>
<br>
<?php
$timeline->display();
?>