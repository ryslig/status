<?php
ob_start();
include('config.php');
session_start();

$GLOBALS['conn'] = $conn = new mysqli($config['db']['host'], $config['db']['user'], $config['db']['pass'], $config['db']['db']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if(isset($_GET['test'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
} else {
	error_reporting(0);
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array('y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second',);
	
    foreach ($string as $k => &$v) {
        if ($diff->$k) : $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        else : unset($string[$k]);
        endif;
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function place_links($message) {
	$message = preg_replace("~(https?://(?:www\.)?[^\s]+)~i", '<a href="$1" target="_blank">$1</a>', $message);
	$message = preg_replace('/@(\w+)/', '<a href="/profile?user=$1" target="_top">@$1</a>', $message);
	return $message;
}

function get_timeline($type, $page = 0, $user = false) {
	$page = intval($page);
	if(isset($_SESSION['username'])) {
		$sql = "SELECT following FROM follows WHERE follower = '".$_SESSION['username']."'";
		$following = array();
		if(mysqli_num_rows(mysqli_query($GLOBALS['conn'], $sql)) !== 0) {
			$result = $GLOBALS['conn']->query($sql);
			while($row = $result->fetch_assoc()) array_push($following, $row['following']);
		}
		array_push($following, $_SESSION['username']);
	}
	switch($type) {
		case 'timeline':
			$sql = "SELECT * FROM `updates` WHERE `author` IN ('".implode("','", $following)."') ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$page*'25'.",25";
			$count = "SELECT COUNT(*) FROM `updates` WHERE `author` IN ('".implode("','", $following)."')";
			break;
		case 'currently':
			$sql = "SELECT * FROM `updates` WHERE `id` IN (SELECT MAX(`id`) FROM `updates` GROUP BY `author`) AND date > DATE_SUB(NOW(), INTERVAL 7 DAY) AND `author` IN ('".implode("','", $following)."') ORDER BY CAST(id as SIGNED INTEGER) DESC";
			break;
		case 'offline':
			$sql = "SELECT * FROM `updates` ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT 10";
			break;
		case 'profile':
			$sql = "SELECT * FROM `updates` WHERE `author` = '".$user."' ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$page*'25'.",25";
			$count = "SELECT COUNT(*) FROM `updates` WHERE `author` = '".$user."'";
			break;
		case 'public':
			$sql = "SELECT * FROM `updates` ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$page*'25'.",25";
			$count = "SELECT COUNT(*) FROM `updates`";
			break;
		case 'mentions':
			$sql = "SELECT * FROM `updates` WHERE status LIKE '%@".$_SESSION['username']."%' ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT 25";
			$count = "SELECT * FROM `updates` WHERE status LIKE '%@".$_SESSION['username']."%'";
			break;
	}
	$result = $GLOBALS['conn']->query($sql);
	while($row = $result->fetch_assoc()) {
		$id = intval($row['id']);
		$timeline['timeline'][$id]['id'] = intval($row['id']);
		$timeline['timeline'][$id]['author']['name'] = $row['author'];
		$timeline['timeline'][$id]['author']['link'] = '/profile?user='.$row['author'];
		$timeline['timeline'][$id]['author']['thumb'] = '/images/profiles/'.$row['author'].'.gif';
		$timeline['timeline'][$id]['status'] = place_links(htmlspecialchars($row['status']));
		$timeline['timeline'][$id]['status_raw'] = htmlspecialchars($row['status']);
		$timeline['timeline'][$id]['date']['timeago'] = time_elapsed_string($row['date']);
		$timeline['timeline'][$id]['date']['timestamp'] = date("c", strtotime($row['date']));
		$timeline['timeline'][$id]['date']['rss_timestamp'] = date(DATE_RFC822, strtotime($row['date']));
		if(isset($_SESSION['username'])) {
			if($_SESSION['username'] == $row['author']) {
				$timeline['timeline'][$id]['actions']['can_delete'] = true;
			} else {
				$timeline['timeline'][$id]['actions']['can_delete'] = false;
			}
		} else {
			$timeline['timeline'][$id]['actions']['can_delete'] = false;
		}
	}
	if(isset($count)) {
		$count = mysqli_fetch_row(mysqli_query($GLOBALS['conn'], $count));
		$count = $count[0];
		if($count > 25) {
			$pages_amount = floor($count/25);
			if($pages_amount <= $page) {
				$pagination['older'] = false;
			} else {
				$pagination['older'] = true;
			}
			if($page !== 0) {
				$pagination['newer'] = true;
			} else {
				$pagination['newer'] = false;
			}
		}
		$timeline['pagination'] = $pagination;
	}
	if(isset($timeline['timeline'])) {
		return $timeline;
	}
}

$request = preg_replace("/\?(.*)/", "", $_SERVER['REQUEST_URI']);

switch($request) {
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
		$title = 'Home';
		$type = 'mentions';
		$load = 'pages/home.php';
		break;
	case '/home/public':
		if(!isset($_SESSION['username'])) header('Location: /');
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
	case '/rss';
		header('Content-Type: text/xml');
		$load = 'pages/rss.php';
		$raw = true;
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
	case '/sitemap.xml';
		header('Content-Type: application/xml');
		$load = 'pages/sitemap.php';
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
	case '/profile';
		if(!preg_match("/[^0-9a-zA-Z\s]/", $_GET['user'])) {
			$sql = mysqli_query($conn, "SELECT `username` FROM `users` WHERE `username` = '".mysqli_real_escape_string($conn, $_GET['user'])."'");
			$sql = mysqli_fetch_array($sql, MYSQLI_ASSOC);
			if(!empty($sql['username'])) {
				$title = $sql['username'];
				$load = 'pages/profile.php';
				break;
			}
		}
	default:
		http_response_code(404);
		$title = 'Page Not Found';
		$load = 'pages/not_found.php';
		break;
}

if(isset($_SESSION['username'])) {
	if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '".$_SESSION['username']."';")) == 0) {
		require 'pages/signout.php';
		exit;
	}
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
	<link href="/style.css" rel="stylesheet" type="text/css">
	<link rel="icon" href="/images/quill.gif" type="image/gif">
	<link rel="shortcut icon" href="/images/quill.gif" type="image/gif">
	<?php
	if(isset($_SESSION['username'])) {
		echo '<script src="/app.js"></script>';
	}
	if($load == "pages/profile.php") {
		echo '<meta property="og:type" content="website">
		<meta property="og:site_name" content="status.ryslig.xyz">
		<meta property="og:title" content="'.ucfirst($title)."'s Profile".'">
		<meta property="og:image" content="http://status.ryslig.xyz/images/profiles/'.$title.'.gif">
		<link rel="alternate" type="application/rss+xml" title="'.strtoupper($title).' :: STATUS.RYSLIG.XYZ" href="http://status.ryslig.xyz/rss?user='.$title.'">';
	}
	if(isset($_SESSION['username']) or $load == 'pages/profile.php') {
		if($load !== 'pages/profile.php') {
			$theme = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '".$_SESSION['username']."'"), MYSQLI_ASSOC);
		} else {
			$theme = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '".$_GET['user']."'"), MYSQLI_ASSOC);
		}
		echo '<style>
		body, textarea {
			background-color: '.$theme['bg_color'].';
			color: '.$theme['text_color'].';
		}

		a {
			color: '.$theme['link_color'].';
		}

		.alert {
			border: 1px solid '.$theme['link_color'].';
		}

		label, q, small {
			color: '.$theme['meta_color'].';
		}

		img.thumb, input[type=text], input[type=password], input[type=email], textarea, select {
			border: 1px solid '.$theme['border_color'].';
		}
		</style>';
	}
	
	?>
	<!--[if IE]><style>body { word-break: break-all; }</style><![endif]-->
</head>
<body>
	<table width="700" align="center">
		<tr>
			<td colspan="2">
				<h1><a href="/">status.ryslig.xyz</a></h1>
			</td>
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
				<br><br>
				<h2>latest users:</h2>
				<ul>
				<?php
					$sql = "SELECT username, fullname FROM `users` ORDER BY `date` DESC LIMIT 6";
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
</body>
</html>
<?php
ob_get_flush();
?>