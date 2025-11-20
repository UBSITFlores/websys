<?php 
    class account{
        function __construct(){
            $host = "localhost";
            $user = "root";
            $pass = "";
            $dbname = "portal";
            $charset = "utf8mb4";

            try{
                $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
                
                $connection = new PDO($dsn,$user,$pass);
                $this->pdo = $connection;
                
            }catch(PDOException $e){
                die("Connection Failed!" . $e->getMessage());
            }
        }

        public function login($account_id,$password){
            $fetch = $this->pdo->prepare("SELECT * FROM account WHERE account_id= :account_id AND password= :password");
            $fetch->execute([
                ':account_id' => $account_id,
                ':password' => $password 
            ]);
            $options = [
                PDO::ATTR_ERRMODE => PDO:: ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            $user = $fetch->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                echo "
                    <script>
                        alert('Provide Correct Credentials...');
                        window.location.href = 'index.php';
                    </script>
                ";
            } else {
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }

                $_SESSION['ACCOUNTID'] = $user['account_id'];
                $_SESSION['FNAME'] = $user['fname'];
                $_SESSION['LNAME'] = $user['lname'];

                // Normalize role value coming from the DB and store it
                $role = strtolower(trim($user['role'] ?? ''));
                $_SESSION['ROLE'] = $role;
                error_log('login role: [' . $role . ']'); // temporary debug, remove when verified

                if ($role === 'management') {
                    header("Location: ../admin/index.php");
                    exit;
                } elseif ($role === 'instructor') {
                    header("Location: ../instructor/index.php");
                    exit;
                } else {
                    // default (students and other roles)
                    header("Location: ../portal/index.php");
                    exit;
                }
            }
        }
        public function logout(){
            session_start();
            session_destroy();
            header("location:../account/index.php");
            exit();
        }
    }
?>