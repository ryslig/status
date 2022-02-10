<?php
ob_start();

$starttime = microtime(true);

include('config.php');
$GLOBALS['conn'] = $conn = new mysqli($config['db']['host'], $config['db']['user'], $config['db']['pass'], $config['db']['db']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

ini_set('session.gc_maxlifetime', 2628000);
session_set_cookie_params(2628000);
session_start();

if($config['debug'] == true) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
} else {
	error_reporting(0);
}

if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_SERVER['HTTP_REFERER']) && !strpos($_SERVER['HTTP_REFERER'], "status.ryslig.xyz")) {
	http_response_code(400);
	exit;
}

class Timeline {
	public $type;
	public $user;
	public $limit = 20;
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
				$sql = "SELECT * FROM `updates` WHERE `id` IN (SELECT MAX(`id`) FROM `updates` WHERE `author` IN (SELECT following FROM follows WHERE follower = '".$_SESSION['username']."') OR `author` = '".$_SESSION['username']."' GROUP BY `author`) ORDER BY CAST(id as SIGNED INTEGER) DESC";
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
				if($this->type == "timeline") {
					if(isset($status['reply']) && mysqli_fetch_array($GLOBALS['conn']->query("SELECT author FROM updates WHERE id = ".$status['reply']))['author'] == $_SESSION['username']) {
						echo ' class="mention"';
					} elseif(strpos($status['status'], $_SESSION['username'])) {
						echo ' class="mention"';
					}
				}
				echo '><td width="49" valign="top"><a href="/profile?user='.$status['author'].'">';
				if(file_exists("./images/profiles/".$status['author'].".gif")) {
					echo '<img src="/images/profiles/'.$status['author'].'.gif" width="45" height="45" class="thumb" alt="'.$status['author'].'">';
				} else {
					echo '<img src="/images/default.gif" width="45" height="45" class="thumb" alt="'.$status['author'].'">';
				}
				echo '</a></td><td><strong><a href="/profile?user='.$status['author'].'">'.$status['author'].'</a>:</strong> '.$this->place_links(htmlspecialchars($status['status'])).' <small>(<a href="/permalink?id='.$status['id'].'">'.$this->time_elapsed_string($status['date']).'</a>';
				if(isset($status['reply'])) {
					$replyauthor = mysqli_fetch_array($GLOBALS['conn']->query("SELECT author FROM updates WHERE id = ".$status['reply']));
					if($replyauthor) {
						echo ' <a href="/permalink?id='.$status['reply'].'">in reply to '.$replyauthor['author'].'</a>';
					}
				}
				echo ')</small> ';
				if(isset($_SESSION['username'])) {
					echo '<img src="/images/icon_reply.gif" alt="Reply" title="Reply" onclick="reply(\''.$status['id'].'\')" width="16" height="16">';
					if($_SESSION['username'] == $status['author']) {
						echo '<img src="/images/icon_delete.gif" alt="Delete" title="Delete" onclick="delete_status(\''.$status['id'].'\')" width="16" height="16">';
					}
				}
				echo '</td></tr>'."\r\n";
			}
			echo '</table>';
		} elseif($format == 1) {
			foreach($GLOBALS['conn']->query($sql) as $status) {
				echo '<p>'.$this->place_links(htmlspecialchars($status['status'])).' <small>(<a href="/permalink?id='.$status['id'].'">'.$this->time_elapsed_string($status['date']).'</a>';
				if(isset($status['reply'])) {
					$replyauthor = mysqli_fetch_array($GLOBALS['conn']->query("SELECT author FROM updates WHERE id = ".$status['reply']));
					if($replyauthor) {
						echo ' <a href="/permalink?id='.$status['reply'].'">in reply to '.$replyauthor['author'].'</a>';
					}
				}
				echo ')</small>';
				if(isset($_SESSION['username'])) {
					echo '<img src="/images/icon_reply.gif" alt="Reply" title="Reply" onclick="reply(\''.$status['id'].'\')" width="16" height="16">';
					if($_SESSION['username'] == $status['author']) {
						echo '<img src="/images/icon_delete.gif" alt="Delete" title="Delete" onclick="delete_status(\''.$status['id'].'\')" width="16" height="16">';
					}
				}
				echo '</p>'."\r\n";
			}
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
				"<description><![CDATA[".$this->place_links(htmlspecialchars($status['status']))."]]></description>".
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
					if($this->page !== 0) {
						echo '<a href="?page='. intval($this->page - 1) .'" rel="prev"><button>&larr; Newer</button></a>';
					}
					if($pages_amount > $this->page) {
						echo '<a href="?page='. intval($this->page + 1) .'" rel="next"><button style="float: right">Older &rarr;</button></a>';
					}
				} else {
					if($this->page !== 0) echo '<a href="?user='.$this->user.'&page='. intval($this->page - 1) .'" rel="prev"><button>&larr; Newer</button></a>';
					if($pages_amount > $this->page) echo '<a href="?user='.$this->user.'&page='. intval($this->page + 1) .'" rel="next"><button style="float: right">Older &rarr;</button></a>';
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
		$message = preg_replace("~(https?://(?:www\.)?[^\s]+)~i", '<a href="$1" target="_blank">$1</a>', $message);
		/*if(!empty($preg[0])) {
			foreach($preg[0] as $link) {
				if(strlen($link) > 50) {
					$visible_link = '<a href="'.$link.'" target="_blank">'.substr($link, 0, 45).'&hellip;</a>';
				} else {
					$visible_link = '<a href="'.$link.'" target="_blank">'.$link.'</a>';
				}
				$message = str_replace($link, $visible_link, $message);
			}
		}*/
		$message = preg_replace('/@(\w+)/', '<a href="/profile?user=$1">@$1</a>', $message);
		unset($preg);
		return $message;
	}
}	

