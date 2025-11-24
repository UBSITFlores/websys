<?php
// admin_functions.php
// Minimal CRUD functions for the existing `account` table (no hashing, no duplicate checks).

require_once __DIR__ . '/dbconfig.php';

function fetchAllAccounts() {
    global $pdo;
    $sql = "SELECT id, account_id, fname, mname, lname, date_enrolled, password, role, track FROM account ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function fetchAccountById($id) {
    global $pdo;
    $sql = "SELECT id, account_id, fname, mname, lname, date_enrolled, password, role, track FROM account WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

function createAccount($data) {
    global $pdo;
    $sql = "INSERT INTO account (account_id, fname, mname, lname, date_enrolled, password, role, track) VALUES (:account_id, :fname, :mname, :lname, :date_enrolled, :password, :role, :track)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':account_id'   => $data['account_id'] ?? '',
        ':fname'        => $data['fname'] ?? '',
        ':mname'        => $data['mname'] ?? '',
        ':lname'        => $data['lname'] ?? '',
        ':date_enrolled'=> $data['date_enrolled'] ?? '0000-00-00',
        ':password'     => $data['password'] ?? '',
        ':role'         => $data['role'] ?? 'student',
        ':track'        => $data['track'] ?? ''
    ]);
    return $pdo->lastInsertId();
}

function updateAccount($id, $data) {
    global $pdo;
    $sql = "UPDATE account SET account_id = :account_id, fname = :fname, mname = :mname, lname = :lname, date_enrolled = :date_enrolled, password = :password, role = :role, track = :track WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':account_id'   => $data['account_id'] ?? '',
        ':fname'        => $data['fname'] ?? '',
        ':mname'        => $data['mname'] ?? '',
        ':lname'        => $data['lname'] ?? '',
        ':date_enrolled'=> $data['date_enrolled'] ?? '0000-00-00',
        ':password'     => $data['password'] ?? '',
        ':role'         => $data['role'] ?? 'student',
        ':track'        => $data['track'] ?? '',
        ':id'           => $id
    ]);
}

function deleteAccount($id) {
    global $pdo;
    $sql = "DELETE FROM account WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':id' => $id]);
}
?>
