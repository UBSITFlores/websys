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

        public function login($account_id, $password) {
            $fetch = $this->pdo->prepare("SELECT * FROM account WHERE account_id= :account_id AND password= :password");
            $fetch->execute([':account_id' => $account_id, ':password' => $password]);
            $user = $fetch->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo "<script>alert('Invalid Credentials.'); window.location.href = 'index.php';</script>";
            } else {
                // --- 1. STATUS CHECK ---
                if ($user['status'] === 'Inactive') {
                    echo "<script>alert('Access Denied: Your account is Inactive.'); window.location.href = 'index.php';</script>";
                    exit;
                }

                // --- 2. GRADUATION GRACE PERIOD CHECK ---
                // If status is 'Graduated', check if 3 months have passed since 'last_active_date'
                if ($user['status'] === 'Graduated' && !empty($user['last_active_date'])) {
                    $gradDate = new DateTime($user['last_active_date']);
                    $now = new DateTime();
                    $interval = $gradDate->diff($now);
                    
                    // If more than 3 months (approx 90 days), auto-disable
                    if ($interval->days > 90) {
                        // Auto-update to Inactive
                        $update = $this->pdo->prepare("UPDATE account SET status = 'Inactive' WHERE id = ?");
                        $update->execute([$user['id']]);
                        
                        echo "<script>alert('Access Expired: Your 3-month grace period after graduation has ended.'); window.location.href = 'index.php';</script>";
                        exit;
                    }
                }

                // ... (Rest of your login logic: Session setting and Redirects) ...
                if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                $_SESSION['ACCOUNTID'] = $user['account_id'];
                $_SESSION['FNAME'] = $user['fname'];
                $_SESSION['LNAME'] = $user['lname'];
                $_SESSION['ROLE'] = strtolower(trim($user['role'] ?? ''));
                
                // Redirects
                if ($_SESSION['ROLE'] === 'admin') header("Location: ../admin/index.php");
                elseif ($_SESSION['ROLE'] === 'instructor') header("Location: ../instructor/index.php");
                elseif ($_SESSION['ROLE'] === 'management') header("Location: ../management/index.php");
                else header("Location: ../portal/index.php");
                exit;
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