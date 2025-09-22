<?php
require_once "common.php";

$conn = ConnectionManager::connect();

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
echo "Connected successfully";

// $success = AccountDAO::addAccount(
//     "muh hee ow",
//     "rileylee.2024@computing.smu.edu.sg",
//     Account::hashPassword("muh hee ow"),
//     true
// );

// echo  "<br>Insertion: ";

// echo $success ? 'true' : 'false';

// $account = AccountDAO::getAccountById(7);

// if ($account !== null) {
//     var_dump($account);
// } else {
//     echo "Get failed";
// }
