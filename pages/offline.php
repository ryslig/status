<h2>welcome to the website. <a href="/signup">sign up</a> or <a href="/signin">sign in</a>.</h2>
<p>or you can check out what's going on right now!</p>
<?php
$timeline = new Timeline;
$timeline->type = "public";
$timeline->limit = 10;
$timeline->paging = false;
$timeline->display(0);
?>