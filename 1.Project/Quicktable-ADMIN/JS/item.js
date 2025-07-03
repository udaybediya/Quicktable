function editItem(sr_no) {
    let row = document.querySelector(`tr[data-id='${sr_no}']`);
    let cells = row.querySelectorAll("td");

    let itemName = cells[1].innerText;
    let itemPrice = cells[2].innerText;
    let itemType = cells[3].innerText;

    let itemTypes = ["SOUTH INDIAN", "PUNJABI", "GUJARATI", "ROTIES & TANDURI", "BEVERAGES", "SALAD’S & PAPAD"];

    if (!confirm(`Are you sure you want to edit "${itemName}"?`)) return;

    // Convert fields to input elements
    cells[1].innerHTML = `<input type="text" value="${itemName}" id="edit-name-${sr_no}" class="edit-input">`;
    cells[2].innerHTML = `<input type="number" value="${itemPrice}" id="edit-price-${sr_no}" class="edit-input">`;

    let selectOptions = itemTypes.map(type => 
        `<option value="${type}" ${type === itemType ? "selected" : ""}>${type}</option>`
    ).join("");

    cells[3].innerHTML = `<select id="edit-type-${sr_no}" class="edit-select">${selectOptions}</select>`;

    // Create Save button
    let saveButton = document.createElement("button");
    saveButton.classList.add("save-btn");
    saveButton.innerHTML = `<i class='bx bx-save'></i>`;
    saveButton.addEventListener("click", function () {
        saveItem(sr_no);
    });


    // Replace edit button with save and cancel buttons
    cells[4].innerHTML = "";
    cells[4].appendChild(saveButton);
    cells[4].appendChild(cancelButton);
}

// Function to cancel edit and revert back to original data
function cancelEdit(sr_no, itemName, itemPrice, itemType) {
    let row = document.querySelector(`tr[data-id='${sr_no}']`);
    let cells = row.querySelectorAll("td");

    cells[1].innerHTML = itemName;
    cells[2].innerHTML = itemPrice;
    cells[3].innerHTML = itemType;
    
    cells[4].innerHTML = `<button class='edit-btn' onclick='editItem(${sr_no})'><i class='bx bx-edit'></i></button>`;
}


function saveItem(sr_no) {
    let name = document.getElementById(`edit-name-${sr_no}`).value;
    let price = document.getElementById(`edit-price-${sr_no}`).value;
    let type = document.getElementById(`edit-type-${sr_no}`).value;

    fetch("item.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=update&sr_no=${sr_no}&item_name=${encodeURIComponent(name)}&item_price=${price}&item_type=${encodeURIComponent(type)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert(data.message);
            location.reload(); // Refresh page to show updated values
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}


function deleteItem(sr_no) {
    if (!confirm("Are you sure you want to delete this item?")) return;

    fetch("demo.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=delete&sr_no=${sr_no}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert(data.message);
            location.reload(); // Reload to reflect deletion
        } else {
            alert("Delete failed!");
        }
    })
    .catch(error => console.error("Error:", error));
}


document.addEventListener("DOMContentLoaded", function () {
    const addItemButton = document.getElementById("add-new-item-btn");
    const tableBody = document.getElementById("item-table-body");
    let isAddingNewRow = false; // Track if a new row is already being added

    addItemButton.addEventListener("click", function () {
        if (isAddingNewRow) {
            alert("Please save or cancel the current entry before adding a new one.");
            return;
        }

        // Find the last Sr. No in the table and calculate the next one
        let lastSrNo = 0;
        const rows = tableBody.querySelectorAll("tr");
        if (rows.length > 0) {
            lastSrNo = parseInt(rows[rows.length - 1].querySelector("td:first-child").textContent) || 0;
        }
        let newSrNo = lastSrNo + 1;

        // Create a new row
        const newRow = document.createElement("tr");
        newRow.innerHTML = `
            <td>${newSrNo}</td>
            <td><input type="text" class="item-input" placeholder="Enter Item Name"></td>
            <td><input type="number" class="item-input" placeholder="Enter Price"></td>
            <td>
                <select class="item-dropdown">
                    <option value="">Select Type</option>
                    <option value="SOUTH INDIAN">SOUTH INDIAN</option>
                    <option value="PUNJABI">PUNJABI</option>
                    <option value="GUJARATI">GUJARATI</option>
                    <option value="ROTIES & TANDURI">ROTIES & TANDURI</option>
                    <option value="BEVERAGES">BEVERAGES</option>
                    <option value="SALAD’S & PAPAD">SALAD’S & PAPAD</option>
                </select>
            </td>
            <td><button class="save-btn">✔</button></td>
            <td><button class="cancel-btn">✖</button></td>
        `;

        // Append row at the end of the table
        tableBody.appendChild(newRow);
        isAddingNewRow = true; // Mark that a new row is being added

        // Handle save action
        newRow.querySelector(".save-btn").addEventListener("click", function () {
            saveNewItem(newRow, newSrNo);
        });

        // Handle cancel action
        newRow.querySelector(".cancel-btn").addEventListener("click", function () {
            newRow.remove();
            isAddingNewRow = false; // Allow adding a new row again
        });
    });

    function saveNewItem(row, srNo) {
        const itemNameInput = row.querySelector("input[type='text']");
        const itemPriceInput = row.querySelector("input[type='number']");
        const itemTypeSelect = row.querySelector("select");

        const itemName = itemNameInput.value.trim();
        const itemPrice = itemPriceInput.value.trim();
        const itemType = itemTypeSelect.value;

        // Validation: Check if all fields are filled
        if (!itemName) {
            alert("Please enter the item name.");
            itemNameInput.focus();
            return;
        }
        if (!itemPrice) {
            alert("Please enter the item price.");
            itemPriceInput.focus();
            return;
        }
        if (!itemType) {
            alert("Please select an item type.");
            itemTypeSelect.focus();
            return;
        }

        // Send data to PHP via AJAX
        fetch("item.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=add&item_name=${encodeURIComponent(itemName)}&item_price=${itemPrice}&item_type=${encodeURIComponent(itemType)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                // Replace input row with actual row
                row.innerHTML = `
                    <td>${srNo}</td>
                    <td>${itemName}</td>
                    <td>${itemPrice}</td>
                    <td>${itemType}</td>
                    <td><button class='edit-btn' onclick='editItem(${srNo})'><i class='bx bx-edit'></i></button></td>
                    <td><button class='delete-btn' onclick='deleteItem(${srNo})'><i class='bx bx-trash'></i></button></td>
                `;
                isAddingNewRow = false; // Allow adding another row
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    }
});



function deleteItem(sr_no, item_name) {
    if (confirm(`Are you sure you want to delete ${item_name}?`)) {
        fetch("item.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=delete&sr_no=${sr_no}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert(data.message);
                location.reload(); // Refresh page after deletion
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    }
}