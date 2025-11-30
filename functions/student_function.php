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
    // --- NEW: BILLING FUNCTIONS ---
    
    // 1. Get Financial Summary
    public function getBillingSummary($student_pk) {
        // Total Tuition (Assessment)
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) FROM assessments WHERE student_id = ?");
        $stmt->execute([$student_pk]);
        $total_fee = $stmt->fetchColumn() ?: 0;

        // Total Paid
        $stmt = $this->pdo->prepare("SELECT SUM(amount) FROM payments WHERE student_id = ?");
        $stmt->execute([$student_pk]);
        $total_paid = $stmt->fetchColumn() ?: 0;

        return [
            'total_fee' => $total_fee,
            'total_paid' => $total_paid,
            'balance' => $total_fee - $total_paid
        ];
    }

    // 2. Get Transaction History
    public function getPaymentHistory($student_pk) {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY transaction_date DESC");
        $stmt->execute([$student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Submit Payment (Student Side)
    public function submitPayment($student_pk, $amount, $method, $ref, $purpose) {
        $sql = "INSERT INTO payments (student_id, amount, method, reference_no, purpose, transaction_date) 
                VALUES (:sid, :amt, :meth, :ref, :purp, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':sid'  => $student_pk,
            ':amt'  => $amount,
            ':meth' => $method,
            ':ref'  => $ref,
            ':purp' => $purpose
        ]);
    }
    // Get distinct School Years for the filter dropdown
    public function getStudentSchoolYears($student_pk) {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT s.school_year 
            FROM enrollments e 
            JOIN sections s ON e.section_id = s.id 
            WHERE e.student_id = ? 
            ORDER BY s.school_year DESC
        ");
        $stmt->execute([$student_pk]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Get Grades (Modified to show subjects even without grades)
    public function getStudentGrades($student_pk, $sy, $sem) {
        $sql = "SELECT 
                    sub.code, 
                    sub.description, 
                    s.section,
                    s.semester,
                    s.school_year,
                    g.quarter, 
                    g.grade 
                FROM enrollments e
                JOIN sections s ON e.section_id = s.id
                JOIN subjects sub ON s.code = sub.code
                LEFT JOIN grades g ON g.student_id = e.student_id AND g.section_id = s.id
                WHERE e.student_id = :sid 
                AND s.school_year = :sy";

        // Filter by Semester if provided (Ignore 'Whole Year' so JHS subjects always show)
        if ($sem !== 'All') {
            $sql .= " AND (s.semester = :sem OR s.semester = 'Whole Year')";
        }

        $sql .= " ORDER BY sub.code ASC";

        $stmt = $this->pdo->prepare($sql);
        $params = [':sid' => $student_pk, ':sy' => $sy];
        if ($sem !== 'All') {
            $params[':sem'] = $sem;
        }

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>