switch(preg_replace("/\?(.*)/", "", $_SERVER['REQUEST_URI'])) {
	case '/':
		if(isset($_SESSION['username'])) {
			header('Location: /home');
		}
		$load = 'pages/offline.php';
		break;
	case '/signup':
		if(isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Sign Up';
		$load = 'pages/signup.php';
		break;
	case '/signin':
		if(isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Sign In';
		$load = 'pages/signin.php';
		break;
	case '/signout':
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$load = 'pages/signout.php';
		break;
	case '/home':
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Home';
		$load = 'pages/home.php';
		break;
	case '/home/mentions':
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Home';
		$type = 'mentions';
		$load = 'pages/home.php';
		break;
	case '/home/public':
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Home';
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
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Settings';
		$load = 'pages/settings_profile.php';
		break;
	case '/settings/picture';
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Settings';
		$load = 'pages/settings_picture.php';
		break;
	case '/settings/design';
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Settings';
		$load = 'pages/settings_design.php';
		break;
	case '/settings/password';
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Settings';
		$load = 'pages/settings_password.php';
		break;
	case '/settings/api';
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$title = 'Settings';
		$load = 'pages/settings_api.php';
		break;
	case '/ajax/delete';
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$load = 'pages/ajax_delete.php';
		$raw = true;
		break;
	case '/ajax/follow';
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$load = 'pages/ajax_follow.php';
		$raw = true;
		break;
	case '/ajax/unfollow';
		if(!isset($_SESSION['username'])) {
			http_response_code(403);
			header('Location: /');
		}
		$load = 'pages/ajax_unfollow.php';
		$raw = true;
		break;
	case '/admin/become';
		if($_SESSION['admin'] !== true) {
			http_response_code(403);
			header('Location: /');
		} else {
			$load = 'pages/admin_become.php';
		}
		$raw = true;
		break;
	case '/admin/ban';
		if($_SESSION['admin'] !== true) {
			http_response_code(403);
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
		$stmt = $conn->prepare("SELECT * FROM updates WHERE id = ?");
		$stmt->bind_param("i", $_GET['id']);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0) {
			$result = $result->fetch_assoc();
			$title = $result['author'].": ".$result['status'];
			$load = "pages/permalink.php";
			$partial = true;
			break;
		}
		$stmt->close();
	case '/profile';
		$stmt = $conn->prepare("SELECT fullname FROM users WHERE username = ? AND banned = 0;");
		$stmt->bind_param("s", $_GET['user']);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows == 1) {
			$result = $result->fetch_assoc();
			$title = $result['fullname'];
			$load = 'pages/profile.php';
			break;
		}
		$stmt->close();
	default:
		http_response_code(404);
		$title = 'Page Not Found';
		$load = 'pages/not_found.php';
		break;
}

if(isset($_SESSION['username'])) {
	$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND banned = 0;");
	$stmt->bind_param("s", $_SESSION['username']);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0) {
		http_response_code(403);
		require 'pages/signout.php';
		exit;
	}
	$stmt->close();
}

