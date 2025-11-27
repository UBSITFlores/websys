<?php 
    class account{
        public $pdo;
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
                        alert('Invalid Credentials.');
                        window.location.href = 'index.php';
                    </script>
                ";
            } else {
                // --- SECURITY CHECK: BLOCK INACTIVE USERS ---
                if (isset($user['status']) && $user['status'] === 'Inactive') {
                    echo "
                        <script>
                            alert('Access Denied: Your account is currently Inactive. Please contact the administrator.');
                            window.location.href = 'index.php';
                        </script>
                    ";
                    exit;
                }

                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }

                $_SESSION['ACCOUNTID'] = $user['account_id'];
                $_SESSION['FNAME'] = $user['fname'];
                $_SESSION['LNAME'] = $user['lname'];

                $role = strtolower(trim($user['role'] ?? ''));
                $_SESSION['ROLE'] = $role;

                if ($role === 'admin') {
                    header("Location: ../admin/index.php");
                    exit;
                } elseif ($role === 'instructor') {
                    header("Location: ../instructor/index.php");
                    exit;
                }
                elseif($role === 'management'){
                    header("Location: ../management/index.php");
                    exit;
                } else {
                    // default (students)
                    header("Location: ../portal/index.php");
                    exit;
                }
            }
        }

        public function logout(){
            session_start();
            session_destroy();
            header("location:../account/login.php");
            exit();
        }
    }
?>