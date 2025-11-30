<?php
class Student {
    private $pdo;

    public function __construct() {
        // Caveman Style Connection
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

    // 1. Get Student ID from Account ID
    public function getStudentId($account_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM account WHERE account_id = :aid");
        $stmt->execute([':aid' => $account_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id'] : false;
    }

    // 2. Get Profile (FIXED: Removed a.email)
    public function getProfile($student_pk) {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.account_id, a.fname, a.mname, a.lname, 
                a.date_enrolled, a.track,
                s.* FROM students s
            JOIN account a ON s.student_id = a.id
            WHERE s.student_id = ?
        ");
        $stmt->execute([$student_pk]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. Get Enrollment (Subjects)
    public function getEnrollment($student_pk) {
        $sql = "SELECT 
                    sub.code, sub.description, sec.section, sec.semester, sec.school_year,
                    sec.schedule_time, sec.room
                FROM enrollments e
                JOIN sections sec ON e.section_id = sec.id
                JOIN subjects sub ON sec.code = sub.code
                WHERE e.student_id = :sid
                ORDER BY sub.code ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sid' => $student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Get Schedule (Visual Grid Data)
    public function getSchedule($student_pk) {
        // Re-using the enrollment query logic but just returning it for the schedule page to parse
        return $this->getEnrollment($student_pk);
    }

    // 5. Get School Years (For Grade Filtering)
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

    // 6. Get Grades (Academic)
    public function getStudentGrades($student_pk, $sy, $sem) {
        $sql = "SELECT 
                    sub.code, sub.description, s.section, s.semester, s.school_year,
                    g.quarter, g.grade 
                FROM enrollments e
                JOIN sections s ON e.section_id = s.id
                JOIN subjects sub ON s.code = sub.code
                LEFT JOIN grades g ON g.student_id = e.student_id AND g.section_id = s.id
                WHERE e.student_id = :sid AND s.school_year = :sy";

        if ($sem !== 'All') {
            $sql .= " AND (s.semester = :sem OR s.semester = 'Whole Year')";
        }
        $sql .= " ORDER BY sub.code ASC";

        $stmt = $this->pdo->prepare($sql);
        $params = [':sid' => $student_pk, ':sy' => $sy];
        if ($sem !== 'All') $params[':sem'] = $sem;

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 7. Get Behavior (Attendance/Conduct)
    public function getStudentBehavior($student_pk) {
        $sql = "SELECT 
                    sub.code, 
                    b.grading_period, 
                    b.attendance_score, 
                    b.conduct_grade 
                FROM enrollments e
                JOIN sections s ON e.section_id = s.id
                JOIN subjects sub ON s.code = sub.code
                JOIN behavior_records b ON b.student_id = e.student_id AND b.section_id = s.id
                WHERE e.student_id = ?
                ORDER BY sub.code ASC, b.grading_period ASC";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. Billing: Get Summary
    public function getBillingSummary($student_pk) {
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) FROM assessments WHERE student_id = ?");
        $stmt->execute([$student_pk]);
        $total_fee = $stmt->fetchColumn() ?: 0;

        $stmt = $this->pdo->prepare("SELECT SUM(amount) FROM payments WHERE student_id = ?");
        $stmt->execute([$student_pk]);
        $total_paid = $stmt->fetchColumn() ?: 0;

        return [
            'total_fee' => $total_fee,
            'total_paid' => $total_paid,
            'balance' => $total_fee - $total_paid
        ];
    }

    // 9. Billing: Get History
    public function getPaymentHistory($student_pk) {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY transaction_date DESC");
        $stmt->execute([$student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 10. Billing: Submit Payment
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
}
?>