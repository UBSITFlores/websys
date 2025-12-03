<?php
$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");

if (isset($_POST['track']) && isset($_POST['year_level'])) {
    $track = $_POST['track'];
    $year  = $_POST['year_level'];

    // 1. BUILD QUERY (Simple If-Else)
    // We query 'section_list' because that's where valid sections live
    $sql = "SELECT section_name FROM section_list WHERE year_level = ?";
    
    if ($track == 'kinder') {
        $sql = $sql . " AND track = 'kinder'";
    } 
    else if ($track == 'junior high school') {
        $sql = $sql . " AND track = 'junior high school'";
    } 
    else {
        // For Senior High, we accept ANY track (STEM, ABM, HUMSS)
        // We just make sure it's not Kinder or JHS to be safe
        $sql = $sql . " AND track != 'kinder' AND track != 'junior high school'";
    }

    $sql = $sql . " ORDER BY section_name ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year]);
    
    // Fetch all rows
    $sections = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $count = count($sections);

    // 2. OUTPUT OPTIONS (Simple For Loop)
    if ($count > 0) {
        echo "<option value=''>-- Select Section --</option>";
        
        for ($i = 0; $i < $count; $i++) {
            $sec_name = $sections[$i];
            // Manually echo the option
            echo "<option value='" . htmlspecialchars($sec_name) . "'>" . htmlspecialchars($sec_name) . "</option>";
        }
    } else {
        echo "<option value=''>-- No Sections Found --</option>";
    }
}
?>