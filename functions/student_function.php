<?php
class Student {
    private $pdo;

    public function __construct() {
        require __DIR__ . '/db.php';
        $this->pdo = $pdo;
    }

    public function getStudentId($account_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM account WHERE account_id = :aid");
        $stmt->execute([':aid' => $account_id]);
        return $stmt->fetchColumn();
    }

    public function getProfile($student_pk) {
        $stmt = $this->pdo->prepare("SELECT s.*, a.account_id, a.fname, a.lname, a.track 
                                     FROM students s 
                                     JOIN account a ON s.student_id = a.id 
                                     WHERE s.student_id = ?");
        $stmt->execute([$student_pk]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- RESTORED: This is what fixed the error ---
    public function getEnrollment($student_pk) {
        $sql = "SELECT 
                    sub.code, sub.description, sec.section, sec.semester, sec.school_year,
                    sec.schedule_time, sec.room
                FROM enrollments e
                JOIN sections sec ON e.section_id = sec.id
                JOIN subjects sub ON sec.code = sub.code
                WHERE e.student_id = :sid
                ORDER BY sec.school_year DESC, sub.code ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sid' => $student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NEW: Get distinct School Year + Semester history for the dropdown
    public function getEnrollmentHistory($student_pk) {
        $sql = "SELECT DISTINCT s.school_year, s.semester, s.year_level
                FROM enrollments e
                JOIN sections s ON e.section_id = s.id
                WHERE e.student_id = ?
                ORDER BY s.school_year DESC, s.semester DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get Grades for a specific SY and Sem
    public function getStudentGrades($student_pk, $sy, $sem) {
        $sql = "SELECT 
                    sub.code, sub.description, s.section, s.track,
                    g.quarter, g.grade 
                FROM enrollments e
                JOIN sections s ON e.section_id = s.id
                JOIN subjects sub ON s.code = sub.code
                LEFT JOIN grades g ON g.student_id = e.student_id AND g.section_id = s.id
                WHERE e.student_id = :sid AND s.school_year = :sy";

        // Filter by Semester (unless it's 'Whole Year' which appears in all filters)
        if ($sem !== 'All') {
            $sql .= " AND (s.semester = :sem OR s.semester = 'Whole Year' OR s.semester = '')";
        }
        $sql .= " ORDER BY sub.code ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sid' => $student_pk, ':sy' => $sy, ':sem' => $sem]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get Schedule Data (Visual)
    public function getScheduleData($student_pk) {
        $sql = "SELECT sub.code, sub.description, sec.section, sec.room, sec.schedule_time
                FROM enrollments e
                JOIN sections sec ON e.section_id = sec.id
                JOIN subjects sub ON sec.code = sub.code
                WHERE e.student_id = :sid
                ORDER BY sec.school_year DESC, sub.code ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sid' => $student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getBillingSummary($student_pk) {
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) FROM assessments WHERE student_id = ?");
        $stmt->execute([$student_pk]);
        $fee = $stmt->fetchColumn() ?: 0;
        $stmt = $this->pdo->prepare("SELECT SUM(amount) FROM payments WHERE student_id = ?");
        $stmt->execute([$student_pk]);
        $paid = $stmt->fetchColumn() ?: 0;
        return ['total_fee' => $fee, 'total_paid' => $paid, 'balance' => $fee - $paid];
    }
    
    public function getPaymentHistory($student_pk) {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY transaction_date DESC");
        $stmt->execute([$student_pk]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function submitPayment($student_pk, $amount, $method, $ref, $purpose) {
        $sql = "INSERT INTO payments (student_id, amount, method, reference_no, purpose, transaction_date) VALUES (?, ?, ?, ?, ?, NOW())";
        return $this->pdo->prepare($sql)->execute([$student_pk, $amount, $method, $ref, $purpose]);
    }
}
?>