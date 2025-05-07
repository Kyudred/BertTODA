// inquiry.js - Handles functionality for the inquiry history page

document.addEventListener('DOMContentLoaded', function() {
    // Sample inquiry data (in a real application, this would come from a database)
    const inquiries = [
        {
            id: 'INQ001',
            name: 'Juan Dela Cruz',
            email: 'juan.delacruz@email.com',
            phone: '09123456789',
            subject: 'Question about upcoming basketball tournament',
            date: '2025-05-07',
            status: 'pending',
            message: 'Hello, I would like to know more details about the upcoming basketball tournament at San Isidro covered court. Is there an entrance fee for participants? Thank you.'
        },
        {
            id: 'INQ002',
            name: 'Maria Santos',
            email: 'maria.santos@email.com',
            phone: '09187654321',
            subject: 'Volunteering opportunities',
            date: '2025-05-06',
            status: 'processing',
            message: 'Good day! I am a resident of San Isidro and I would like to volunteer for the upcoming youth programs. Please let me know how I can help and what opportunities are available.'
        },
        {
            id: 'INQ003',
            name: 'Pedro Reyes',
            email: 'pedro.reyes@email.com',
            phone: '09234567890',
            subject: 'Borrowing equipment for community event',
            date: '2025-05-05',
            status: 'resolved',
            message: 'We are organizing a small community event and would like to borrow some equipment like sound system and chairs. Is this possible? What is the process for borrowing equipment?'
        },
        {
            id: 'INQ004',
            name: 'Elena Garcia',
            email: 'elena.garcia@email.com',
            phone: '09345678901',
            subject: 'Youth leadership workshop',
            date: '2025-05-04',
            status: 'closed',
            message: 'I heard about the youth leadership workshop scheduled for next month. My daughter is interested in participating. Could you please provide more information about the registration process?'
        },
        {
            id: 'INQ005',
            name: 'Antonio Lim',
            email: 'antonio.lim@email.com',
            phone: '09456789012',
            subject: 'Donation for community library',
            date: '2025-05-03',
            status: 'pending',
            message: 'I have some books that I would like to donate to the community library. When and where can I drop them off?'
        },
        {
            id: 'INQ006',
            name: 'Sofia Mendoza',
            email: 'sofia.mendoza@email.com',
            phone: '09567890123',
            subject: 'Youth council meeting schedule',
            date: '2025-05-02',
            status: 'processing',
            message: 'Can you please provide the schedule for the next youth council meeting? I would like to attend and observe.'
        },
        {
            id: 'INQ007',
            name: 'Rafael Torres',
            email: 'rafael.torres@email.com',
            phone: '09678901234',
            subject: 'Environmental cleanup drive',
            date: '2025-05-01',
            status: 'resolved',
            message: 'I want to propose an environmental cleanup drive for our barangay. Who should I talk to about this initiative?'
        },
        {
            id: 'INQ008',
            name: 'Isabella Cruz',
            email: 'isabella.cruz@email.com',
            phone: '09789012345',
            subject: 'Tutoring program inquiry',
            date: '2025-04-30',
            status: 'closed',
            message: 'I am interested in the after-school tutoring program for high school students. Is this program still ongoing? How can my son enroll?'
        }
    ];

    // Get DOM elements
    const inquiryTable = document.getElementById('inquiryTable');
    const tableBody = inquiryTable.querySelector('tbody');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const modal = document.getElementById('inquiryModal');
    const closeModal = document.querySelector('.close-modal');
    const sendResponseBtn = document.getElementById('sendResponse');
    const closeInquiryBtn = document.getElementById('closeInquiry');
    
    // Update statistics display
    updateStatistics();
    
    // Initial table population
    populateTable(inquiries);
    
    // Event listeners
    searchBtn.addEventListener('click', filterInquiries);
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            filterInquiries();
        }
    });
    
    statusFilter.addEventListener('change', filterInquiries);
    dateFilter.addEventListener('change', filterInquiries);
    resetFiltersBtn.addEventListener('click', resetFilters);
    
    // Modal event listeners
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    sendResponseBtn.addEventListener('click', handleSendResponse);
    closeInquiryBtn.addEventListener('click', handleCloseInquiry);
    
    // Functions
    function populateTable(data) {
        // Clear the table
        tableBody.innerHTML = '';
        
        // Check if there are any inquiries to display
        if (data.length === 0) {
            const noDataRow = document.createElement('tr');
            noDataRow.innerHTML = `<td colspan="7" style="text-align: center;">No inquiries found</td>`;
            tableBody.appendChild(noDataRow);
            return;
        }
        
        // Add inquiries to the table
        data.forEach(inquiry => {
            const row = document.createElement('tr');
            
            // Format the date
            const date = new Date(inquiry.date);
            const formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            // Create status badge class
            const statusClass = `badge badge-${inquiry.status}`;
            
            row.innerHTML = `
                <td>${inquiry.id}</td>
                <td>${inquiry.name}</td>
                <td>${inquiry.email}</td>
                <td>${inquiry.subject}</td>
                <td>${formattedDate}</td>
                <td><span class="${statusClass}">${capitalizeFirstLetter(inquiry.status)}</span></td>
                <td>
                    <button class="btn btn-primary btn-view" data-id="${inquiry.id}">View Details</button>
                </td>
            `;
            
            tableBody.appendChild(row);
        });
        
        // Add event listeners to view buttons
        document.querySelectorAll('.btn-view').forEach(button => {
            button.addEventListener('click', function() {
                const inquiryId = this.getAttribute('data-id');
                openInquiryModal(inquiryId);
            });
        });
    }
    
    function filterInquiries() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const dateValue = dateFilter.value;
        
        // Filter the inquiries based on search, status, and date
        let filteredInquiries = inquiries.filter(inquiry => {
            const matchesSearch = 
                inquiry.id.toLowerCase().includes(searchTerm) ||
                inquiry.name.toLowerCase().includes(searchTerm) ||
                inquiry.email.toLowerCase().includes(searchTerm) ||
                inquiry.subject.toLowerCase().includes(searchTerm) ||
                inquiry.message.toLowerCase().includes(searchTerm);
            
            const matchesStatus = statusValue === 'all' || inquiry.status === statusValue;
            
            let matchesDate = true;
            if (dateValue !== 'all') {
                const inquiryDate = new Date(inquiry.date);
                const today = new Date();
                
                if (dateValue === 'today') {
                    matchesDate = inquiryDate.toDateString() === today.toDateString();
                } else if (dateValue === 'week') {
                    const weekAgo = new Date();
                    weekAgo.setDate(today.getDate() - 7);
                    matchesDate = inquiryDate >= weekAgo;
                } else if (dateValue === 'month') {
                    const monthAgo = new Date();
                    monthAgo.setMonth(today.getMonth() - 1);
                    matchesDate = inquiryDate >= monthAgo;
                } else if (dateValue === 'quarter') {
                    const quarterAgo = new Date();
                    quarterAgo.setMonth(today.getMonth() - 3);
                    matchesDate = inquiryDate >= quarterAgo;
                }
            }
            
            return matchesSearch && matchesStatus && matchesDate;
        });
        
        populateTable(filteredInquiries);
    }
    
    function resetFilters() {
        searchInput.value = '';
        statusFilter.value = 'all';
        dateFilter.value = 'all';
        populateTable(inquiries);
    }
    
    function openInquiryModal(inquiryId) {
        // Find the inquiry by ID
        const inquiry = inquiries.find(item => item.id === inquiryId);
        
        if (!inquiry) return;
        
        // Format the date
        const date = new Date(inquiry.date);
        const formattedDate = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Populate modal with inquiry details
        document.getElementById('modalInquiryId').textContent = inquiry.id;
        document.getElementById('modalName').textContent = inquiry.name;
        document.getElementById('modalEmail').textContent = inquiry.email;
        document.getElementById('modalPhone').textContent = inquiry.phone;
        document.getElementById('modalSubject').textContent = inquiry.subject;
        document.getElementById('modalDate').textContent = formattedDate;
        document.getElementById('modalStatus').textContent = capitalizeFirstLetter(inquiry.status);
        document.getElementById('modalStatus').className = `detail-value badge badge-${inquiry.status}`;
        document.getElementById('modalMessage').textContent = inquiry.message;
        
        // Set status dropdown to current status
        document.getElementById('statusUpdate').value = inquiry.status;
        
        // Clear response text area
        document.getElementById('responseText').value = '';
        
        // Show the modal
        modal.style.display = 'block';
    }
    
    function handleSendResponse() {
        const inquiryId = document.getElementById('modalInquiryId').textContent;
        const response = document.getElementById('responseText').value;
        const newStatus = document.getElementById('statusUpdate').value;
        
        // Find the inquiry and update its status
        const inquiry = inquiries.find(item => item.id === inquiryId);
        if (inquiry) {
            inquiry.status = newStatus;
            
            // In a real application, you would send this response to a server
            console.log(`Response sent to ${inquiry.email}: ${response}`);
            console.log(`Status updated to: ${newStatus}`);
            
            // Update the table and statistics
            populateTable(inquiries);
            updateStatistics();
            
            // Close the modal
            modal.style.display = 'none';
            
            // Show notification (this would be replaced with a proper notification system)
            alert('Response sent successfully!');
        }
    }
    
    function handleCloseInquiry() {
        const inquiryId = document.getElementById('modalInquiryId').textContent;
        
        // Find the inquiry and update its status to closed
        const inquiry = inquiries.find(item => item.id === inquiryId);
        if (inquiry) {
            inquiry.status = 'closed';
            
            // Update the table and statistics
            populateTable(inquiries);
            updateStatistics();
            
            // Close the modal
            modal.style.display = 'none';
            
            // Show notification
            alert('Inquiry has been closed.');
        }
    }
    
    function updateStatistics() {
        // Count inquiries by status
        const total = inquiries.length;
        const pending = inquiries.filter(item => item.status === 'pending').length;
        const processing = inquiries.filter(item => item.status === 'processing').length;
        const resolved = inquiries.filter(item => item.status === 'resolved').length;
        
        // Update the statistics displays
        document.getElementById('totalInquiries').textContent = total;
        document.getElementById('pendingInquiries').textContent = pending;
        document.getElementById('processingInquiries').textContent = processing;
        document.getElementById('resolvedInquiries').textContent = resolved;
    }
    
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
});