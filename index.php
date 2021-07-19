<?php
ob_start();

$starttime = microtime(true);

include('config.php');
$GLOBALS['conn'] = $conn = new mysqli($config['db']['host'], $config['db']['user'], $config['db']['pass'], $config['db']['db']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
session_start();

if($config['debug'] == true) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
} else {
	error_reporting(0);
}

class Timeline {
	public $type;
	public $user;
	public $limit = 25;
	public $paging = true;
	public function display($format = 0) {
		if(isset($_GET['page'])) {
			$this->page = intval($_GET['page']);
		} else {
			$this->page = 0;
		}	
		switch($this->type) {
			case 'timeline':
				$sql = "SELECT * FROM `updates` WHERE `author` IN (SELECT following FROM follows WHERE follower = '".$_SESSION['username']."') OR `author` = '".$_SESSION['username']."' OR `reply` IN (SELECT id FROM updates WHERE author = '".$_SESSION['username']."') OR `status` LIKE '%@".$_SESSION['username']."%' ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$this->page*$this->limit.",".$this->limit;
				$count = "SELECT COUNT(*) FROM `updates` WHERE `author` IN (SELECT following FROM follows WHERE follower = '".$_SESSION['username']."') OR `author` = '".$_SESSION['username']."' OR `reply` IN (SELECT id FROM updates WHERE author = '".$_SESSION['username']."') OR `status` LIKE '%@".$_SESSION['username']."%'";
				break;
			case 'currently':
				$sql = "SELECT * FROM `updates` WHERE `id` IN (SELECT MAX(`id`) FROM `updates` WHERE `author` IN (SELECT following FROM follows WHERE follower = '".$_SESSION['username']."') OR `author` = '".$_SESSION['username']."' GROUP BY `author`) AND `date` > DATE_SUB(NOW(), INTERVAL 1 WEEK) ORDER BY CAST(id as SIGNED INTEGER) DESC";
				break;
			case 'mentions':
				$sql = "SELECT * FROM `updates` WHERE `status` LIKE '%@".$_SESSION['username']."%' OR `reply` IN (SELECT id FROM updates WHERE author = '".$_SESSION['username']."') ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$this->page*$this->limit.",".$this->limit;
				$count = "SELECT COUNT(*) FROM `updates` WHERE `status` LIKE '%@".$_SESSION['username']."%' OR `reply` IN (SELECT id FROM updates WHERE author = '".$_SESSION['username']."')";
				break;
			case 'public':
				$sql = "SELECT * FROM `updates` ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$this->page*$this->limit.",".$this->limit;
				$count = "SELECT COUNT(*) FROM `updates`";
				break;
			case 'profile':
				$sql = "SELECT * FROM `updates` WHERE `author` = '".$this->user."' ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$this->page*$this->limit.",".$this->limit;
				$count = "SELECT COUNT(*) FROM `updates` WHERE `author` = '".$this->user."'";
				break;
			case 'permalink':
				$sql = "SELECT * FROM `updates` WHERE `id` = '".intval($_GET['id'])."' LIMIT 1";
				break;
		}
		if($format == 0) {
			echo '<table cellpadding="5" cellspacing="0" width="100%" class="timeline">';
			foreach($GLOBALS['conn']->query($sql) as $status) {
				echo '<tr';
				if(isset($status['reply']) && $this->type == "timeline" && mysqli_fetch_array($GLOBALS['conn']->query("SELECT author FROM updates WHERE id = ".$status['reply']))['author'] == $_SESSION['username']) echo ' class="mention"'; 
				echo '><td width="49"><a href="/profile?user='.$status['author'].'"><img src="/images/profiles/'.$status['author'].'.gif" width="45" height="45" class="thumb" alt="'.$status['author'].'"></a></td><td><strong><a href="/profile?user='.$status['author'].'">'.$status['author'].'</a>:</strong> '.$this->place_links(htmlspecialchars($status['status'])).' <small>(<a href="/permalink?id='.$status['id'].'">'.$this->time_elapsed_string($status['date']).'</a>';
				if(isset($status['reply'])) echo ' <a href="/permalink?id='.$status['reply'].'">in reply to '.mysqli_fetch_array($GLOBALS['conn']->query("SELECT author FROM updates WHERE id = ".$status['reply']))['author'].'</a>';
				echo ')</small> ';
				if(isset($_SESSION['username'])) {
					echo '<img src="/images/icon_reply.gif" alt="Reply" title="Reply" onclick="reply(\''.$status['id'].'\')" width="16" height="16">';
					if($_SESSION['username'] == $status['author']) {
						echo '<img src="/images/icon_delete.gif" alt="Delete" title="Delete" onclick="delete_status(\''.$status['id'].'\')" width="16" height="16">';
					}
				}
				echo '</td></tr>';
			}
			echo '</table>';
		} elseif($format == 1) {
			foreach($GLOBALS['conn']->query($sql) as $status) {
				echo '<p>'.$this->place_links(htmlspecialchars($status['status'])).' <small>(<a href="/permalink?id='.$status['id'].'">'.$this->time_elapsed_string($status['date']).'</a>';
				if(isset($status['reply'])) echo ' <a href="/permalink?id='.$status['reply'].'">in reply to '.mysqli_fetch_array($GLOBALS['conn']->query("SELECT author FROM updates WHERE id = ".$status['reply']))['author'].'</a>';
				echo ')</small>';
				if(isset($_SESSION['username'])) {
					echo '<img src="/images/icon_reply.gif" alt="Reply" title="Reply" onclick="reply(\''.$status['id'].'\')" width="16" height="16">';
					if($_SESSION['username'] == $status['author']) {
						echo '<img src="/images/icon_delete.gif" alt="Delete" title="Delete" onclick="delete_status(\''.$status['id'].'\')" width="16" height="16">';
					}
				}
				'</p>';
			}
			echo "<br>";
		} elseif($format == 2) {
			foreach($GLOBALS['conn']->query($sql) as $status) {
				echo '<p><strong><a href="/profile?user='.$status['author'].'" target="_blank">'.$status['author'].'</a>:</strong> '.$this->place_links(htmlspecialchars($status['status'])).' <small>(<a href="/permalink?id='.$status['id'].'" target="_blank">'.$this->time_elapsed_string($status['date']).'</a>';
				if(isset($status['reply'])) echo ' <a href="/permalink?id='.$status['reply'].'">in reply to '.mysqli_fetch_array($GLOBALS['conn']->query("SELECT author FROM updates WHERE id = ".$status['reply']))['author'].'</a>';
				echo ')</small>';
				if(isset($_SESSION['username'])) {
					echo ' <img src="/images/icon_reply_small.gif" alt="Reply" title="Reply" onclick="reply(\''.$status['id'].'\')" width="10" height="10">';
				}
				echo '</p><hr>';
			}
		} elseif($format == 4) {
			foreach($GLOBALS['conn']->query($sql) as $status) {
				echo "<item>".
				"<title>".$status['author'].": ".htmlspecialchars($status['status'])."</title>".
				"<description>".htmlspecialchars($status['status'])."</description>".
				"<pubDate>".date(DATE_RFC822, strtotime($status['date']))."</pubDate>".
				"<link>//status.ryslig.xyz/permalink?id=".$status['id']."</link>".
				"<author><name>".$status['author']."</name></author>".
				"<guid>//status.ryslig.xyz/permalink?id=".$status['id']."</guid>".
				"</item>";
			}
		}
		if(isset($count) && $this->paging !== false) {
			echo "<br>";
			$count = mysqli_fetch_row(mysqli_query($GLOBALS['conn'], $count));
			$count = $count[0];
			if($count > $this->limit) {
				$pages_amount = floor($count/$this->limit);
				if(!isset($this->user)) {
					if($this->page !== 0) echo '<a href="?page='. $this->page-1 .'" rel="prev"><button>&larr; Newer</button></a>';
					if($pages_amount > $this->page) echo '<a href="?page='. $this->page+1 .'" rel="next"><button style="float: right">Older &rarr;</button></a>';
				} else {
					if($this->page !== 0) echo '<a href="?user='.$this->user.'&page='. $this->page-1 .'" rel="prev"><button>&larr; Newer</button></a>';
					if($pages_amount > $this->page) echo '<a href="?user='.$this->user.'&page='. $this->page+1 .'" rel="next"><button style="float: right">Older &rarr;</button></a>';
				}
			}
		}
	}
	private function time_elapsed_string($datetime, $full = false) {
		if(strtotime($datetime) < strtotime("-1 day")) {
			return date("M jS g:i a", strtotime($datetime));
		} else {
			$now = new DateTime;
			$ago = new DateTime($datetime);
			$diff = $now->diff($ago);

			$diff->w = floor($diff->d / 7);
			$diff->d -= $diff->w * 7;

			$string = array('y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second',);
			
			foreach ($string as $k => &$v) {
				if ($diff->$k) {
					$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
				} else {
					unset($string[$k]);
				}
			}

			if (!$full) $string = array_slice($string, 0, 1);
			return $string ? implode(', ', $string) . ' ago' : 'just now';
		}
	}
	private function place_links($message) {
		preg_match_all("~(https?://(?:www\.)?[^\s]+)~i", $message, $preg);
		if(!empty($preg[0])) {
			foreach($preg[0] as $link) {
				if(strlen($link) > 50) {
					$visible_link = '<a href="'.$link.'" target="_blank">'.substr($link, 0, 45).'&hellip;</a>';
				} else {
					$visible_link = '<a href="'.$link.'" target="_blank">'.$link.'</a>';
				}
				$message = str_replace($link, $visible_link, $message);
			}
		}
		$message = preg_replace('/@(\w+)/', '<a href="/profile?user=$1">@$1</a>', $message);
		unset($preg);
		return $message;
	}
}	

