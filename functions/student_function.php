<?php
class Student {
    private $pdo;

    public function __construct() {
        $host = "localhost";
        $user = "root";
        $pass = "";
        $dbname = "portal";
        $charset = "utf8mb4";

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $this->pdo = new PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection Failed: " . $e->getMessage());
        }
    }

    public function getStudentId($account_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM account WHERE account_id = :aid");
        $stmt->execute([':aid' => $account_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id'] : false;
    }

    public function getGrades($student_pk) {
        $query = "SELECT s.code, s.description, g.quarter, g.grade 
                  FROM grades g
                  JOIN sections s ON g.section_id = s.id
                  WHERE g.student_id = :sid
                  ORDER BY s.code, g.quarter ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':sid' => $student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSchedule($student_pk) {
        $query = "SELECT 
                    s.schedule_time, 
                    s.description as course, 
                    s.code,
                    s.room,
                    a.fname as instructor_fname, 
                    a.lname as instructor_lname
                  FROM enrollments e
                  JOIN sections s ON e.section_id = s.id
                  LEFT JOIN account a ON s.instructor_id = a.id
                  WHERE e.student_id = :sid";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':sid' => $student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getProfile($student_pk) {
        $query = "SELECT 
                    a.account_id,
                    a.track,
                    s.* FROM students s
                  JOIN account a ON s.student_id = a.id
                  WHERE s.student_id = :sid";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':sid' => $student_pk]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getEnrollment($student_pk) {
        $query = "SELECT 
                    s.code, 
                    s.description, 
                    s.section, 
                    s.semester,
                    s.school_year
                  FROM enrollments e
                  JOIN sections s ON e.section_id = s.id
                  WHERE e.student_id = :sid
                  ORDER BY s.code ASC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':sid' => $student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>