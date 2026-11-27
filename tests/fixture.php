<?php

// Router for the test server on the local loopback interface.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/health') {
	echo 'ready';
	return;
}

if (strpos($path, '/api/v2/') === 0 && isset($_GET['offline'])) {
	http_response_code(404);
	header('Content-Type: application/json');
	echo '{"success":false,"error":"NOT_FOUND","message":"Page not found"}';
	return;
}
if ($path === '/api/v2/simple') {
	header('Location: /api/v2/wait', true, 302);
	return;
}
if ($path === '/api/v2/wait') {
	header('Location: simple/123', true, 302);
	return;
}
if ($path === '/api/v2/simple/123') {
	header('Content-Type: image/png');
	echo "\x89PNG\r\n\x1a\n\xff";
	return;
}

if ($path !== '/api/v1/screenshot/pdf' && $path !== '/api/v1/screenshot/video') {
	header('Content-Type: application/json');
	echo json_encode(array(
		'method' => $_SERVER['REQUEST_METHOD'],
		'json' => json_decode(file_get_contents('php://input')),
		'path' => $path,
		'query' => $_GET,
		'rawQuery' => $_SERVER['QUERY_STRING'],
		'file' => isset($_FILES['file']) ? file_get_contents($_FILES['file']['tmp_name']) : null
	));
	return;
}

$formats = array(
	'pdf' => array('Application/PDF; charset=binary', "%PDF-1.7\n\x00\x80\xff"),
	'webm' => array('video/webm', "\x1a\x45\xdf\xa3\x00\x80\xff"),
	'mp4' => array('video/mp4', "\x00\x00\x00\x10ftyp\x00\x80\xff"),
	'gif' => array('image/gif', "GIF89a\x00\x80\xff")
);

if (isset($_GET['status'])) {
	http_response_code((int)$_GET['status']);
	header('Content-Type: application/json');
	echo '{"error":"test"}';
	return;
}

$format = isset($_GET['format']) ? $_GET['format'] : ($path === '/api/v1/screenshot/pdf' ? 'pdf' : 'webm');
list($type, $bytes) = $formats[$format];
if (isset($_GET['unexpected'])) {
	$type = 'text/html';
	$bytes = '<p>not an artifact</p>';
}

if (!isset($_GET['id']) || $_GET['id'] !== '9007199254740991' ||
	!isset($_GET['key']) || $_GET['key'] !== 'test key&value') {
	http_response_code(404);
	return;
}

header('Content-Type: ' . $type);
if (isset($_GET['truncated'])) {
	header('Content-Length: ' . (strlen($bytes) + 1));
}
if (!isset($_GET['empty'])) {
	echo $bytes;
}
