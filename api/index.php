<?php
require __DIR__ . '/../config.php';
$GLOBALS['conn'] = $conn = new mysqli($config['db']['host'], $config['db']['user'], $config['db']['pass'], $config['db']['db']);
$conn->set_charset("utf8mb4");

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

function alert($txt) {
	http_response_code(400);
	$output['error'] = $txt;
	echo json_encode($output);
	die();
}

function getUserFromToken($token) {
	$stmt = $GLOBALS['conn']->prepare("SELECT username FROM users WHERE token = ? AND banned = 0");
	$stmt->bind_param("s", $token);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows == 0) {
		alert("The token you supplied is invalid!");
	} else {
		$user = $result->fetch_assoc();
		return $user['username'];
	}
}

function generateToken() {
	$rand_token = openssl_random_pseudo_bytes(8);
	return bin2hex($rand_token);
}

error_reporting(0);

$router = new \Bramus\Router\Router();

$router->set404(function() {
    http_response_code(501);
	die();
});

$router->get('/', function() {
	header('Content-Type: text/html');
	require __DIR__ . "/doc.html";
});

$router->get('/timeline', function() {
	if(isset($_GET['user'])) {
		$user = $_GET['user'];
	}
	if(isset($_GET['id'])) {
		$id = intval($_GET['id']);
	}
	if(isset($_GET['page'])) {
		$page = intval($_GET['page'])-1;
	} else {
		$page = 0;
	}
	if(isset($_GET['limit'])) {
		$limit = intval($_GET['limit']);
		if($limit > 100) alert("Limit parameter must not exceed 100!");
	} else {
		$limit = 25;
	}
	switch($_GET['type']) {
		case 'profile';
			if(!isset($user)) alert('This type of timeline requires the "user" parameter!');
			$sql = "SELECT * FROM updates
			WHERE author = '".$user."'
			ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$page*$limit.",".$limit;
			$count = "SELECT COUNT(*) AS count FROM updates WHERE author = '".$user."'";
			break;
		case 'mentions';
			if(!isset($user)) alert('This type of timeline requires the "user" parameter!');
			$sql = "SELECT * FROM updates WHERE status
			LIKE '%@".$user."%' OR reply IN (SELECT id FROM updates WHERE author = '".$user."')
			ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$page*$limit.",".$limit;
			$count = "SELECT COUNT(*) AS count FROM updates WHERE status LIKE '%@".$user."%' OR reply IN (SELECT id FROM updates WHERE author = '".$user."')";
			break;
		case 'following';
			if(!isset($user)) alert('This type of timeline requires the "user" parameter!');
			$sql = "SELECT * FROM updates WHERE author
			IN (SELECT following FROM follows WHERE follower = '".$user."') OR author = '".$user."'
			OR reply IN (SELECT id FROM updates WHERE author = '".$user."') OR status LIKE '%@".$user."%'
			ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$page*$limit.",".$limit;
			$count = "SELECT COUNT(*) AS count FROM updates WHERE author IN (SELECT following FROM follows WHERE follower = '".$user."') OR author = '".$user."' OR reply IN (SELECT id FROM updates WHERE author = '".$user."') OR status LIKE '%@".$user."%'";
			break;
		case 'specific';
			if(!isset($id)) alert('This type of timeline requires the "id" parameter!');
			$sql = "SELECT * FROM updates WHERE id = ".$id;
			break;
		default:
			$sql = "SELECT * FROM updates ORDER BY CAST(id as SIGNED INTEGER) DESC LIMIT ".$page*$limit.",".$limit;
			$count = "SELECT COUNT(*) AS count FROM updates";
			break;	
	}
	$result = $GLOBALS['conn']->query($sql);
	if ($result->num_rows > 0) {
		while ($status = $result->fetch_array()) {
			$id = $status['id'];
			$timeline[$id]['author'] = $status['author'];
			$timeline[$id]['status'] = $status['status'];
			$timeline[$id]['date'] = date("r", strtotime($status['date']));
			if(!empty($status['reply'])) {
				$replycheck = $GLOBALS['conn']->query("SELECT author FROM updates WHERE id = ".$status['reply']);
				if($replycheck->num_rows == 1) {
					$reply = $replycheck->fetch_row();
					$timeline[$id]['reply'] = $status['reply'];
					//$timeline[$id]['reply']['author'] = $reply[0];
				}
			}
		}
	} else {
		alert("No results found!");
	}
	$output['timeline'] = $timeline;
	if(isset($count)) {
		$result = $GLOBALS['conn']->query($count);
		$pages = $result->fetch_assoc();
		$output['current_page'] = $page+1;
		$output['available_pages'] = ceil($pages['count']/$limit);
	}
	echo json_encode($output);
});