switch(preg_replace("/\?(.*)/", "", $_SERVER['REQUEST_URI'])) {
	case '/':
		if(isset($_SESSION['username'])) header('Location: /home');
		$load = 'pages/offline.php';
		break;
	case '/signup':
		if(isset($_SESSION['username'])) header('Location: /home');
		$title = 'Sign Up';
		$load = 'pages/signup.php';
		break;
	case '/signin':
		if(isset($_SESSION['username'])) header('Location: /home');
		$title = 'Sign In';
		$load = 'pages/signin.php';
		break;
	case '/signout':
		if(!isset($_SESSION['username'])) header('Location: /');
		$load = 'pages/signout.php';
		break;
	case '/home':
		if(!isset($_SESSION['username'])) header('Location: /');
		$title = 'Home';
		$load = 'pages/home.php';
		break;
	case '/home/mentions':
		if(!isset($_SESSION['username'])) header('Location: /');
		$title = 'Mentions';
		$type = 'mentions';
		$load = 'pages/home.php';
		break;
	case '/home/public':
		if(!isset($_SESSION['username'])) header('Location: /');
		$title = 'Public';
		$type = 'public';
		$load = 'pages/home.php';
		break;
	case '/settings';
		if(!isset($_SESSION['username'])) {
			header('Location: /');
		} else {
			header('Location: /settings/profile');
		}
		break;
	case '/settings/profile';
		if(!isset($_SESSION['username'])) header('Location: /');
		$title = 'Settings';
		$load = 'pages/settings_profile.php';
		break;
	case '/settings/picture';
		if(!isset($_SESSION['username'])) header('Location: /');
		$title = 'Settings';
		$load = 'pages/settings_picture.php';
		break;
	case '/settings/design';
		if(!isset($_SESSION['username'])) header('Location: /');
		$title = 'Settings';
		$load = 'pages/settings_design.php';
		break;
	case '/settings/password';
		if(!isset($_SESSION['username'])) header('Location: /');
		$title = 'Settings';
		$load = 'pages/settings_password.php';
		break;
	case '/ajax/delete';
		if(!isset($_SESSION['username'])) header('Location: /');
		$load = 'pages/ajax_delete.php';
		$raw = true;
		break;
	case '/ajax/follow';
		if(!isset($_SESSION['username'])) header('Location: /');
		$load = 'pages/ajax_follow.php';
		$raw = true;
		break;
	case '/ajax/unfollow';
		if(!isset($_SESSION['username'])) header('Location: /');
		$load = 'pages/ajax_unfollow.php';
		$raw = true;
		break;
	case '/admin/become';
		if($_SESSION['admin'] !== true) {
			header('Location: /');
		} else {
			$load = 'pages/admin_become.php';
		}
		$raw = true;
		break;
	case '/admin/ban';
		if($_SESSION['admin'] !== true) {
			header('Location: /');
		} else {
			$load = 'pages/admin_ban.php';
		}
		$raw = true;
		break;
	case '/sitemap.xml';
		header('Content-Type: application/xml');
		$load = 'pages/sitemap.php';
		$raw = true;
		break;
	case '/rss';
		header('Content-Type: text/xml');
		$load = 'pages/rss.php';
		$raw = true;
		break;
	case '/widget';
		if(!isset($_SESSION['username'])) {
			$load = 'pages/widget-login.php';
		} else {
			$load = 'pages/widget.php';
		}
		$raw = true;
		break;
	case '/permalink';
		$title = "Permalink";
		$load = "pages/permalink.php";
		$partial = true;
		break;
	case '/profile';
		if(!preg_match("/[^0-9a-zA-Z\s]/", $_GET['user'])) {
			$sql = mysqli_fetch_array(mysqli_query($conn, "SELECT `username`, `banned` FROM `users` WHERE `username` = '".mysqli_real_escape_string($conn, $_GET['user'])."'"), MYSQLI_ASSOC);
			if(!empty($sql['username'])) {
				if($sql['banned'] !== true) {
					$title = $sql['username']."'s Profile";
					$load = 'pages/profile.php';
					break;
				}
			}
		}
	default:
		http_response_code(404);
		$title = 'Page Not Found';
		$load = 'pages/not_found.php';
		break;
}

