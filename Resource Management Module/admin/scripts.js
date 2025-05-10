// Function to load patient data
function loadPatientData() {
    fetch('fetch_patients.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#patients-table tbody');
            tableBody.innerHTML = '';
            data.forEach(patient => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${patient.name}</td>
                    <td>${patient.treatment_type}</td>
                    <td>${patient.amount_due}</td>
                    <td>${patient.payment_status}</td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error:', error));
}

// Call the function to load data on page load
document.addEventListener('DOMContentLoaded', loadPatientData);
