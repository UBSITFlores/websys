<?php
class PersonalInfoHandler {
    private $pdo;

    function __construct(){
        $host = "localhost";
        $user = "root";
        $pass = "";
        $dbname = "portal";
        $charset = "utf8mb4";

        try{
            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $connection = new PDO($dsn, $user, $pass);
            $this->pdo = $connection;
            echo "Connected successfully";
        } catch(PDOException $e){
            die("Connection Failed! " . $e->getMessage());
        }
    }

    public function enroll($personalinfo) {
    try {
        $stmt = $this->pdo->prepare("
            INSERT INTO personalinfo (
                account_id, track, date_enrolled, familyname, fname, mname, suffix, birthdate, birthplace,
                religion, civilstatus, nationality, gender, sex, first_gen_question, ethnicity, contactno, email,
                housenum_street, barangay, city, province, zipcode,
                sLast, sStreet, sBarangay, sCity, sProvince, sZipcode, year_graduated,
                gLname, gFname, gMname, gContactnum, gOccupation, gAddress, gRelationship,
                mLname, mFname, mMname, mContactnum, mOccupation, mAddress,
                fLname, fFname, fMname, fContactnum, fOccupation, fAddress
            ) VALUES (
                :account_id, :track, :date_enrolled, :familyname, :fname, :mname, :suffix, :birthdate, :birthplace,
                :religion, :civilstatus, :nationality, :gender, :sex, :first_gen_question, :ethnicity, :contactno, :email,
                :housenum_street, :barangay, :city, :province, :zipcode,
                :sLast, :sStreet, :sBarangay, :sCity, :sProvince, :sZipcode, :year_graduated,
                :gLname, :gFname, :gMname, :gContactnum, :gOccupation, :gAddress, :gRelationship,
                :mLname, :mFname, :mMname, :mContactnum, :mOccupation, :mAddress,
                :fLname, :fFname, :fMname, :fContactnum, :fOccupation, :fAddress
            )
        ");
            $stmt->execute($personalinfo);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
?>
