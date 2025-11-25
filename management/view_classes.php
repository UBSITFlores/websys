<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Session Expired."; exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM sections WHERE id = :id");
    if($stmt->execute([':id' => $id])) {
        echo "DELETED";
    } else {
        echo "ERROR";
    }
    exit;
}
$sql = "SELECT 
            s.id, s.code, s.description, s.section, s.schedule_time, s.track, s.year_level,
            a.lname as prof_last, a.fname as prof_first,
            (SELECT COUNT(*) FROM enrollments e WHERE e.section_id = s.id) as student_count
        FROM sections s
        LEFT JOIN account a ON s.instructor_id = a.id
        ORDER BY s.track, s.year_level, s.code ASC";

$classes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="form-card" style="max-width: 1000px; padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="margin:0; border:none;">Master Class List</h2>
        <button onclick="loadZone('create_class.php', null)" class="btn-save" style="width:auto; padding:10px 20px;">+ Add New Class</button>
    </div>

    <style>
        .master-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 0.9rem; 
        }
        .master-table th { 
            background: #002D72; 
            color: white; 
            padding: 12px; 
            text-align: left; 
        }
        .master-table td { 
            padding: 10px; 
            border-bottom: 1px solid #eee; 
            color: #333; 
        }
        .master-table tr:hover { 
            background: #f0f8ff; 
        }
        .badge-track { 
            background: #eee; 
            padding: 3px 8px; 
            border-radius: 4px; 
            font-size: 0.8em; 
            font-weight: bold; 
            color: #555; 
        }
        .btn-del { 
            background: #dc3545; 
            color: white; 
            border: none; 
            padding: 5px 10px; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        .btn-del:hover { 
            background: #a71d2a; 
        }
    </style>

    <div style="overflow-x:auto;">
        <table class="master-table">
            <thead>
                <tr>
                    <th>Code / Section</th>
                    <th>Description</th>
                    <th>Schedule / Room</th>
                    <th>Track</th>
                    <th>Instructor</th>
                    <th style="text-align:center;">Students</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($classes)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:20px;">No classes found.</td></tr>
                <?php else: ?>
                    <?php foreach($classes as $c): ?>
                    <tr id="row_<?php echo $c['id']; ?>">
                        <td>
                            <span style="font-weight:bold; color:#002D72;"><?php echo htmlspecialchars($c['code']); ?></span><br>
                            <?php echo htmlspecialchars($c['section']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($c['description']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($c['schedule_time']); ?><br>
                            <small style="color:#777;"><?php echo htmlspecialchars($c['room'] ?? 'TBA'); ?></small>
                        </td>
                        <td>
                            <span class="badge-track"><?php echo htmlspecialchars($c['track']); ?></span><br>
                            <small><?php echo htmlspecialchars($c['year_level']); ?></small>
                        </td>
                        <td>
                            <?php if($c['prof_last']): ?>
                                <?php echo htmlspecialchars($c['prof_last'] . ', ' . $c['prof_first']); ?>
                            <?php else: ?>
                                <span style="color:#dc3545; font-style:italic;">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center; font-weight:bold;">
                            <?php echo $c['student_count']; ?>
                        </td>
                        <td>
                            <button class="btn-del" onclick="deleteClass(<?php echo $c['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteClass(id) {
    if(confirm("Are you sure? This will UN-ENROLL all students in this class.")) {
        var formData = new FormData();
        formData.append('delete_id', id);

        fetch('view_classes.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "DELETED") {
                var row = document.getElementById('row_' + id);
                row.style.display = 'none';
            } else {
                alert("Error deleting class.");
            }
        });
    }
}
</script>