document.addEventListener("DOMContentLoaded", function () {
    const formData = new FormData();
    // all formData.append() ...
    fetch('submit_inquiry.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector("#inquiryTable tbody");
            tbody.innerHTML = "";

            data.forEach((inquiry, index) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${inquiry.id}</td>
                    <td>${inquiry.full_name}</td>
                    <td>${inquiry.email}</td>
                    <td>${inquiry.age}</td>
                    <td>${inquiry.contact_number}</td>
                    <td>${inquiry.gender}</td>
                    <td>${inquiry.address}</td>
                    <td>${inquiry.message}</td>
                    <td>${inquiry.inquiry_type}</td>
                    <td>${inquiry.submitted_at}</td>
                    <td class="status">${inquiry.status}</td>
                    <td><button class="view-btn" data-id="${inquiry.id}">View</button></td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => console.error("Failed to fetch inquiries:", error));
});