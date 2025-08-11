// Phone List Page JavaScript

let allPhones = []; // Store the full list of phones globally

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');

    fetchPhoneList();

    searchInput.addEventListener('input', filterPhoneList);
    clearSearchBtn.addEventListener('click', clearSearch);
});

async function fetchPhoneList() {
    const phoneTableBody = document.getElementById('phoneTableBody');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const messageContainer = document.getElementById('messageContainer');
    const phoneTable = document.getElementById('phoneTable');

    // Show loading spinner
    loadingSpinner.classList.remove('d-none');
    phoneTable.classList.add('d-none');
    messageContainer.classList.add('d-none');

    try {
        const response = await fetch('../php/phoneslist.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();

        if (result.success) {
            allPhones = result.data; // Store fetched data
            if (allPhones.length > 0) {
                renderPhoneTable(allPhones);
                phoneTable.classList.remove('d-none');
            } else {
                showMessage('No phones found in the database.', 'info');
                phoneTable.classList.add('d-none');
            }
        } else {
            showMessage(result.message, 'danger');
            phoneTable.classList.add('d-none');
        }

    } catch (error) {
        console.error('Error fetching phone list:', error);
        showMessage('An error occurred while fetching the phone list. Please try again later.', 'danger');
        phoneTable.classList.add('d-none');
    } finally {
        loadingSpinner.classList.add('d-none');
    }
}

function renderPhoneTable(phones) {
    const phoneTableBody = document.getElementById('phoneTableBody');
    phoneTableBody.innerHTML = ''; // Clear previous rows
    
    if (phones.length === 0) {
        phoneTableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">No matching phones found.</td>
            </tr>
        `;
        return;
    }

    phones.forEach(phone => {
        const row = document.createElement('tr');
        row.innerHTML = `
           
            <td>${phone.brand}</td>
            <td>${phone.model}</td>
            <td>${phone.color}</td>
            <td>$${phone.price}</td>
            <td>${phone.stock}</td>
            <td>${phone.created_at}</td>
        `;
        phoneTableBody.appendChild(row);
    });
}

function filterPhoneList(event) {
    const searchTerm = event.target.value.toLowerCase();
    const filteredPhones = allPhones.filter(phone => 
        phone.brand.toLowerCase().includes(searchTerm) ||
        phone.model.toLowerCase().includes(searchTerm)
    );
    renderPhoneTable(filteredPhones);
}

function clearSearch() {
    const searchInput = document.getElementById('searchInput');
    searchInput.value = '';
    renderPhoneTable(allPhones);
}

function showMessage(message, type) {
    const messageContainer = document.getElementById('messageContainer');
    messageContainer.textContent = message;
    messageContainer.className = `alert alert-${type}`;
    messageContainer.classList.remove('d-none');
}