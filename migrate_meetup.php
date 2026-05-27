<?php
$env_file = __DIR__ . '/.env';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'hcmue_pass_sach';

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        $value = trim($value, '"');
        $value = trim($value, "'");
        if ($name == 'DB_HOSTNAME') $db_host = $value;
        if ($name == 'DB_USERNAME') $db_user = $value;
        if ($name == 'DB_PASSWORD') $db_pass = $value;
        if ($name == 'DB_NAME') $db_name = $value;
    }
}

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$queries = [
    "ALTER TABLE messages ADD COLUMN message_type ENUM('text', 'meetup') DEFAULT 'text' AFTER content",
    "ALTER TABLE messages ADD COLUMN meetup_location VARCHAR(255) DEFAULT NULL AFTER message_type",
    "ALTER TABLE messages ADD COLUMN meetup_time DATETIME DEFAULT NULL AFTER meetup_location",
    "ALTER TABLE messages ADD COLUMN meetup_status ENUM('pending', 'accepted', 'rejected', 'cancelled') DEFAULT NULL AFTER meetup_time"
];

foreach ($queries as $query) {
    if ($mysqli->query($query) === TRUE) {
        echo "Thực thi thành công: $query\n";
    } else {
        echo "Lỗi hoặc cột đã tồn tại: " . $mysqli->error . "\n";
    }
}

$mysqli->close();
echo "Migration hoàn tất.\n";