if(isset($_SESSION['username'])) {
	if(mysqli_num_rows(mysqli_query($conn, "SELECT `username` FROM `users` WHERE `username` = '".$_SESSION['username']."' and `banned` = 0")) == 0) {
		require 'pages/signout.php';
		exit;
	}
}

if(isset($_POST['status'])) {
	if(isset($_SESSION['username'])) {
		$status = trim($_POST['status']);
		if(!empty($status)) {
			if(strlen($status) > 2) {
				if($_SESSION['last_status'] !== $status) {
					$query = mysqli_query($GLOBALS['conn'], "SELECT `id` FROM `updates` WHERE `date` > DATE_SUB(NOW(), INTERVAL 30 SECOND) AND `author` = '".$_SESSION['username']."';");
					$rows = mysqli_num_rows($query);
					if($rows == 0) {
						$_SESSION['last_status'] = $status;
						if(!empty($_POST['reply'])) {
							mysqli_query($conn, "INSERT INTO updates (author, status, reply) VALUES ('".$_SESSION['username']."', '".mysqli_real_escape_string($conn, $status)."', '".intval($_POST['reply'])."')");
						} else {
							mysqli_query($conn, "INSERT INTO updates (author, status) VALUES ('".$_SESSION['username']."', '".mysqli_real_escape_string($conn, $status)."')");
						}
					} else { $_SESSION['alert'] = "Please wait 30 seconds before updating your status!"; }
				} else { $_SESSION['alert'] = "Stop repeating yourself!";}
			} else { $_SESSION['alert'] = "Your status must be longer than two characters!"; }
		} else { $_SESSION['alert'] = "We need something here."; }
	} else { $_SESSION['alert'] = "We need something here."; }
	header('Location: '.$_SERVER['REQUEST_URI']);
	exit;
}

