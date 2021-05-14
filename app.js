if (window.XMLHttpRequest) {
	xhttp = new XMLHttpRequest();
} else {
	xhttp = new ActiveXObject("Microsoft.XMLHTTP");
}

function delete_status(id) {
	if (confirm("Are you sure you want to delete your status?")) {
		xhttp.open("GET", "/ajax/delete?id="+id, false);
		xhttp.send();
		location.reload();
	}
}

function follow_user(user) {
	xhttp.open("GET", "/ajax/follow?user="+user, false);
	xhttp.send();
	location.reload();
}

function unfollow_user(user) {
	if (confirm("Are you sure you want to unfollow this user?")) {
		xhttp.open("GET", "/ajax/unfollow?user="+user, false);
		xhttp.send();
		location.reload();
	}
}