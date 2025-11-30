<?php
$pdo = new PDO("mysql:host=localhost;dbname=portal;charset=utf8mb4", "root", "");

if (isset($_POST['track']) && isset($_POST['year_level'])) {
    $track = $_POST['track'];
    $year  = $_POST['year_level'];

    // Get distinct section names for this level
    // We group by section name so we don't get duplicates
    $sql = "SELECT DISTINCT section FROM sections 
            WHERE track = ? AND year_level = ? 
            ORDER BY section ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$track, $year]);
    $sections = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($sections) {
        echo "<option value=''>-- Select Section --</option>";
        foreach($sections as $sec) {
            echo "<option value='" . htmlspecialchars($sec) . "'>" . htmlspecialchars($sec) . "</option>";
        }
    } else {
        echo "<option value=''>-- No Sections Found --</option>";
    }
}
?>