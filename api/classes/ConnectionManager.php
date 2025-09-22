<?php

// https://www.php.net/manual/en/book.pgsql.php

// https://www.php.net/manual/en/pgsql.examples-basic.php
// https://www.php.net/manual/en/pgsql.examples-queries.php

class ConnectionManager
{
    public static function connect(bool $useServer = true)
    {
        if ($useServer) {
            $host = $_ENV['PGHOST'];
            $port = $_ENV['PGPORT'];
            $db = $_ENV['PGDATABASE'];
            $user = $_ENV['PGUSER'];
            $password = $_ENV['PGPASSWORD'];

            $connection_string = "host=" . $host . " port=" . $port . " dbname=" . $db . " user=" . $user . " password=" . $password . " sslmode=require";
        } else {
            $connection_string = "host=localhost port=5432 dbname=wad2 user=postgres password=root";
        }

        $conn = pg_connect($connection_string);

        return $conn;
    }
}