if(isset($_POST['status']) && isset($_SESSION['username'])) {
	$status = trim($_POST['status']);
	$updatecount = mysqli_fetch_assoc($GLOBALS['conn']->query("SELECT COUNT(id) AS count FROM updates WHERE author = '".$_SESSION['username']."' AND date > DATE_SUB(NOW(), INTERVAL 24 HOUR)"));
	$olduser = mysqli_fetch_assoc($GLOBALS['conn']->query("SELECT COUNT(*) AS count FROM users WHERE username = '".$_SESSION['username']."' AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK)"));
	if(strlen($status) > 2) {
		if(strlen($status) <= 200) {
			if(!isset($_SESSION['last_status']) || $_SESSION['last_status'] !== $status) {
				if(!isset($_SESSION['last_status_date']) || strtotime($_SESSION['last_status_date']) < strtotime("-30 seconds")) {
					if($olduser['count'] == 1 && $updatecount['count'] >= 10) {
						$_SESSION['alert'] = "You have ran out of updates for the day. Come back tomorrow!";
						exit;
					}
					$_SESSION['last_status'] = $status;
					$_SESSION['last_status_date'] = date(DATE_RFC822);
					$reply = intval($_POST['reply']);
					if(!empty($_POST['reply'])) {
						$stmt = $conn->prepare("INSERT INTO updates (author, status, reply) VALUES (?, ?, ?)");
						$stmt->bind_param("ssi", $_SESSION['username'], $status, $reply);
					} else {
						$stmt = $conn->prepare("INSERT INTO updates (author, status) VALUES (?, ?)");
						$stmt->bind_param("ss", $_SESSION['username'], $status);
					}
					$stmt->execute();
					$stmt->close();
				} else { $_SESSION['alert'] = "Please wait 30 seconds between updates!"; }
			} else { $_SESSION['alert'] = "Stop repeating yourself!"; }
		} else { $_SESSION['alert'] = "Your status is longer than 200 characters!"; }
	} else { $_SESSION['alert'] = "Your status must be longer than two characters!"; }
	if(empty($_POST['reply'])) header('Location: '.$_SERVER['REQUEST_URI']);
	exit;
}

if(isset($raw)) {
	require $load;
	exit;
}

if($load !== "pages/permalink.php") {
	if(isset($title)) {
		$title = 'Status - '.$title;
	} else {
		$title = 'Status';
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($title); ?></title>
	<link href="/style.css?5292021_6" rel="stylesheet" type="text/css">
	<script src="/app.js?07252021" type="text/javascript"></script>
	<?php
	if($load == "pages/profile.php") {
		if(file_exists("./images/profiles/".$_GET['user'].".gif")) {
			echo '<meta property="og:image" content="http://status.ryslig.xyz/images/profiles/'.$_GET['user'].'.gif">';
		}
		echo '<link rel="alternate" type="application/rss+xml" title="'.$title.'" href="http://status.ryslig.xyz/rss?user='.$_GET['user'].'">';
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
			<td colspan="3"><h1><a href="/">status.ryslig.xyz</a></h1></td>
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
					if($load == 'pages/profile.php' && isset($_SESSION['admin']) && $_SESSION['admin'] == true && $_SESSION['username'] !== $_GET['user']) {
						echo '<h2>admin tools:</h2>'.
						'<ul><li><a href="/admin/become?user='.$_GET['user'].'">Become User</a></li>'.
						'<li><a href="/admin/ban?user='.$_GET['user'].'">Ban Account</a></li></ul>'.
						'<br>';
					}
				?>
				<h2>latest users:</h2>
				<ul><?php
					$sql = "SELECT username, fullname FROM users WHERE banned = 0 ORDER BY `date` DESC LIMIT 6";
					$result = $conn->query($sql);
					while($row = $result->fetch_assoc()) {
						echo '<li><a href="/profile?user='.$row['username'].'">'.htmlspecialchars($row['fullname']).'</a></li>';
					}
				?></ul>
				<?php
				if(date("nj") == 1031) { // halloween bruh!
					echo '<br><br><img src="/images/jack-o-lantern.gif" width="105" height="104" border="0" alt="Jack-o-lantern" />';
				}
				?>
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
						<li><a href="/settings/password">Change Password</a></li>
						<li class="last"><a href="/settings/api">API Token</a></li>
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