<?php
class Instructor {
    private $pdo;

    public function __construct() {
        $host = "localhost";
        $username = "root";
        $password = "";
        $dbname = "portal";
        $charset = "utf8mb4";

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection Failed! " . $e->getMessage());
        }
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


// Get distinct semesters available for this instructor's classes
public function getSemestersByInstructor($instructor_id) {
    $stmt = $this->pdo->prepare("SELECT DISTINCT semester FROM class_schedule WHERE instructor_id = :instructor_id ORDER BY semester ASC");
    $stmt->execute([':instructor_id' => $instructor_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get distinct school years available for this instructor's classes
public function getSchoolYearsByInstructor($instructor_id) {
    $stmt = $this->pdo->prepare("SELECT DISTINCT school_year FROM class_schedule WHERE instructor_id = :instructor_id ORDER BY school_year DESC");
    $stmt->execute([':instructor_id' => $instructor_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
    public function getGradingSheet($class_id, $grading_period) {
        $stmt = $this->pdo->prepare("
            SELECT e.enrollment_id, st.student_number, st.full_name, g.grade
            FROM enrollments e 
            JOIN students st ON e.student_id = st.student_id
            LEFT JOIN grades g ON e.enrollment_id = g.enrollment_id AND g.grading_period = :grading_period
            WHERE e.class_id = :class_id
            ORDER BY st.full_name ASC
        ");
        $stmt->execute([
            'class_id' => $class_id,
            'grading_period' => $grading_period
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getYearLevel($grade_level) {
        if (is_numeric($grade_level)) {
            if ($grade_level >= 7 && $grade_level <= 10) {
                return "High School";
            } elseif ($grade_level >= 11 && $grade_level <= 12) {
                return "Senior High";
            }
        }
        return "Unknown";
    }

    // Get class info by class_id (including grade_level needed to identify HS/SHS)
public function getClassInfo($class_id) {
    $stmt = $this->pdo->prepare("
        SELECT cs.class_id, s.subject_name, s.grade_level, sec.section_name
        FROM class_schedule cs
        JOIN subjects s ON cs.subject_id = s.subject_id
        JOIN sections sec ON cs.section_id = sec.section_id
        WHERE cs.class_id = :class_id
        LIMIT 1
    ");
    $stmt->execute(['class_id' => $class_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
  
// Get students enrolled in a class by class_id along with enrollment_id and student_number
public function getStudentsByClass($class_id) {
    $stmt = $this->pdo->prepare("
        SELECT 
            e.enrollment_id, 
            s.student_number,           -- correct student number
            s.full_name,                -- bulk name as in students, or...
            a.fname, a.mname, a.lname   -- ...use these only if you need
        FROM enrollments e
        JOIN students s ON e.student_id = s.student_id
        LEFT JOIN account a ON e.student_id = a.id
        WHERE e.class_id = :class_id
        ORDER BY s.student_number ASC
    ");
    $stmt->execute(['class_id' => $class_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getGradeValue($allGrades, $period, $enroll_id) {
    return isset($allGrades[$period][$enroll_id]) ? $allGrades[$period][$enroll_id] : '';
}

  
// // Get existing grades for students by class_id and grading_period
public function getGradesByClassAndPeriod($class_id, $grading_period) {
    $stmt = $this->pdo->prepare("
        SELECT g.enrollment_id, g.grade
        FROM grades g
        JOIN enrollments e ON g.enrollment_id = e.enrollment_id
        WHERE e.class_id = :class_id AND g.grading_period = :grading_period
    ");
    $stmt->execute([
        'class_id' => $class_id,
        'grading_period' => $grading_period,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function saveGrades($grades, $grading_period) {
        $this->pdo->beginTransaction();
        try {
            $stmtUpdate = $this->pdo->prepare("
                UPDATE grades SET grade = :grade, input_type = 'manual' 
                WHERE enrollment_id = :enrollment_id AND grading_period = :grading_period
            ");
            $stmtInsert = $this->pdo->prepare("
                INSERT INTO grades (enrollment_id, grading_period, grade, input_type) 
                VALUES (:enrollment_id, :grading_period, :grade, 'manual')
            ");

            foreach ($grades as $enrollment_id => $grade) {
                $stmtUpdate->execute([
                    'grade' => $grade,
                    'enrollment_id' => $enrollment_id,
                    'grading_period' => $grading_period
                ]);
                if ($stmtUpdate->rowCount() === 0) {
                    $stmtInsert->execute([
                        'enrollment_id' => $enrollment_id,
                        'grading_period' => $grading_period,
                        'grade' => $grade
                    ]);
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getProfile($instructor_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM instructors WHERE instructor_id = :instructor_id");
        $stmt->execute(['instructor_id' => $instructor_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($instructor_id, $full_name, $email) {
        $stmt = $this->pdo->prepare("
            UPDATE instructors SET full_name = :full_name, email = :email WHERE instructor_id = :instructor_id
        ");
        return $stmt->execute([
            'full_name' => $full_name,
            'email' => $email,
            'instructor_id' => $instructor_id
        ]);
    }

    public function changePassword($instructor_id, $new_password_hash) {
        $stmt = $this->pdo->prepare("
            UPDATE instructors SET password_hash = :password_hash WHERE instructor_id = :instructor_id
        ");
        return $stmt->execute([
            'password_hash' => $new_password_hash,
            'instructor_id' => $instructor_id
        ]);
    }
}
?>
