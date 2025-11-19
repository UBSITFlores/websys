<?php 

    class instructor{
        private $pdo;

        function __construct() {
            $host="localhost";
            $username="root";
            $password="";
            $dbname="portal";
            $charset="utf8mb4";

            try {
                $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
                $connection = new PDO($dsn, $username, $password);
                $this->pdo = $connection;
            } catch (PDOException $e) {
                die("Connection Failed! " . $e->getMessage());
            }
        }
        public function getSections($instructorId, $semester, $schoolYear) {
        $query = "SELECT s.section, s.code, s.description, s.last_transaction, s.finalized
                  FROM sections s
                  WHERE s.instructor_id = :instructorId
                    AND s.semester = :semester
                    AND s.school_year = :schoolYear
                  ORDER BY s.last_transaction DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':instructorId' => $instructorId,
            ':semester' => $semester,
            ':schoolYear' => $schoolYear
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    // Fetch available semesters (dynamic dropdown)
        public function getSemesters() {
        $query = "SELECT DISTINCT semester FROM sections ORDER BY semester ASC";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

    // Fetch available school years (dynamic dropdown)
        public function getSchoolYears() {
        $query = "SELECT DISTINCT school_year FROM sections ORDER BY school_year DESC";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }

?>