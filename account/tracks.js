document.addEventListener("DOMContentLoaded", function() {
    var trackSelect = document.getElementById('grade_track');
    var deptSelect = document.getElementById('department');
    
    function updateDepartments() {
        var value = trackSelect.value;
        deptSelect.innerHTML = ""; // Clear options
        
        if (value === "Kinder") {
            deptSelect.innerHTML = '<option value="Kinder">Kinder</option>';
        } else if (value === "Highschool") {
            deptSelect.innerHTML = '<option value="Junior Highschool">Junior Highschool</option>';
        } else if (value === "SeniorHigh") {
            deptSelect.innerHTML = `
                <option value="HUMSS">Humanities and Social Sciences Strand (HUMSS)</option>
                <option value="STEM">Science, Technology, Engineering and Mathematics (STEM) Strand</option>
                <option value="ABM">Accountancy, Business and Management (ABM) Strand</option>
            `;
        }
    }
    trackSelect.addEventListener('change', updateDepartments);
    updateDepartments();
});
