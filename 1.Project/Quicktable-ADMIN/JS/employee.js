// Toggle Employee Details
function toggleDetails(button) {
    let details = button.closest('.employee-card').querySelector('.details');
    let icon = button.querySelector('i');
    details.classList.toggle('show');
    icon.classList.toggle('bxs-up-arrow');
    icon.classList.toggle('bxs-down-arrow');
}

// Enable Edit Mode
function enableEdit(button) {
    let card = button.closest('.employee-card');
    let detailsInfo = card.querySelector('.details-info');

    detailsInfo.querySelectorAll('p').forEach(p => {
        let text = p.querySelector('span').textContent.trim();
        let key = p.querySelector('strong').textContent.replace(':', '').trim();

        if (key === "Gender") {
            p.innerHTML = `<strong>Gender:</strong> 
                <select class="edit-field" data-original-value="${text}">
                    <option value="Male" ${text === "Male" ? "selected" : ""}>Male</option>
                    <option value="Female" ${text === "Female" ? "selected" : ""}>Female</option>
                </select>`;
        } else if (key === "Date of birth") {
            let dateParts = text.split('/');
            let formattedDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
            p.innerHTML = `<strong>Date of birth:</strong> 
                <input type="date" class="edit-field" value="${formattedDate}" data-original-value="${formattedDate}">`;
        } else {
            p.innerHTML = `<strong>${key}:</strong> 
                <input type="text" class="edit-field" value="${text}" data-original-value="${text}">`;
        }
    });

    let actions = card.querySelector('.details-actions');
    actions.innerHTML = `
        <button class="btn save-btn" onclick="saveDetails(this)">Save</button>
        <button class="btn cancel-btn" onclick="cancelEdit(this)">Cancel</button>
    `;
}

// Save Employee Details
function saveDetails(button) {
    let card = button.closest('.employee-card');
    let srNo = card.querySelector('.sr-no').textContent.replace('.', '').trim();
    let employeeName = card.querySelector('.employee-name').textContent.trim();

    let formData = new FormData();
    formData.append('update_employee', 1); // Correct flag for update
    formData.append('sr_no', srNo); 

    let changes = [];

    card.querySelectorAll('.edit-field').forEach(input => {
        let key = input.closest('p').querySelector('strong').textContent.replace(':', '').trim();
        let fieldName = key.replace(" ", "_").toLowerCase(); // Convert field name to match database columns
        let oldValue = input.dataset.originalValue || "";
        let newValue = input.value.trim();

        if (newValue !== oldValue) { 
            formData.append(fieldName, newValue);
            changes.push(`${key}: ${oldValue} → ${newValue}`);
        }
    });

    if (changes.length === 0) {
        alert("No changes detected.");
        return;
    }

    if (!confirm(`Are you sure you want to update ${employeeName}?\n\nChanges:\n${changes.join("\n")}`)) {
        return;
    }

    fetch('', { 
        method: 'POST',
        body: formData 
    }).then(response => response.text())
    .then(data => {
        console.log(data);
        window.location.reload(); // Reload after successful update
    }).catch(error => console.error('Error:', error));
}

 // Cancel Edit Mode
function cancelEdit(button) {
    let card = button.closest('.employee-card');
    let detailsInfo = card.querySelector('.details-info');

    // Revert back to original text values
    detailsInfo.querySelectorAll('.edit-field').forEach(input => {
        let originalValue = input.dataset.originalValue || "";
        let key = input.closest('p').querySelector('strong').textContent.replace(':', '').trim();

        if (key === "Gender") {
            input.closest('p').innerHTML = `<strong>Gender:</strong> <span>${originalValue}</span>`;
        } else if (key === "Date of birth") {
            let dateParts = originalValue.split('-');
            let formattedDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
            input.closest('p').innerHTML = `<strong>Date of birth:</strong> <span>${formattedDate}</span>`;
        } else {
            input.closest('p').innerHTML = `<strong>${key}:</strong> <span>${originalValue}</span>`;
        }
    });

    // Restore original buttons
    let actions = card.querySelector('.details-actions');
    actions.innerHTML = `
        <button class="btn update-btn" onclick="enableEdit(this)">Update</button>
        <button class="btn delete-btn" onclick="deleteEmployee(${card.querySelector('.sr-no').textContent.replace('.', '')})">Delete</button>
    `;
}

// Delete Employee
function deleteEmployee(srNo) {
    if (confirm("Are you sure you want to delete this employee?")) {
        let formData = new FormData();
        formData.append('delete_employee', 1);
        formData.append('sr_no', srNo);

        fetch('', {
            method: 'POST',
            body: formData
        }).then(response => response.text())
        .then(data => {
            console.log(data);
            window.location.reload(); // Reload after successful deletion
        }).catch(error => console.error('Error:', error));
    }
}

// Show Popup Form
document.getElementById('add-new-item-btn').addEventListener('click', () => {
    document.getElementById('overlay').style.display = 'flex';
});
 // Show Popup Form
 document.getElementById('add-new-item-btn').addEventListener('click', () => {
    document.getElementById('overlay').style.display = 'flex';
});

// Close Popup Form
function closePopup() {
    document.getElementById('overlay').style.display = 'none';
}

// Handle Form Submission for Adding New Employee
document.getElementById('employeeForm').addEventListener('submit', function (e) {
    e.preventDefault();

    let formData = new FormData(this);
    formData.append('add_employee', 1);

    fetch('', {
        method: 'POST',
        body: formData
    }).then(response => response.text()).then(() => {
        window.location.reload();
    });
});
