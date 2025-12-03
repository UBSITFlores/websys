<?php
class Instructor {
    private $pdo;

    public function __construct() {
        require __DIR__ . '/db.php';
        $this->pdo = $pdo;
    }

    // Sections: all or filtered by semester/year
    public function getSections($account_id, $semester = null, $schoolYear = null) {
    $query = "SELECT s.section, s.code, s.description, s.last_transaction, s.finalized
              FROM sections s
              WHERE s.instructor_id = :account_id";
    $params = [':account_id' => $account_id];

    if (!empty($semester)) {
        $query .= " AND s.semester = :semester";
        $params[':semester'] = $semester;
    }
    if (!empty($schoolYear)) {
        $query .= " AND s.school_year = :schoolYear";
        $params[':schoolYear'] = $schoolYear;
    }

    $query .= " ORDER BY s.last_transaction DESC";
    $stmt = $this->pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function getSemesters() {
        $stmt = $this->pdo->query("SELECT DISTINCT semester FROM sections ORDER BY semester ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getSchoolYears() {
        $stmt = $this->pdo->query("SELECT DISTINCT school_year FROM sections ORDER BY school_year DESC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Get students enrolled in a section
    public function getStudents($section, $code) {
        $query = "SELECT a.id, a.account_id
                  FROM enrollments e
                  INNER JOIN account a ON e.student_id = a.id
                  INNER JOIN sections s ON e.section_id = s.id
                  WHERE s.section = :section AND s.code = :code
                  ORDER BY a.account_id ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':section' => $section, ':code' => $code]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Save grades (bulk or manual)
    public function saveGrades($section, $code, $grades) {
        $stmtSec = $this->pdo->prepare("SELECT id FROM sections WHERE section = :section AND code = :code");
        $stmtSec->execute([':section' => $section, ':code' => $code]);
        $sectionRow = $stmtSec->fetch(PDO::FETCH_ASSOC);
        if (!$sectionRow) return false;
        $section_id = $sectionRow['id'];

        $sql = "INSERT INTO grades (student_id, section_id, quarter, grade)
                VALUES (:student_id, :section_id, :quarter, :grade)
                ON DUPLICATE KEY UPDATE grade = VALUES(grade)";
        $stmt = $this->pdo->prepare($sql);

        $this->pdo->beginTransaction();
        try {
            foreach ($grades as $student_id => $quarters) {
                foreach ($quarters as $quarter => $grade) {
                    if ($grade !== '') {
                        $stmt->execute([
                            ':student_id' => $student_id,
                            ':section_id' => $section_id,
                            ':quarter'    => $quarter,
                            ':grade'      => $grade
                        ]);
                    }
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    // Get existing grades
    public function getGrades($section, $code) {
        $stmtSec = $this->pdo->prepare("SELECT id FROM sections WHERE section = :section AND code = :code");
        $stmtSec->execute([':section' => $section, ':code' => $code]);
        $sectionRow = $stmtSec->fetch(PDO::FETCH_ASSOC);
        if (!$sectionRow) return [];
        $section_id = $sectionRow['id'];

        $query = "SELECT student_id, quarter, grade
                  FROM grades
                  WHERE section_id = :section_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':section_id' => $section_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grades = [];
        foreach ($rows as $row) {
            $grades[$row['student_id']][$row['quarter']] = $row['grade'];
        }
        return $grades;
    }
}
?>