$router->get('/profile', function() {
	if(isset($_GET['user'])) {
		$user = $_GET['user'];
	} elseif(isset($_GET['token'])) {
		$user = getUserFromToken($_GET['token']);
	} else {
		alert('This function requires the "user" parameter!');
	}
	$stmt = $GLOBALS['conn']->prepare("SELECT * FROM users WHERE username = ? AND banned = 0");
	$stmt->bind_param("s", $user);
	$stmt->execute();
	$result = $stmt->get_result();
	if($result->num_rows == 0) {
		alert("That user does not exist or has been banned!");
	}
	$info = $result->fetch_assoc();
	$output['username'] = $info['username'];
	$output['fullname'] = $info['fullname'];
	$output['registered'] = date("r", strtotime($info['date']));
	if(!empty($info['website'])) {
		$output['website'] = $info['website'];
	}
	if(!empty($info['quote'])) {
		$output['quote'] = $info['quote'];
	}
	$output['theme'] = array(
		"bg_color" => $info['bg_color'],
		"text_color" => $info['text_color'],
		"meta_color" => $info['meta_color'],
		"border_color" => $info['border_color'],
		"link_color" => $info['link_color'],
		"highlight_color" => $info['highlight_color']
	);
	echo json_encode($output);
});

$router->post('/update', function() {
	if(isset($_POST['token'])) {
		if(isset($_POST['status'])) {
			$status = trim($_POST['status']);
			if(strlen($status) > 2) {
				if(strlen($status) <= 200) {
					$user = getUserFromToken($_POST['token']);
					$latest = $GLOBALS['conn']->query("SELECT * FROM updates WHERE author = '".$user."'");
					$latest = $latest->fetch_assoc();
					if(!isset($latest) || $latest['status'] !== $status) {
						if(strtotime($latest['date']) < strtotime("-30 seconds")) {
							if(!empty($_POST['reply'])) {
								$stmt = $GLOBALS['conn']->prepare("INSERT INTO updates (author, status, reply) VALUES (?, ?, ?)");
								$stmt->bind_param("ssi", $user, $status, $reply);
							} else {
								$stmt = $GLOBALS['conn']->prepare("INSERT INTO updates (author, status) VALUES (?, ?)");
								$stmt->bind_param("ss", $user, $status);
							}
							$stmt->execute();
							$stmt->close();
						} else { alert("Please wait 30 seconds between updates!"); }
					} else { alert("Stop repeating yourself!"); }
				} else { alert("Your status is longer than 200 characters!"); }
			} else { alert("Your status must be longer than two characters!"); }
		} else { alert('This function requires the "status" parameter!'); }
	} else { alert('This function requires the "token" parameter!'); }
});

$router->get('/delete', function() {
	if(isset($_GET['token'])) {
		if(isset($_GET['id'])) {
			$user = getUserFromToken($_GET['token']);
			$id = intval($_GET['id']);
			$stmt = $GLOBALS['conn']->prepare("DELETE FROM updates WHERE username = ? AND id = ?");
			$stmt->bind_param("si", $user, $id);
			$stmt->execute();
		} else { alert('This function requires the "id" parameter!'); }
	} else { alert('This function requires the "token" parameter!'); }
});

$router->run();