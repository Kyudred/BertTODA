document.addEventListener('DOMContentLoaded', function() {
    // Tab Navigation
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to current button and content
            button.classList.add('active');
            document.getElementById(tabId).classList.add('active');
            
            // Update URL hash for direct access
            window.location.hash = tabId;
        });
    });
    
    // Check URL hash on page load
    const hash = window.location.hash.substring(1);
    if (hash) {
        const activeTab = document.querySelector(`.tab-button[data-tab="${hash}"]`);
        if (activeTab) {
            activeTab.click();
        }
    }
    
    // Modal functionality
    const modals = {
        'log-use': document.getElementById('log-use-modal'),
        'log-return': document.getElementById('log-return-modal'),
        'view-details': document.getElementById('view-details-modal')
    };
    
    // Open Log Use Modal
    const logUseButtons = document.querySelectorAll('.log-use-btn');
    logUseButtons.forEach(button => {
        button.addEventListener('click', () => {
            const itemId = button.getAttribute('data-id');
            const itemName = button.getAttribute('data-name');
            
            document.getElementById('use-item-id').value = itemId;
            document.getElementById('use-item-name').value = itemName;
            
            // Set today as default borrow date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('borrow-date').value = today;
            
            // Set default expected return date (7 days from now)
            const nextWeek = new Date();
            nextWeek.setDate(nextWeek.getDate() + 7);
            document.getElementById('expected-return-date').value = nextWeek.toISOString().split('T')[0];
            
            modals['log-use'].classList.add('active');
        });
    });
    
    // Open Log Return Modal
    const logReturnButtons = document.querySelectorAll('.log-return-btn');
    logReturnButtons.forEach(button => {
        button.addEventListener('click', () => {
            const itemId = button.getAttribute('data-id');
            const itemName = button.getAttribute('data-name');
            
            document.getElementById('return-item-id').value = itemId;
            document.getElementById('return-item-name').value = itemName;
            
            // Set today as default return date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('return-date').value = today;
            
            modals['log-return'].classList.add('active');
        });
    });
    
    // Close modals
    document.getElementById('close-log-use-modal').addEventListener('click', () => {
        modals['log-use'].classList.remove('active');
    });
    
    document.getElementById('cancel-log-use').addEventListener('click', () => {
        modals['log-use'].classList.remove('active');
    });
    
    document.getElementById('close-log-return-modal').addEventListener('click', () => {
        modals['log-return'].classList.remove('active');
    });
    
    document.getElementById('cancel-log-return').addEventListener('click', () => {
        modals['log-return'].classList.remove('active');
    });
    
    document.getElementById('close-view-details-modal').addEventListener('click', () => {
        modals['view-details'].classList.remove('active');
    });
    
    document.getElementById('close-details').addEventListener('click', () => {
        modals['view-details'].classList.remove('active');
    });
    
    // Submit log use form
    document.getElementById('submit-log-use').addEventListener('click', () => {
        const form = document.getElementById('log-use-form');
        const formValid = validateForm('log-use-form');
        
        if (formValid) {
            // In a real application, you would submit this data to the server
            // For now, we'll just show a success message and close the modal
            alert('Item usage logged successfully!');
            modals['log-use'].classList.remove('active');
            
            // Refresh the tables (in a real app, this would be done after the server response)
            simulateTableRefresh();
        }
    });
    
    // Submit log return form
    document.getElementById('submit-log-return').addEventListener('click', () => {
        const form = document.getElementById('log-return-form');
        const formValid = validateForm('log-return-form');
        
        if (formValid) {
            // In a real application, you would submit this data to the server
            // For now, we'll just show a success message and close the modal
            alert('Item return logged successfully!');
            modals['log-return'].classList.remove('active');
            
            // Refresh the tables (in a real app, this would be done after the server response)
            simulateTableRefresh();
        }
    });
    
    // Search functionality
    document.getElementById('available-search').addEventListener('input', function() {
        filterTable('available-items-table', this.value);
    });
    
    document.getElementById('in-use-search').addEventListener('input', function() {
        filterTable('in-use-items-table', this.value);
    });
    
    document.getElementById('history-search').addEventListener('input', function() {
        filterTable('history-table', this.value);
    });
    
    // Category filter
    document.getElementById('category-filter').addEventListener('change', function() {
        filterTableByColumn('available-items-table', 2, this.value);
    });
    
    // Date filter for history
    document.getElementById('date-filter').addEventListener('change', function() {
        filterTableByDate('history-table', 4, this.value);
    });
    
    // Refresh buttons
    document.getElementById('refresh-available-btn').addEventListener('click', () => {
        // In a real app, this would fetch fresh data from the server
        document.getElementById('available-search').value = '';
        document.getElementById('category-filter').value = '';
        resetTable('available-items-table');
    });
    
    document.getElementById('refresh-in-use-btn').addEventListener('click', () => {
        // In a real app, this would fetch fresh data from the server
        document.getElementById('in-use-search').value = '';
        resetTable('in-use-items-table');
    });
    
    document.getElementById('refresh-history-btn').addEventListener('click', () => {
        // In a real app, this would fetch fresh data from the server
        document.getElementById('history-search').value = '';
        document.getElementById('date-filter').value = '';
        resetTable('history-table');
    });
});

