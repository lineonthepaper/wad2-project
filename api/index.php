<?php
require_once "common.php";

$conn = ConnectionManager::connect();

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
echo "Connected successfully";
