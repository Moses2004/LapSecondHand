document.addEventListener('DOMContentLoaded', () => {
    const invoicesTableBody = document.getElementById('invoicesTableBody');

    // Function to fetch and display invoices
    const fetchInvoices = async () => {
        try {
            const response = await fetch('../php/invoiceslist.php'); 
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const invoices = await response.json();

            // Clear loading message
            invoicesTableBody.innerHTML = '';

            if (invoices.length > 0) {
                invoices.forEach(invoice => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${invoice.invoice_id}</td>
                        <td>${invoice.order_id}</td>
                        <td>${invoice.vehicle_id}</td>
                        
                        <td>${invoice.notes || 'N/A'}</td> `;
                    invoicesTableBody.appendChild(row);
                });
            } else {
                invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No invoices found.</td></tr>';
            }

        } catch (error) {
            console.error('Error fetching invoices:', error);
            invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load invoices. Please check the server and database connection.</td></tr>';
        }
    };

    fetchInvoices();
});