<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['POST only.']]);
    exit;
}

function clean($v) { return trim(strip_tags((string)$v)); }

$d = [
    'name'        => clean($_POST['name'] ?? ''),
    'email'       => clean($_POST['email'] ?? ''),
    'phone'       => clean($_POST['phone'] ?? ''),
    'dob'         => clean($_POST['dob'] ?? ''),
    'gender'      => clean($_POST['gender'] ?? ''),
    'course'      => clean($_POST['course'] ?? ''),
    'institution' => clean($_POST['institution'] ?? ''),
    'address'     => clean($_POST['address'] ?? ''),
];
$interests = array_map('clean', (array)($_POST['interests'] ?? []));
$d['interests'] = implode(', ', $interests);

// Server-side validation
$errors = [];
foreach (['name','email','phone','dob','gender','course','institution','address'] as $f) {
    if ($d[$f] === '') $errors[] = ucfirst($f) . ' is required.';
}
if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email is invalid.';
if ($d['phone'] !== '' && !preg_match('/^[0-9+\- ]{7,15}$/', $d['phone'])) $errors[] = 'Phone is invalid.';
if ($d['dob'] !== '' && !DateTime::createFromFormat('Y-m-d', $d['dob'])) $errors[] = 'Date of birth is invalid.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

date_default_timezone_set('Asia/Kolkata');
$d['ref'] = 'REG-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
$d['submitted'] = date('d M Y, h:i A');

// Optional persistence: append to a log file (use a database in production)
@file_put_contents(__DIR__ . '/registrations.log', json_encode($d) . PHP_EOL, FILE_APPEND | LOCK_EX);

echo json_encode(['success' => true, 'data' => $d]);
