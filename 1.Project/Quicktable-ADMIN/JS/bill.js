document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("billModal");
    const closeBtns = document.querySelectorAll(".close-btn, #closeBill");

    // Handle See Bill button clicks
    document.querySelectorAll(".see-bill").forEach(button => {
        button.addEventListener("click", function () {
            const tableNo = this.dataset.table;
            const token = this.dataset.token;
            const date = this.dataset.date;

            // Update modal header
            document.getElementById("modalTableNo").textContent = tableNo;
            document.getElementById("modalToken").textContent = token;
            document.getElementById("modalDate").textContent = date;

            // Fetch bill items via AJAX
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    table_no: tableNo,
                    token_number: token,
                    order_date: date
                })
            })
            .then(response => response.json())
            .then(items => {
                const tbody = document.getElementById("billItems");
                tbody.innerHTML = '';
                let grandTotal = 0;

                items.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.quantity}</td>
                        <td>₹${item.item_price}</td>
                        <td>₹${item.total_price}</td>
                    `;
                    tbody.appendChild(row);
                    grandTotal += parseFloat(item.total_price);
                });

                document.getElementById("grandTotal").textContent = grandTotal.toFixed(2);
                modal.style.display = "block";
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Handle close buttons
    closeBtns.forEach(btn => {
        btn.addEventListener("click", function () {
            modal.style.display = "none";
        });
    });

    // Close modal when clicking outside
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // Print functionality
    document.getElementById("printBill").addEventListener("click", function () {
        window.print();
    });

    // Selection mode handling
    // Replace the existing "Selection mode handling" code with this
    let selectMode = false;
    document.querySelector('.action-btn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.bill-checkbox');
        
        if (!selectMode) {
            checkboxes.forEach(checkbox => checkbox.style.display = 'inline-block');
            this.textContent = 'Delete';
            this.style.background = '#dc3545';
        } else {
            const checkedBoxes = document.querySelectorAll('.bill-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert("Please select at least one bill to delete!");
                return;
            }
            if (confirm("Are you sure you want to delete the selected bills?")) {
                // Collect data from selected bills
                const billsToDelete = Array.from(checkedBoxes).map(checkbox => {
                    const card = checkbox.closest('.bill-card');
                    const button = card.querySelector('.see-bill');
                    return {
                        table_no: button.dataset.table,
                        token_number: button.dataset.token,
                        order_date: button.dataset.date
                    };
                });

                // Send delete request to server
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'delete_bills',
                        bills: JSON.stringify(billsToDelete)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove deleted bills from the UI
                        checkedBoxes.forEach(checkbox => {
                            checkbox.closest('.bill-card').remove();
                        });
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting bills.');
                });
            }
        }
        selectMode = !selectMode;
    });
});