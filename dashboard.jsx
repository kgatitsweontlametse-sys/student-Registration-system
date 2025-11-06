import React, { useState, useEffect } from 'react';

// Mock data - in production this would come from a Node.js/Express API
const initial = [
  { student_id: 'S2025001', full_name: 'Alice Johnson', email: 'alice@example.edu', course: 'Computer Science', status: 'Active' },
  { student_id: 'S2025002', full_name: 'Bob Smith', email: 'bob@example.edu', course: 'Mathematics', status: 'Pending' }
];

export default function Dashboard(){
  const [students, setStudents] = useState([]);
  const [query, setQuery] = useState('');

  useEffect(() => {
    // simulate fetch
    setTimeout(()=> setStudents(initial), 200);
  }, []);

  function handleDelete(id){
    if(window.confirm('Delete ' + id + '?')){
      setStudents(students.filter(s => s.student_id !== id));
    }
  }

  const filtered = students.filter(s => {
    const q = query.toLowerCase();
    return s.full_name.toLowerCase().includes(q) || s.student_id.toLowerCase().includes(q) || s.email.toLowerCase().includes(q) || s.course.toLowerCase().includes(q);
  });

  return (
    <div>
      <div style={{marginBottom:10}}>
        <input placeholder="Search..." value={query} onChange={(e)=>setQuery(e.target.value)} />
      </div>
      <table style={{width:'100%', borderCollapse:'collapse'}}>
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          {filtered.map(s => (
            <tr key={s.student_id}>
              <td>{s.student_id}</td>
              <td>{s.full_name}</td>
              <td>{s.email}</td>
              <td>{s.course}</td>
              <td>{s.status}</td>
              <td>
                <button onClick={()=> alert('View profile of ' + s.student_id)}>View</button>
                <button onClick={()=> handleDelete(s.student_id)}>Delete</button>
              </td>
            </tr>
          ))}
          {filtered.length === 0 && <tr><td colSpan="6">No students</td></tr>}
        </tbody>
      </table>
    </div>
  );
}