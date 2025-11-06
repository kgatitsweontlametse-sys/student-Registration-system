// scripts.js - client side utilities

// Confirm before delete
function confirmDelete(studentId, fullname){
  if(confirm("Delete student " + fullname + " (ID: " + studentId + ")? This action is irreversible.")){
    // navigate to delete handler
    window.location = "delete.php?id=" + encodeURIComponent(studentId);
  }
}

// Simple client-side table search/filter
function filterTable(){
  const q = document.getElementById('searchInput').value.toLowerCase();
  const rows = document.querySelectorAll('#studentsTable tbody tr');
  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.indexOf(q) > -1 ? '' : 'none';
  });
}

// Basic front-end form validation (supplemental)
function validateRegisterForm(){
  const fullName = document.getElementById('full_name').value.trim();
  const studentId = document.getElementById('student_id').value.trim();
  const email = document.getElementById('email').value.trim();
  if(!fullName || !studentId || !email){
    alert('Please fill in Full Name, Student ID and Email.');
    return false;
  }
  return true;
}