if(isset($raw)) {
	require $load;
	exit;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php
	if(isset($title)) {
		echo strtoupper($title).' :: STATUS.RYSLIG.XYZ';
	} else {
		echo 'STATUS.RYSLIG.XYZ';
	}
	?></title>
	<link href="/style.css?5292021_6" rel="stylesheet" type="text/css">
	<script src="/app.js?07142021_2" type="text/javascript"></script>
	<?php
	if($load == "pages/profile.php") {
		echo '<meta property="og:type" content="website">'.
		'<meta property="og:site_name" content="status.ryslig.xyz">'.
		'<meta property="og:title" content="'.ucfirst($title).'">'.
		'<meta property="og:image" content="http://status.ryslig.xyz/images/profiles/'.$_GET['user'].'.gif">'.
		'<link rel="alternate" type="application/rss+xml" title="'.strtoupper($title).' :: STATUS.RYSLIG.XYZ" href="http://status.ryslig.xyz/rss?user='.$_GET['user'].'">';
	}
	if(isset($_SESSION['username']) or $load == 'pages/profile.php' or $load == 'pages/permalink.php') {
		if($load == 'pages/profile.php') {
			$theme = mysqli_fetch_array(mysqli_query($conn, "SELECT bg_color, text_color, meta_color, border_color, link_color, highlight_color, home FROM `users` WHERE `username` = '".$_GET['user']."'"), MYSQLI_ASSOC);
		} elseif($load == 'pages/permalink.php') {
			$theme = mysqli_fetch_array(mysqli_query($conn, "SELECT bg_color, text_color, meta_color, border_color, link_color, highlight_color, home FROM `users` WHERE `username` IN(SELECT author FROM updates WHERE id = ".intval($_GET['id']).")"));
		} else {
			$theme = mysqli_fetch_array(mysqli_query($conn, "SELECT bg_color, text_color, meta_color, border_color, link_color, highlight_color, home FROM `users` WHERE `username` = '".$_SESSION['username']."'"), MYSQLI_ASSOC);
		}
		echo '<style type="text/css">'.
		'body, textarea {background-color: '.$theme['bg_color'].';color: '.$theme['text_color'].';}'.
		'a {color: '.$theme['link_color'].';}'.
		'a, small a:hover {color: '.$theme['link_color'].';}'.
		'.alert {border: 1px solid '.$theme['link_color'].';}'.
		'label, q, small, small a {color: '.$theme['meta_color'].';}'.
		'img.thumb, input[type=text], input[type=password], input[type=email], textarea, select {border: 1px solid '.$theme['border_color'].';}'.
		'tr.mention {background-color: '.$theme['highlight_color'].';}'.
		'</style>';
	}
	?>
	<!--[if IE]><style type="text/css">body { word-break: break-all; }</style><![endif]-->
</head>
<body>
	<?php if(!isset($partial)) { ?>
	<table align="center" width="700">
		<tr>
			<td colspan="2"><h1><a href="/">status.ryslig.xyz</a></h1></td>
		</tr>
		<tr>
			<td width="130" valign="top" id="sidebar">
				<?php
					if(isset($_SESSION['username'])){ 
						require 'pages/sidebar_online.php';
					} else {
						require 'pages/sidebar_offline.php';
					}
				?>
				<br>
				<?php
					if($load == 'pages/profile.php' && $_SESSION['admin'] == true && $_SESSION['username'] !== $_GET['user']) {
						echo '<h2>admin tools:</h2>
						<ul>
							<li><a href="/admin/become?user='.$_GET['user'].'">Become User</a></li>
							<li><a href="/admin/ban?user='.$_GET['user'].'">Ban Account</a></li>
						</ul>
						<br>';
					}
				?>
				<h2>latest users:</h2>
				<ul>
				<?php
					$sql = "SELECT `username`, `fullname` FROM users WHERE banned != 1 ORDER BY `date` DESC LIMIT 6";
					$result = $conn->query($sql);
					while($row = $result->fetch_assoc()) {
						echo '<li><a href="/profile?user='.$row['username'].'">'.htmlspecialchars($row['fullname']).'</a></li>';
					}
				?>
				</ul> 
			</td>
			<td valign="top" id="content">
			<?php
				if(isset($_SESSION['alert']) and $_SESSION['alert'] !== true) {
					echo '<div class="alert">'.$_SESSION['alert'].'</div>';
					unset($_SESSION['alert']);
				}
				if(substr_count($load, "settings") == 1) {
					echo '<h2>Settings</h2>
					<ul class="nav">
						<li><a href="/settings/profile">Profile Info</a></li>
						<li><a href="/settings/picture">Change Picture</a></li>
						<li><a href="/settings/design">Edit Design</a></li>
						<li class="last"><a href="/settings/password">Change Password</a></li>
					</ul>
					<br><br>';
				}
				require $load;
			?>
			</td>
		</tr>
	</table>
	<?php } else { require $load; } ?>
	<?php
	if(strpos($_SERVER['HTTP_USER_AGENT'], "RetroZilla") or strpos($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
		echo '<form method="post" action="/home" id="form_legacy">
		<input type="hidden" name="status" id="status_legacy">
		<input type="hidden" name="reply" id="id_legacy">
		</form>';
	}
	?>
	<!-- <?php $endtime = microtime(true); printf("Page loaded in %f seconds", $endtime - $starttime); ?> -->
</body>
</html>
<?php
ob_get_flush();
?>
