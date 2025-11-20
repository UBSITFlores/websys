<?php
require_once 'dbconfig.php';
$portalDB = new PortalDatabase($pdo);
class PortalDatabase {

    private $pdoConnection;

    public function __construct($pdo) {
        $this->pdoConnection = $pdo;
    }

    // Login method
    public function getUserByCredentials($accountId, $password) {
        $query = "SELECT * FROM account WHERE account_id = :accountId AND password = :password LIMIT 1";
        $stmt = $this->pdoConnection->prepare($query);
        $stmt->execute([ ':accountId' => $accountId, ':password' => $password ]);
        return $stmt;
    }

    // Register a new student
    public function addStudent($accountId, $firstName, $middleName, $lastName, $password, $track) {
        $query = "INSERT INTO account(account_id, fname, mname, lname, date_enrolled, password, role, track)
                  VALUES(:accountId, :firstName, :middleName, :lastName, CURDATE(), :password, 'student', :track)";
        $stmt = $this->pdoConnection->prepare($query);
        $stmt->execute([
            ':accountId' => $accountId,
            ':firstName' => $firstName,
            ':middleName' => $middleName,
            ':lastName' => $lastName,
            ':password' => $password,
            ':track' => $track
        ]);
        return $stmt;
    }

    // Retrieve all accounts for admin
    public function getAllAccounts() {
        $stmt = $this->pdoConnection->prepare("SELECT * FROM account ORDER BY id DESC");
        $stmt->execute();
        return $stmt;
    }

    // Get account by ID for editing/viewing
    public function getAccountById($accountId) {
        $stmt = $this->pdoConnection->prepare("SELECT * FROM account WHERE id = :id");
        $stmt->execute([':id' => $accountId]);
        return $stmt;
    }

    // Add general account (admin use)
    public function addAccount($accountId, $firstName, $middleName, $lastName, $password, $role, $track) {
        $query = "INSERT INTO account(account_id, fname, mname, lname, date_enrolled, password, role, track)
                  VALUES(:accountId, :firstName, :middleName, :lastName, CURDATE(), :password, :role, :track)";
        $stmt = $this->pdoConnection->prepare($query);
        $stmt->execute([
            ':accountId' => $accountId,
            ':firstName' => $firstName,
            ':middleName' => $middleName,
            ':lastName' => $lastName,
            ':password' => $password,
            ':role' => $role,
            ':track' => $track
        ]);
        return $stmt;
    }

    // Update account details
    public function updateAccount($accountId, $firstName, $middleName, $lastName, $password, $role, $track) {
        $query = "UPDATE account SET fname = :firstName, mname = :middleName, lname = :lastName, 
                  password = :password, role = :role, track = :track WHERE id = :accountId";
        $stmt = $this->pdoConnection->prepare($query);
        $stmt->execute([
            ':accountId' => $accountId,
            ':firstName' => $firstName,
            ':middleName' => $middleName,
            ':lastName' => $lastName,
            ':password' => $password,
            ':role' => $role,
            ':track' => $track
        ]);
        return $stmt;
    }

    // Delete account
    public function deleteAccount($accountId) {
        $stmt = $this->pdoConnection->prepare("DELETE FROM account WHERE id = :id");
        $stmt->execute([':id' => $accountId]);
        return $stmt;
    }
}

$portalDB = new PortalDatabase($pdo);
?>