// Form validation function
function validateForm(formId) {
    const form = document.getElementById(formId);
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'red';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    return isValid;
}

// Filter table by search term
function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const lowerSearchTerm = searchTerm.toLowerCase();
    
    for (let i = 0; i < rows.length; i++) {
        const rowText = rows[i].textContent.toLowerCase();
        if (rowText.includes(lowerSearchTerm)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

// Filter table by column value
function filterTableByColumn(tableId, columnIndex, filterValue) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const cell = rows[i].getElementsByTagName('td')[columnIndex];
        if (!filterValue || cell.textContent === filterValue) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

// Filter table by date
function filterTableByDate(tableId, columnIndex, filterDate) {
    if (!filterDate) {
        resetTable(tableId);
        return;
    }
    
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const cell = rows[i].getElementsByTagName('td')[columnIndex];
        // Convert date formats for comparison
        const rowDate = new Date(cell.textContent).toISOString().split('T')[0];
        
        if (rowDate === filterDate) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

// Reset table filters
function resetTable(tableId) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        rows[i].style.display = '';
    }
}

// Simulate table refresh (in a real app, this would update with fresh data from server)
function simulateTableRefresh() {
    // This is just a placeholder for actual data refresh
    console.log("Tables would be refreshed with server data");
    // In a real application, you would make an AJAX call to get updated data
    // and then update the tables with the new data
}

// View item details
function viewItemDetails(itemId) {
    // In a real app, you would fetch item details from the server
    // For now, we'll just show a mock item details
    const detailsContent = document.getElementById('details-content');
    
    // Mock item details based on ID
    let itemDetails = '';
    
    if (itemId === '001') {
        itemDetails = `
            <h4>Projector (ID: 001)</h4>
            <p><strong>Category:</strong> Electronics</p>
            <p><strong>Condition:</strong> Good</p>
            <p><strong>Acquisition Date:</strong> January 15, 2025</p>
            <p><strong>Last Maintenance:</strong> April 10, 2025</p>
            <p><strong>Notes:</strong> HDMI cable and remote included. Has been used for 5 events.</p>
        `;
    } else if (itemId === '003') {
        itemDetails = `
            <h4>Microphone Set (ID: 003)</h4>
            <p><strong>Category:</strong> Audio Equipment</p>
            <p><strong>Condition:</strong> Excellent</p>
            <p><strong>Acquisition Date:</strong> February 20, 2025</p>
            <p><strong>Last Maintenance:</strong> April 15, 2025</p>
            <p><strong>Notes:</strong> Set includes 2 wireless microphones and receiver.</p>
        `;
    } else {
        itemDetails = `
            <h4>Item (ID: ${itemId})</h4>
            <p><strong>Category:</strong> Various</p>
            <p><strong>Condition:</strong> Good</p>
            <p><strong>Acquisition Date:</strong> March 1, 2025</p>
            <p><strong>Last Maintenance:</strong> April 1, 2025</p>
            <p><strong>Notes:</strong> General purpose item.</p>
        `;
    }
    
    detailsContent.innerHTML = itemDetails;
    document.getElementById('view-details-modal').classList.add('active');
}

// View usage details
function viewUsageDetails(itemId) {
    // In a real app, you would fetch usage details from the server
    const detailsContent = document.getElementById('details-content');
    
    // Mock usage details based on ID
    let usageDetails = '';
    
    if (itemId === '002') {
        usageDetails = `
            <h4>Basketball (ID: 002) - Currently In Use</h4>
            <p><strong>Borrowed By:</strong> Juan Santos</p>
            <p><strong>Contact:</strong> 0915-123-4567</p>
            <p><strong>Date Borrowed:</strong> May 3, 2025</p>
            <p><strong>Expected Return:</strong> May 10, 2025</p>
            <p><strong>Purpose:</strong> Basketball tournament at Barangay Plaza</p>
            <p><strong>Condition When Borrowed:</strong> Good</p>
            <p><strong>Notes:</strong> Borrower is a youth council member.</p>
        `;
    } else if (itemId === '004') {
        usageDetails = `
            <h4>Digital Camera (ID: 004) - Currently In Use</h4>
            <p><strong>Borrowed By:</strong> Ana Reyes</p>
            <p><strong>Contact:</strong> 0917-987-6543</p>
            <p><strong>Date Borrowed:</strong> May 2, 2025</p>
            <p><strong>Expected Return:</strong> May 9, 2025</p>
            <p><strong>Purpose:</strong> Documentation of clean-up drive</p>
            <p><strong>Condition When Borrowed:</strong> Excellent</p>
            <p><strong>Notes:</strong> Camera includes memory card and charger.</p>
        `;
    } else {
        usageDetails = `
            <h4>Item (ID: ${itemId}) - Currently In Use</h4>
            <p><strong>Borrowed By:</strong> Various User</p>
            <p><strong>Contact:</strong> 0918-111-2222</p>
            <p><strong>Date Borrowed:</strong> May 4, 2025</p>
            <p><strong>Expected Return:</strong> May 11, 2025</p>
            <p><strong>Purpose:</strong> Community event</p>
            <p><strong>Condition When Borrowed:</strong> Good</p>
            <p><strong>Notes:</strong> Standard usage policy applies.</p>
        `;
    }
    
    detailsContent.innerHTML = usageDetails;
    document.getElementById('view-details-modal').classList.add('active');
}

// View return details
function viewReturnDetails(itemId) {
    // In a real app, you would fetch return details from the server
    const detailsContent = document.getElementById('details-content');
    
    // Mock return details based on ID
    let returnDetails = '';
    
    if (itemId === '009') {
        returnDetails = `
            <h4>Volleyball Net (ID: 009) - Return History</h4>
            <p><strong>Borrowed By:</strong> Maria Garcia</p>
            <p><strong>Contact:</strong> 0919-876-5432</p>
            <p><strong>Date Borrowed:</strong> April 20, 2025</p>
            <p><strong>Date Returned:</strong> April 27, 2025</p>
            <p><strong>Purpose:</strong> Volleyball tournament at covered court</p>
            <p><strong>Condition When Borrowed:</strong> Good</p>
            <p><strong>Return Condition:</strong> Good</p>
            <p><strong>Return Notes:</strong> Item returned in the same condition.</p>
        `;
    } else if (itemId === '012') {
        returnDetails = `
            <h4>Tent (ID: 012) - Return History</h4>
            <p><strong>Borrowed By:</strong> Ramon Diaz</p>
            <p><strong>Contact:</strong> 0927-345-6789</p>
            <p><strong>Date Borrowed:</strong> April 5, 2025</p>
            <p><strong>Date Returned:</strong> April 7, 2025</p>
            <p><strong>Purpose:</strong> Outreach program</p>
            <p><strong>Condition When Borrowed:</strong> Good</p>
            <p><strong>Return Condition:</strong> Damaged</p>
            <p><strong>Return Notes:</strong> Tent has a tear on the side and one bent pole. Borrower has agreed to contribute to repair costs.</p>
        `;
    } else {
        returnDetails = `
            <h4>Item (ID: ${itemId}) - Return History</h4>
            <p><strong>Borrowed By:</strong> Past User</p>
            <p><strong>Contact:</strong> 0912-345-6789</p>
            <p><strong>Date Borrowed:</strong> April 10, 2025</p>
            <p><strong>Date Returned:</strong> April 15, 2025</p>
            <p><strong>Purpose:</strong> Community activity</p>
            <p><strong>Condition When Borrowed:</strong> Good</p>
            <p><strong>Return Condition:</strong> Good</p>
            <p><strong>Return Notes:</strong> Standard return process completed.</p>
        `;
    }
    
    detailsContent.innerHTML = returnDetails;
    document.getElementById('view-details-modal').classList.add('active');
}