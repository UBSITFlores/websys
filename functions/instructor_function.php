<?php 

    class instructor {
        function __construct() {
            $host="localhost";
            $username="root";
            $password="";
            $dbname="portal";
            $charset="utf8mb4";

            try {
                $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
                $connection = new PDO($dsn, $username, $password);
                $this->pdo = $connection;
            } catch (PDOException $e) {
                die("Connection Failed! " . $e->getMessage());
            }
        }
    }

?>