<?php
require_once "common.php";

$connMgr = new ConnectionManager();
$conn = $connMgr->connect();

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
echo "Connected successfully";