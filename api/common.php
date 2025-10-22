<?php
// Database connection settings (replace these with your actual credentials from Neon)
$host = "db.gentle-tooth-00501494.neon.tech";        // Host provided by Neon
$dbname = "wad2_database";    // Your database name


// Create the PostgreSQL connection string
$conn_str = "host=$host dbname=$dbname user=$user";

// Establish the database connection
$dbconn = pg_connect($conn_str);

if (!$dbconn) {
    // If the connection fails, stop and show an error
    die("Connection failed: " . pg_last_error());
}

// Autoload classes from the "classes/" directory
spl_autoload_register(function ($class) {
    require_once "classes/" . $class . ".php";
});
?>
