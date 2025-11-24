<?php
require_once '../functions/instructor_function.php';

// Demo instructor ID; replace with session in production
$instructor_id = 1;

$instructor = new Instructor();

// Fetch school years dynamically
$school_years = $instructor->getSchoolYearsByInstructor($instructor_id);
array_unshift($school_years, 'All'); // Add 'All' option at start

// Grade level categories for dropdown
$grade_level_categories = ['All', 'High School', 'Senior High'];

// Get filter inputs from GET or default values
$filter_school_year = $_GET['school_year'] ?? 'All';
$filter_level_category = $_GET['level_category'] ?? 'All';

// Get all classes filtered by school year first
$classes_raw = $instructor->getClassesFiltered($instructor_id, null, $filter_school_year);

// Filter classes by grade level category in PHP based on numeric grade_level
$classes = [];
foreach ($classes_raw as $class) {
    $grade_level = intval($class['grade_level']);
    if ($filter_level_category === 'High School') {
        if ($grade_level >= 7 && $grade_level <= 10) {
            $classes[] = $class;
        }
    } elseif ($filter_level_category === 'Senior High') {
        if ($grade_level >= 11 && $grade_level <= 12) {
            $classes[] = $class;
        }
    } else { // 'All'
        $classes[] = $class;
    }
}

// Helper to display Year Level text
function getYearLevel($grade_level) {
    if (!$grade_level) return "Unknown";
    $num = intval($grade_level);
    if ($num >= 7 && $num <= 10) return "High School";
    if ($num >= 11 && $num <= 12) return "Senior High";
    return "Unknown";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Grading Sheets - Instructor Portal</title>
<link rel="stylesheet" href="index.css" />
<style>
    tbody tr { cursor: pointer; }
    tbody tr:hover { background-color: #f2f2f2; }
</style>
</head>
<body>

<div class="header">
    <div class="logo">AMS Instructor Portal</div>
    <form action="../logout.php" method="post" style="margin:0;">
        <button class="logout-button" type="submit">Logout</button>
    </form>
</div>

<div class="container">
    <div class="sidebar">
        <button onclick="location.href='class-list.php'">Class List</button>
        <button class="active" onclick="location.href='grading-sheet.php'">Grading Sheets</button>
        <a href="profile.php" title="Profile"><div class="profile-icon" aria-label="Profile"></div></a>
    </div>

    <div class="main">
        <h2>Grading Sheets</h2>

        <form method="get" style="margin-bottom:20px;">
            <label for="level_category">Grade Level Category:</label>
            <select id="level_category" name="level_category">
                <?php foreach ($grade_level_categories as $cat): ?>
                    <option value="<?=htmlspecialchars($cat)?>" <?= $cat === $filter_level_category ? 'selected' : '' ?>><?=htmlspecialchars($cat)?></option>
                <?php endforeach; ?>
            </select>

            <label for="school_year">School Year:</label>
            <select id="school_year" name="school_year">
                <?php foreach ($school_years as $sy): ?>
                    <option value="<?=htmlspecialchars($sy)?>" <?= $sy === $filter_school_year ? 'selected' : '' ?>><?=htmlspecialchars($sy)?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Search</button>
        </form>

        <table class="grading-table" border="1" cellpadding="6" style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Grade Level</th>
                    <th>Description</th>
                    <th>Last Transaction</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php if (empty($classes)): ?>
                    <tr><td colspan="7" style="text-align:center;">No classes found for selected filters.</td></tr>
                <?php else: ?>
                    <?php $count = 1; foreach ($classes as $class): ?>
                        <tr onclick="window.location.href='section-grades.php?class_id=<?=urlencode($class['class_id'])?>'">
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($class['section_name']) ?></td>
                            <td><?= htmlspecialchars($class['subject_name']) ?></td>
                            <td><?= getYearLevel($class['grade_level']) ?></td>
                            <td><?= htmlspecialchars($class['description']) ?></td>
                            <td><?= 'N/A' /* No timestamp column available in schema */ ?></td>
                            
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
