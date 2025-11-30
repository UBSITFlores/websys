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

// Filter role
if ($filterRole !== 'all' && $filterRole !== '') {
    $whereClauses[] = "role = :role";
    $sqlParams[':role'] = $filterRole;
}

// Search filtering
if ($searchTerm !== '') {
    $whereClauses[] = "(account_id LIKE :search_id OR fname LIKE :search_fname OR lname LIKE :search_lname)";
    $sqlParams[':search_id'] = "%".$searchTerm."%";
    $sqlParams[':search_fname'] = "%".$searchTerm."%";
    $sqlParams[':search_lname'] = "%".$searchTerm."%";
}

$sqlFinal = $sqlBase;
if ($whereClauses) $sqlFinal .= " WHERE " . implode(" AND ", $whereClauses);
$sqlFinal .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sqlFinal);
    !empty($sqlParams) ? $stmt->execute($sqlParams) : $stmt->execute();
} catch(PDOException $e){
    error_log("PDO Exception: ".$e->getMessage());
    http_response_code(500);
    exit("Database error occurred.");
}

$rowsFound=false;

while($account=$stmt->fetch()){
    $rowsFound=true;
    echo "
    <tr>
        <td>".htmlspecialchars($account['account_id'])."</td>
        <td>".htmlspecialchars($account['fname'].' '.$account['lname'])."</td>
        <td class='role-cap'>".htmlspecialchars($account['role'])."</td>
        <td>".htmlspecialchars($account['track'])."</td>
        <td>".htmlspecialchars($account['date_enrolled'])."</td>
        <td>
            <a href='admin_edit_account.php?id=".$account['id']."' class='link-edit'>Edit</a>
            <a href='admin_delete_account.php?id=".$account['id']."' onclick='return confirm(\"Confirm delete?\")' class='link-delete'>Delete</a>
        </td>
    </tr>";
}

if(!$rowsFound){
    echo "<tr><td colspan='6' class='no-data'>No data found!</td></tr>";
}
?>
