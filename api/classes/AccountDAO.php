<?php

require_once __DIR__ . "/../common.php";

class AccountDAO
{
    private $conn;
    public function __construct(bool $useServer = true)
    {
        $this->conn = ConnectionManager::connect($useServer);
    }

    public function addAccount(Account $newAccount): bool
    {
        $query = "insert into 
        account(display_name, email, pw_hashed, is_staff)
        values($1, $2, $3, $4)
        ";
        $params = [
            $newAccount->getDisplayName(),
            $newAccount->getEmail(),
            $newAccount->getPasswordHashed(),
            $newAccount->isStaff()
        ];

        $success = pg_query_params($this->conn, $query, $params);

        return $success !== false;
    }

    public function getAccountById(int $accountId): ?Account
    {
        $query = "select account_id, display_name, pw_hashed, is_staff, email from account
        where account_id = $1";
        $params = [$accountId];

        $res = pg_fetch_array(pg_query_params($this->conn, $query, $params), null, PGSQL_ASSOC);

        if ($res == false) {
            return null;
        }

        return new Account(
            (int)$res["account_id"],
            $res["display_name"],
            $res["email"],
            $res["pw_hashed"],
            $res["is_staff"] === "t" ? true : false
        );
    }

    public function getAccountByEmail(string $email): ?Account
    {
        $query = "select account_id, display_name, pw_hashed, is_staff, email from account
        where email = $1";
        $params = [$email];

        $res = pg_fetch_array(pg_query_params($this->conn, $query, $params), null, PGSQL_ASSOC);

        if ($res == false) {
            return null;
        }

        return new Account(
            (int)$res["account_id"],
            $res["display_name"],
            $res["email"],
            $res["pw_hashed"],
            $res["is_staff"] === "t" ? true : false
        );
    }

    public function updatePassword(int $accountId, string $newPasswordHashed): bool
    {
        $query = "update account set pw_hashed = $1 where account_id = $2";
        $params = [$newPasswordHashed, $accountId];

        $success = pg_query_params($this->conn, $query, $params);

        return $success !== false;
    }

    public function updateDisplayName(int $accountId, string $newDisplayName): bool
    {
        $query = "update account set display_name = $1 where account_id = $2";
        $params = [$newDisplayName, $accountId];

        $success = pg_query_params($this->conn, $query, $params);

        return $success !== false;
    }

    public function updateEmail(int $accountId, string $newEmail): bool
    {
        $query = "update account set email = $1 where account_id = $2";
        $params = [$newEmail, $accountId];

        $success = pg_query_params($this->conn, $query, $params);

        return $success !== false;
    }

    public function verifyPassword(string $email, string $password): bool
    {
        $account = $this->getAccountByEmail($email);
        if (!$account) {
            return false;
        }
        
        return password_verify($password, $account->getPasswordHashed());
    }
    public function deleteAccount(int $accountId): bool
{
    $query = "DELETE FROM account WHERE account_id = $1";
    $params = [$accountId];

    $success = pg_query_params($this->conn, $query, $params);

    return $success !== false;
}
}