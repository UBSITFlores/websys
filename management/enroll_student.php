<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="enroll_student.css">
</head>
<h2>Enroll New Student</h2>
<form method="POST" action="register_student.php" autocomplete="off" id="enrollForm">
  <label>First Name: <input name="fname" required></label>
  <label>Middle Name: <input name="mname" required></label>
  <label>Last Name: <input name="lname" required></label>
  <label>Suffix: <input name="suffix"></label>
  <label>Date of Birth: <input type="date" name="birthdate" required></label>
  <label>Birthplace: <input name="birthplace"></label>
  <label>Religion: <input name="religion"></label>
  <label>Civil Status: <input name="civilstatus"></label>
  <label>Nationality: <input name="nationality"></label>
  <label>Gender:
    <select name="gender">
      <option value="">Select…</option>
      <option value="Cisgender">Cisgender</option>
      <option value="Transgender">Transgender</option>
      <option value="Non-binary">Non-binary</option>
      <option value="Other">Other</option>
    </select>
  </label>
  <label>Sex:
    <select name="sex">
      <option value="">Select…</option>
      <option value="Female">Female</option>
      <option value="Male">Male</option>
      <option value="Intersex">Intersex</option>
      <option value="Other">Other</option>
    </select>
  </label>
  <label>First Generation Student:
    <select name="first_gen_question">
      <option value="">Select…</option>
      <option value="Yes">Yes</option>
      <option value="No">No</option>
    </select>
  </label>
  <label>Ethnicity: <input name="ethnicity"></label>
  <label>Mobile: <input name="contactno" required></label>
  <label>Email: <input name="email" type="email"></label>
  <label>House No/Street: <input name="housenum_street"></label>
  <label>Barangay: <input name="barangay"></label>
  <label>City: <input name="city"></label>
  <label>Province: <input name="province"></label>
  <label>ZIP: <input name="zipcode"></label>
  <label>Year Graduated: <input name="year_graduated"></label>
  <label>School Name: <input name="sLast"></label>
  <label>School Street: <input name="sStreet"></label>
  <label>School Barangay: <input name="sBarangay"></label>
  <label>School City: <input name="sCity"></label>
  <label>School Province: <input name="sProvince"></label>
  <label>School ZIP: <input name="sZipcode"></label>
  <label>Guardian Last Name: <input name="gLname"></label>
  <label>Guardian First Name: <input name="gFname"></label>
  <label>Guardian Middle Name: <input name="gMName"></label>
  <label>Guardian Contact: <input name="gContactnum"></label>
  <label>Guardian Occupation: <input name="gOccupation"></label>
  <label>Guardian Address: <input name="gAddress"></label>
  <label>Guardian Relationship: <input name="gRelationship"></label>
  <label>Mother Last Name: <input name="mLname"></label>
  <label>Mother First Name: <input name="mFname"></label>
  <label>Mother Middle Name: <input name="mMName"></label>
  <label>Mother Contact: <input name="mContactnum"></label>
  <label>Mother Occupation: <input name="mOccupation"></label>
  <label>Mother Address: <input name="mAddress"></label>
  <label>Father Last Name: <input name="fLname"></label>
  <label>Father First Name: <input name="fFname"></label>
  <label>Father Middle Name: <input name="fMName"></label>
  <label>Father Contact: <input name="fContactnum"></label>
  <label>Father Occupation: <input name="fOccupation"></label>
  <label>Father Address: <input name="fAddress"></label>
  <label>Track:
    <select name="track" required>
      <option value="">Select track</option>
      <option value="kinder">Kindergarten</option>
      <option value="junior high school">Junior High School</option>
      <option value="senior high school">Senior High School</option>
    </select>
  </label>
  <button type="submit" name="submit">Register Student</button>
</form>
