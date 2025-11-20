<?php
// Function to get accounts with filters
function getAccounts($pdo, $filterRole = 'all', $searchTerm = '') {
    $sqlBase = "SELECT * FROM account";
    $whereClauses = [];
    $sqlParams = [];

    if ($filterRole !== 'all' && $filterRole !== '') {
        $whereClauses[] = "role = :role";
        $sqlParams[':role'] = $filterRole;
    }

    if ($searchTerm !== '') {
        $whereClauses[] = "(account_id LIKE :search OR fname LIKE :search OR lname LIKE :search)";
        $sqlParams[':search'] = "%" . $searchTerm . "%";
    }

    $sqlFinal = $sqlBase;
    if ($whereClauses) {
        $sqlFinal .= " WHERE " . implode(" AND ", $whereClauses);
    }
    $sqlFinal .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sqlFinal);
    if ($sqlParams) {
        $stmt->execute($sqlParams);
    } else {
        $stmt->execute();
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to render accounts table HTML
function renderAccountsTable($accounts) {
    $html = '<div style="overflow-x:auto;">
        <table>
          <thead>
            <tr>
              <th>Account ID</th>
              <th>Full Name</th>
              <th>Role</th>
              <th>Track</th>
              <th>Date Enrolled</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>';

    if (count($accounts) > 0) {
        foreach ($accounts as $account) {
            $html .= '<tr>
                <td>' . htmlspecialchars($account['account_id']) . '</td>
                <td>' . htmlspecialchars($account['fname'] . ' ' . $account['lname']) . '</td>
                <td style="text-transform:capitalize;">' . htmlspecialchars($account['role']) . '</td>
                <td>' . htmlspecialchars($account['track']) . '</td>
                <td>' . htmlspecialchars($account['date_enrolled']) . '</td>
                <td>
                  <a href="admin_edit_account.php?id=' . $account['id'] . '" class="action-link edit-link">Edit</a>
                  <a href="admin_delete_account.php?id=' . $account['id'] . '" onclick="return confirm(\'Confirm delete?\')" class="action-link delete-link">Delete</a>
                </td>
              </tr>';
        }
    } else {
        $html .= '<tr>
            <td colspan="6" style="text-align:center; padding:20px; color:#7f8c8d;">No accounts found</td>
          </tr>';
    }

    $html .= '</tbody>
        </table>
      </div>';

    return $html;
}

// AJAX endpoint - handle requests
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    session_start();
    require_once 'pdo_functions.php';

    // Check authentication
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'management') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $filterRole = $_GET['role'] ?? 'all';
    $searchTerm = trim($_GET['search'] ?? '');

    $accounts = getAccounts($pdo, $filterRole, $searchTerm);
    echo renderAccountsTable($accounts);
    exit;
}
?>