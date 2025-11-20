<?php
include '../functions/process_function.php';

if(isset($_POST['submit'])){
    $personalinfo = [
        ':familyname'        => $_POST['familyname'] ?? '',
        ':fname'             => $_POST['fname'] ?? '',
        ':mname'             => $_POST['mname'] ?? '',
        ':suffix'            => $_POST['suffix'] ?? '',
        ':birthdate'         => $_POST['birthdate'] ?? '',
        ':birthplace'        => $_POST['birthplace'] ?? '',
        ':religion'          => $_POST['religion'] ?? '',
        ':civilstatus'       => $_POST['civilstatus'] ?? '',
        ':nationality'       => $_POST['nationality'] ?? '',
        ':contactno'         => $_POST['mobile'] ?? '',
        ':email'             => $_POST['email'] ?? '',
        ':housenum_street'   => $_POST['current_street'] ?? '',
        ':barangay'          => $_POST['current_barangay'] ?? '',
        ':city'              => $_POST['current_city'] ?? '',
        ':province'          => $_POST['current_province'] ?? '',
        ':zipcode'           => $_POST['current_zip'] ?? '',
        // Only the "last school attended" block
        ':sLast'             => $_POST['school_last'] ?? '',
        ':sStreet'           => $_POST['school_street'] ?? '',
        ':sBarangay'         => $_POST['school_barangay'] ?? '',
        ':sCity'             => $_POST['school_city'] ?? '',
        ':sProvince'         => $_POST['school_province'] ?? '',
        ':sZipcode'          => $_POST['school_zip'] ?? '',
        ':year_graduated'    => $_POST['year_graduated'] ?? '',
        // No :pept, :als_refnum, :name_of_school, :add_of_school
        // Guardian
        ':gLname'            => $_POST['guardian_last'] ?? '',
        ':gFname'            => $_POST['guardian_first'] ?? '',
        ':gMname'            => $_POST['guardian_middle'] ?? '',
        ':gContactnum'       => $_POST['guardian_contact'] ?? '',
        ':gOccupation'       => $_POST['guardian_occupation'] ?? '',
        ':gAddress'          => $_POST['guardian_address'] ?? '',
        ':gRelationship'     => $_POST['guardian_relationship'] ?? '',
        // Mother
        ':mLname'            => $_POST['mother_last'] ?? '',
        ':mFname'            => $_POST['mother_first'] ?? '',
        ':mMname'            => $_POST['mother_middle'] ?? '',
        ':mContactnum'       => $_POST['mother_contact'] ?? '',
        ':mOccupation'       => $_POST['mother_occupation'] ?? '',
        ':mAddress'          => $_POST['mother_addr'] ?? '',
        // Father
        ':fLname'            => $_POST['father_last'] ?? '',
        ':fFname'            => $_POST['father_first'] ?? '',
        ':fMname'            => $_POST['father_middle'] ?? '',
        ':fContactnum'       => $_POST['father_contact'] ?? '',
        ':fOccupation'       => $_POST['father_occupation'] ?? '',
        ':fAddress'          => $_POST['father_addr'] ?? '',
        ':ethnicity'         => $_POST['ethnicity'] ?? ''
    ];

    $info = new PersonalInfoHandler();
    $result = $info->addPersonalInfo($personalinfo);

    if($result === true){
        echo '<script>alert("Success: Your information has been saved.");</script>';
    } else {
        echo '<script>alert("Error: ' . addslashes($result) . '");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SLU Pacdal - Application Details</title>
  <link rel="stylesheet" href="process.css">
</head>
<body>
  <form class="app-form" method="POST">
    <div class="form-section">
        <h2>I. PERSONAL INFORMATION</h2>
        <div class="fields">
            <div class="field-group">
            <label for="family_name">* Family Name:</label>
            <input type="text" id="familyname" name="familyname" required>            </div>
            <div class="field-group">
            <label for="first_name">* First Name:</label>
            <input type="text" id="first_name" name="fname" required>
            </div>
            <div class="field-group">
            <label for="middle_name">* Middle Name:</label>
            <input type="text" id="middle_name" name="mname" required>
            </div>
            <div class="field-group">
            <label for="suffix">* Suffix:</label>
            <input type="text" id="suffix" name="suffix" required>
            </div>
            <div class="field-group">
            <label for="birthdate">* Birthdate:</label>
            <input type="date" id="birthdate" name="birthdate" required>
            </div>
        </div>
        <div class="fields">
            <div class="field-group">
            <label for="birthplace">* Birthplace:</label>
            <input type="text" id="birthplace" name="birthplace" required>
            </div>
            <div class="field-group">
            <label for="religion">* Religion:</label>
            <input type="text" id="religion" name="religion" required>
            </div>
            <div class="field-group">
            <label for="civil_status">* Civil Status:</label>
            <select id="civil_status" name="civilstatus" required>
                <option value="">Select...</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Separated">Separated</option>
                <option value="Widowed">Widowed</option>
                <option value="Other">Other</option>
            </select>
            </div>
            <div class="field-group">
            <label for="nationality">* Nationality:</label>
            <select id="nationality" name="nationality" required>
                <option value="">Select...</option>
                <option value="Filipino">Filipino</option>
                <option value="American">American</option>
                <option value="Chinese">Chinese</option>
                <option value="Korean">Korean</option>
                <option value="Japanese">Japanese</option>
                <option value="Indian">Indian</option>
                <option value="Other">Other</option>
            </select>
            </div>
        </div>
        <!-- Gender and Sex Row -->
        <div class="fields">
            <div class="field-group">
            <label for="gender">* Gender:</label>
            <select id="gender" name="gender" required>
                <option value="">Select...</option>
                <option value="Cisgender">Cisgender (Cis)</option>
                <option value="Transgender">Transgender</option>
                <option value="Non-binary">Non-binary</option>
                <option value="Other">Other/Prefer not to say</option>
            </select>
            <small class="help-text">Someone whose gender identity corresponds with sex they have at birth.</small>
            </div>
            <div class="field-group">
            <label for="sex">* SEX:</label>
            <select id="sex" name="sex" required>
                <option value="">Select...</option>
                <option value="Female">Female</option>
                <option value="Male">Male</option>
                <option value="Intersex">Intersex</option>
                <option value="Other">Other</option>
            </select>
            <small class="help-text">Personal identification of one's own gender</small>
            </div>
        </div>
        <!-- First Generation and Ethnicity -->
        <div class="fields">
            <div class="field-group">
            <label for="firstgen">* Are you a first generation student:</label>
            <select id="firstgen" name="firstgen" required>
                <option value="">Select...</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
            <small class="help-text">If you are the first person in your immediate family to attend a university at any level</small>
            </div>
            <div class="field-group">
            <label for="ethnicity">* ETHNICITY:</label>
            <input type="text" id="ethnicity" name="ethnicity" required placeholder="Based on your family language, please specify the ethnic group you belong to.">
            </div>
        </div>
        </div>


    <div class="form-section">
      <h2>II. CONTACT INFORMATION</h2>
      <div class="fields">
        <div class="field-group">
          <label for="mobile">* Mobile No:</label>
          <input type="text" id="mobile" name="mobile" required>
        </div>
        <div class="field-group">
          <label for="email">* Email Address:</label>
          <input type="email" id="email" name="email" required>
        </div>
      </div>
      <div class="fields" style="margin-top:1rem;">
        <div class="field-group">
          <label for="current_street">* House Number & Street:</label>
          <input type="text" id="current_street" name="current_street" required>
        </div>
        <div class="field-group">
          <label for="current_barangay">* Barangay:</label>
          <input type="text" id="current_barangay" name="current_barangay" required>
        </div>
        <div class="field-group">
          <label for="current_city">* City/Municipality:</label>
          <input type="text" id="current_city" name="current_city" required>
        </div>
        <div class="field-group">
          <label for="current_province">* Province:</label>
          <input type="text" id="current_province" name="current_province" required>
        </div>
        <div class="field-group">
          <label for="current_zip">* ZIP Code:</label>
          <input type="text" id="current_zip" name="current_zip" required>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h2>III. EDUCATIONAL BACKGROUND</h2>
      <div class="subsection">
        <label><input type="checkbox" name="elem_check"> Elementary School</label>
        <div class="fields">          
          <input type="text" name="year_graduated" placeholder="Year Graduated">
        </div>
      </div>
      <div class="fields">
        <div class="field-group">
          <label for="school_last">* Last School Attended:</label>
          <input type="text" id="school_last" name="school_last" required placeholder="Full school name">
        </div>
      </div>
      <div class="fields" style="margin-top:1rem;">
        <div class="field-group">
          <label for="school_street">* Street:</label>
          <input type="text" id="school_street" name="school_street" required>
        </div>
        <div class="field-group">
          <label for="school_barangay">* Barangay:</label>
          <input type="text" id="school_barangay" name="school_barangay" required>
        </div>
        <div class="field-group">
          <label for="school_city">* City/Municipality:</label>
          <input type="text" id="school_city" name="school_city" required>
        </div>
        <div class="field-group">
          <label for="school_province">* Province:</label>
          <input type="text" id="school_province" name="school_province" required>
        </div>
        <div class="field-group">
          <label for="school_zip">* ZIP Code:</label>
          <input type="text" id="school_zip" name="school_zip" required>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h2>IV. ALTERNATIVE LEARNING SYSTEM (ALS)</h2>
      <div class="fields">
        <input type="text" name="pept_serial" placeholder="PEPT (Serial/Ref Number)">
        <input type="text" name="als_serial" placeholder="ALS (Serial/Ref Number)">
      </div>
    </div>

    <div class="form-section">
      <h2>V. GUARDIAN INFORMATION</h2>
      <div class="fields">
        <div class="field-group">
          <label>* Last Name:</label>
          <input type="text" name="guardian_last" required>
        </div>
        <div class="field-group">
          <label>* First Name:</label>
          <input type="text" name="guardian_first" required>
        </div>
        <div class="field-group">
          <label>* Middle Name:</label>
          <input type="text" name="guardian_middle" required>
        </div>
        <div class="field-group">
          <label>* Contact Number:</label>
          <input type="text" name="guardian_contact" required>
        </div>
        <div class="field-group">
          <label>* Occupation:</label>
          <input type="text" name="guardian_occupation" required>
        </div>
      </div>
      <div class="fields">
        <div class="field-group">
          <label>* Guardian Address:</label>
          <input type="text" name="guardian_address" required>
        </div>
        <div class="field-group">
          <label>* Relationship:</label>
          <input type="text" name="guardian_relationship" required>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h2>VI. PARENTS INFORMATION</h2>
      <h3>MOTHER'S INFORMATION</h3>
      <div class="fields">
        <input type="text" name="mother_last" required placeholder="* Last Name">
        <input type="text" name="mother_first" required placeholder="* First Name">
        <input type="text" name="mother_middle" required placeholder="* Middle Name">
        <input type="text" name="mother_contact" required placeholder="* Contact No">
        <input type="text" name="mother_occupation" required placeholder="* Occupation">
        <input type="text" name="mother_addr" required placeholder="* Address">
      </div>
      <h3>FATHER'S INFORMATION</h3>
      <div class="fields">
        <input type="text" name="father_last" required placeholder="* Last Name">
        <input type="text" name="father_first" required placeholder="* First Name">
        <input type="text" name="father_middle" required placeholder="* Middle Name">
        <input type="text" name="father_contact" required placeholder="* Contact No">
        <input type="text" name="father_occupation" required placeholder="* Occupation">
        <input type="text" name="father_addr" required placeholder="* Address">
      </div>
    </div>

    <!-- BUTTONS -->
    <div class="form-actions">
      <button type="button" class="cancel-btn" onclick="window.location.href='../account/login.php';">Cancel</button>
      <button type="submit" name="submit" class="submit-btn">Submit</button>    </div>
  </form>
</body>
</html>
