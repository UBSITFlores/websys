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

    public function addPersonalInfo($personalinfo) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO personalinfo (
                familyname, fname, mname, suffix, birthdate, birthplace, religion, civilstatus, nationality, contactno, email, housenum_street, barangay, city, province, zipcode, name_of_school, add_of_school, year_graduated, sLast, sStreet, sBarangay, sCity, sProvince, sZipcode, pept, als_refnum, gLname, gFname, gMname, gContactnum, gOccupation, gAddress, gRelationship, mLname, mFname, mMname, mContactnum, mOccupation, mAddress, fLname, fFname, fMname, fContactnum, fOccupation, fAddress, ethnicity
            ) VALUES (
                :familyname, :fname, :mname, :suffix, :birthdate, :birthplace, :religion, :civilstatus, :nationality, :contactno, :email, :housenum_street, :barangay, :city, :province, :zipcode, :name_of_school, :add_of_school, :year_graduated, :sLast, :sStreet, :sBarangay, :sCity, :sProvince, :sZipcode, :pept, :als_refnum, :gLname, :gFname, :gMname, :gContactnum, :gOccupation, :gAddress, :gRelationship, :mLname, :mFname, :mMname, :mContactnum, :mOccupation, :mAddress, :fLname, :fFname, :fMname, :fContactnum, :fOccupation, :fAddress, :ethnicity
            )");
            $stmt->execute($personalinfo);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
?>
