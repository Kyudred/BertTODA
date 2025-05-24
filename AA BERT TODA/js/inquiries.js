// inquiry.js - Handles functionality for the inquiry history page

document.addEventListener('DOMContentLoaded', function() {
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
    
    sendResponseBtn.addEventListener('click', handleSendResponse);
    closeInquiryBtn.addEventListener('click', handleCloseInquiry);

    

    // Load inquiries on page load
    loadInquiries();

    // Event listeners
    searchBtn.addEventListener('click', loadInquiries);
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            loadInquiries();
        }
    });

    statusFilter.addEventListener('change', loadInquiries);
    dateFilter.addEventListener('change', loadInquiries);
    resetFiltersBtn.addEventListener('click', resetFilters);

    closeModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (event) => {
        if (event.target === modal) modal.style.display = 'none';
    });

    sendResponseBtn.addEventListener('click', handleSendResponse);
    closeInquiryBtn.addEventListener('click', handleCloseInquiry);

    function loadInquiries() {
        const searchTerm = searchInput.value.trim();
        const statusValue = statusFilter.value;
        const dateValue = dateFilter.value;

        let url = `get-inquiries.php?search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(statusValue)}&date=${encodeURIComponent(dateValue)}`;

        tableBody.innerHTML = '<tr><td colspan="12" style="text-align: center;">Loading...</td></tr>';

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    updateStatistics(data.statistics);
                    populateTable(data.data);
                } else {
                    throw new Error(data.message || 'Failed to load inquiries');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tableBody.innerHTML = `<tr><td colspan="12" style="text-align: center;">Failed to load inquiries. Please try again later.</td></tr>`;
            });
    }

    function updateStatistics(stats) {
        if (!stats) return;
        
        const elements = {
            totalInquiries: document.getElementById('totalInquiries'),
            pendingInquiries: document.getElementById('pendingInquiries'),
            processingInquiries: document.getElementById('processingInquiries'),
            resolvedInquiries: document.getElementById('resolvedInquiries')
        };

        if (elements.totalInquiries) elements.totalInquiries.textContent = stats.total || 0;
        if (elements.pendingInquiries) elements.pendingInquiries.textContent = stats.pending || 0;
        if (elements.processingInquiries) elements.processingInquiries.textContent = stats.processing || 0;
        if (elements.resolvedInquiries) elements.resolvedInquiries.textContent = stats.resolved || 0;
    }

    function populateTable(data) {
        if (!Array.isArray(data)) {
            console.error('Invalid data format received');
            tableBody.innerHTML = '<tr><td colspan="12" style="text-align: center;">Invalid data format received</td></tr>';
            return;
        }

        tableBody.innerHTML = '';
    
        if (data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="12" style="text-align: center;">No inquiries found</td></tr>';
            return;
        }
    
        data.forEach(inquiry => {
            if (!inquiry) return;

            const row = document.createElement('tr');
            const date = new Date(inquiry.submitted_at);
            const formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
    
            const statusClass = `badge badge-${inquiry.status || 'pending'}`;
    
            row.innerHTML = `
                <td>${inquiry.id || ''}</td>
                <td>${inquiry.full_name || ''}</td>
                <td>${inquiry.email || ''}</td>
                <td>${inquiry.age || ''}</td>
                <td>${inquiry.contact_number || ''}</td>
                <td>${capitalizeFirstLetter(inquiry.gender || '')}</td>
                <td>${inquiry.address || ''}</td>
                <td>${capitalizeFirstLetter(inquiry.inquiry_type || '')}</td>
                <td>${inquiry.message || ''}</td>
                <td>${formattedDate}</td>
                <td><span class="${statusClass}">${capitalizeFirstLetter(inquiry.status || 'pending')}</span></td>
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
                if (inquiryId) {
                    openInquiryModal(inquiryId, data);
                }
            });
        });
    }

    function resetFilters() {
        searchInput.value = '';
        statusFilter.value = 'all';
        dateFilter.value = 'all';
        loadInquiries();
    }

    function openInquiryModal(inquiryId, inquiries) {
        const inquiry = inquiries.find(item => item.id == inquiryId);
        if (!inquiry) {
            console.error('Inquiry not found:', inquiryId);
            return;
        }
    
        const date = new Date(inquiry.submitted_at);
        const formattedDate = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    
        // Populate modal fields with fallback values
        const modalFields = {
            'modalInquiryId': inquiry.id,
            'modalName': inquiry.full_name,
            'modalEmail': inquiry.email,
            'modalAge': inquiry.age,
            'modalPhone': inquiry.contact_number,
            'modalGender': inquiry.gender,
            'modalAddress': inquiry.address,
            'modalSubject': inquiry.inquiry_type,
            'modalDate': formattedDate,
            'modalMessage': inquiry.message,
            'modalStatus': inquiry.status
        };

        Object.entries(modalFields).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.innerText = value || 'N/A';
            }
        });
   
        const statusElement = document.getElementById('modalStatus');
        if (statusElement) {
            statusElement.className = `detail-value badge badge-${inquiry.status || 'pending'}`;
        }
    
        // Set dropdown and textarea values
        const statusUpdate = document.getElementById('statusUpdate');
        const responseText = document.getElementById('responseText');
        
        if (statusUpdate) statusUpdate.value = inquiry.status || 'pending';
        if (responseText) responseText.value = inquiry.response || '';
    
        modal.style.display = 'block';
    }

    function handleSendResponse() {
        const inquiryId = document.getElementById('modalInquiryId')?.textContent?.trim();
        const response = document.getElementById('responseText')?.value?.trim();
        const newStatus = document.getElementById('statusUpdate')?.value?.trim();
    
        if (!inquiryId || !response || !newStatus) {
            alert("Please fill in all required fields before sending.");
            return;
        }
    
        const formData = new FormData();
        formData.append('inquiry_id', inquiryId);
        formData.append('status', newStatus);
        formData.append('response', response);
    
        fetch('update-inquiry.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                loadInquiries();
                modal.style.display = 'none';
                alert('Response sent successfully!');
            } else {
                throw new Error(data.message || 'Failed to update inquiry');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send response. Please try again.');
        });
    }

    function handleCloseInquiry() {
        const inquiryId = document.getElementById('modalInquiryId')?.textContent?.trim();
        if (!inquiryId) {
            alert('Invalid inquiry ID');
            return;
        }

        const formData = new FormData();
        formData.append('inquiry_id', inquiryId);
        formData.append('status', 'closed');

        fetch('update-inquiry.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                loadInquiries();
                modal.style.display = 'none';
                alert('Inquiry has been closed.');
            } else {
                throw new Error(data.message || 'Failed to close inquiry');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to close inquiry. Please try again.');
        });
    }

    function capitalizeFirstLetter(string) {
        if (!string) return '';
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }

    
});
