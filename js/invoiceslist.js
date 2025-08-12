document.addEventListener('DOMContentLoaded', () => {
    const invoicesTableBody = document.getElementById('invoicesTableBody');
    const searchInput = document.getElementById('searchInput');
    const searchFilter = document.getElementById('searchFilter');
    const amountFilter = document.getElementById('amountFilter');
    const clearSearchButton = document.getElementById('clearSearch');
    const searchInfo = document.getElementById('searchInfo');
    
    let allInvoices = []; // Store all invoices for filtering
    let filteredInvoices = []; // Store currently filtered invoices

    // Function to render invoices in the table
    const renderInvoices = (invoices) => {
        invoicesTableBody.innerHTML = '';

        if (invoices.length > 0) {
            invoices.forEach(invoice => {
                const row = document.createElement('tr');
                
                // Format total amount display
                let totalAmountDisplay = 'N/A';
                if (invoice.total_amount !== null && invoice.total_amount !== undefined) {
                    totalAmountDisplay = `$${invoice.total_amount}`;
                }
                
                row.innerHTML = `
                    <td>${invoice.invoice_id}</td>
                    <td>${invoice.order_id}</td>
                    <td>${invoice.vehicle_id}</td>
                    <td class="text-end fw-semibold">${totalAmountDisplay}</td>
                    <td>${invoice.notes || 'N/A'}</td>`;
                invoicesTableBody.appendChild(row);
            });
        } else {
            invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No invoices found.</td></tr>';
        }

        // Update search info
        updateSearchInfo(invoices.length);
    };

    // Function to update search info
    const updateSearchInfo = (resultCount) => {
        const searchTerm = searchInput.value.trim();
        const filterType = searchFilter.value;
        const amountFilterType = amountFilter.value;
        
        if (searchTerm || filterType !== 'all' || amountFilterType !== 'all') {
            let infoText = `Showing ${resultCount} of ${allInvoices.length} invoices`;
            
            if (searchTerm) {
                const filterText = filterType === 'all' ? 'all fields' : filterType.replace('_', ' ');
                infoText += ` for "${searchTerm}" in ${filterText}`;
            }
            
            if (amountFilterType !== 'all') {
                const amountText = amountFilterType === 'with_amount' ? 'with amounts' : 'without amounts';
                infoText += ` (${amountText})`;
            }
            
            searchInfo.textContent = infoText;
            searchInfo.style.display = 'block';
        } else {
            searchInfo.style.display = 'none';
        }
    };

    // Function to filter invoices
    const filterInvoices = () => {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const filterType = searchFilter.value;
        const amountFilterType = amountFilter.value;
        
        filteredInvoices = allInvoices.filter(invoice => {
            // Text search filter
            let matchesText = true;
            if (searchTerm) {
                if (filterType === 'all') {
                    // Search all fields
                    matchesText = 
                        invoice.invoice_id.toString().toLowerCase().includes(searchTerm) ||
                        invoice.order_id.toString().toLowerCase().includes(searchTerm) ||
                        invoice.vehicle_id.toString().toLowerCase().includes(searchTerm) ||
                        (invoice.notes && invoice.notes.toLowerCase().includes(searchTerm));
                } else {
                    // Search specific field
                    let fieldValue = '';
                    switch (filterType) {
                        case 'invoice_id':
                            fieldValue = invoice.invoice_id.toString();
                            break;
                        case 'order_id':
                            fieldValue = invoice.order_id.toString();
                            break;
                        case 'vehicle_id':
                            fieldValue = invoice.vehicle_id.toString();
                            break;
                        case 'notes':
                            fieldValue = invoice.notes || '';
                            break;
                    }
                    matchesText = fieldValue.toLowerCase().includes(searchTerm);
                }
            }
            
            // Amount filter
            let matchesAmount = true;
            if (amountFilterType === 'with_amount') {
                matchesAmount = invoice.total_amount !== null && invoice.total_amount !== undefined;
            } else if (amountFilterType === 'no_amount') {
                matchesAmount = invoice.total_amount === null || invoice.total_amount === undefined;
            }
            
            return matchesText && matchesAmount;
        });
        
        renderInvoices(filteredInvoices);
    };

    // Function to clear all filters
    const clearAllFilters = () => {
        searchInput.value = '';
        searchFilter.value = 'all';
        amountFilter.value = 'all';
        filteredInvoices = allInvoices;
        renderInvoices(allInvoices);
        searchInput.focus();
    };

    // Function to fetch and display invoices
    const fetchInvoices = async () => {
        try {
            const response = await fetch('../php/invoiceslist.php'); 
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const invoices = await response.json();

            // Store all invoices
            allInvoices = invoices;
            filteredInvoices = invoices;

            // Render all invoices initially
            renderInvoices(invoices);

        } catch (error) {
            console.error('Error fetching invoices:', error);
            invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load invoices. Please check the server and database connection.</td></tr>';
            searchInfo.style.display = 'none';
        }
    };

    // Event listeners
    searchInput.addEventListener('input', filterInvoices);
    searchFilter.addEventListener('change', filterInvoices);
    amountFilter.addEventListener('change', filterInvoices);
    clearSearchButton.addEventListener('click', clearAllFilters);

    // Enter key functionality
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterInvoices();
        }
    });

    // Update placeholder text based on selected filter
    searchFilter.addEventListener('change', () => {
        const filterType = searchFilter.value;
        let placeholder = 'Search invoices...';
        
        switch (filterType) {
            case 'invoice_id':
                placeholder = 'Search by Invoice ID...';
                break;
            case 'order_id':
                placeholder = 'Search by Order ID...';
                break;
            case 'vehicle_id':
                placeholder = 'Search by Vehicle ID...';
                break;
            case 'notes':
                placeholder = 'Search in notes...';
                break;
            default:
                placeholder = 'Search all fields...';
        }
        
        searchInput.placeholder = placeholder;
    });

    // Initial fetch
    fetchInvoices();
});