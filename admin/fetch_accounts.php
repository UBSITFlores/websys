<?php
session_start();
require_once 'pdo_functions.php';

if (!isset($_SESSION['ROLE']) || !in_array($_SESSION['ROLE'], ['management', 'admin'])) {
    http_response_code(403);
    exit('Access denied');
}

$filterRole = $_GET['ROLE'] ?? 'all';
$searchTerm = trim($_GET['search'] ?? '');

$sqlBase = "SELECT * FROM account";
$whereClauses = [];
$sqlParams = [];

// Build WHERE clauses with distinct placeholders
if ($filterRole !== 'all' && $filterRole !== '') {
    $whereClauses[] = "role = :role";
    $sqlParams[':role'] = $filterRole;
}

if ($searchTerm !== '') {
    $whereClauses[] = "(account_id LIKE :search_id OR fname LIKE :search_fname OR lname LIKE :search_lname)";
    $sqlParams[':search_id'] = "%" . $searchTerm . "%";
    $sqlParams[':search_fname'] = "%" . $searchTerm . "%";
    $sqlParams[':search_lname'] = "%" . $searchTerm . "%";
}



// Assemble final query
$sqlFinal = $sqlBase;
if ($whereClauses) {
    $sqlFinal .= " WHERE " . implode(" AND ", $whereClauses);
}
$sqlFinal .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sqlFinal);

    // Debug output for SQL and parameters
    error_log("SQL Query: " . $sqlFinal);
    error_log("Params: " . print_r($sqlParams, true));

    if (!empty($sqlParams)) {
        $stmt->execute($sqlParams);
    } else {
        $stmt->execute();
    }
} catch (PDOException $e) {
    error_log("PDO Exception: " . $e->getMessage());
    http_response_code(500);
    echo "Database error occurred.";
    exit;
}

$rowsFound = false;
while ($account = $stmt->fetch()) {
    $rowsFound = true;
    echo '<tr>';
    echo '<td>' . htmlspecialchars($account['account_id']) . '</td>';
    echo '<td>' . htmlspecialchars($account['fname'] . ' ' . $account['lname']) . '</td>';
    echo '<td style="text-transform:capitalize;">' . htmlspecialchars($account['role']) . '</td>';
    echo '<td>' . htmlspecialchars($account['track']) . '</td>';
    echo '<td>' . htmlspecialchars($account['date_enrolled']) . '</td>';
    echo '<td>';
    echo '<a href="admin_edit_account.php?id=' . $account['id'] . '" style="color:#2980b9; font-size:12px; margin-right:5px; text-decoration:none;">Edit</a>';
    echo '<a href="admin_delete_account.php?id=' . $account['id'] . '" onclick="return confirm(\'Confirm delete?\')" style="color:#c0392b; font-size:12px; text-decoration:none;">Delete</a>';
    echo '</td>';
    echo '</tr>';
}

if (!$rowsFound) {
    echo '<tr><td colspan="6" style="text-align:center; color:#888;">No data found!</td></tr>';
}
