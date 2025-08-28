// invoices.js

document.addEventListener('DOMContentLoaded', () => {
    const invoiceForm = document.getElementById('invoiceForm');
    const messageDiv = document.getElementById('message');

    if (invoiceForm) {
        invoiceForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // Prevent default form submission

            // Clear previous messages
            messageDiv.textContent = '';
            messageDiv.className = 'mt-4 p-3 text-center rounded-md hidden'; // Reset classes

            // Collect form data
            const formData = new FormData(invoiceForm);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            try {
                // Send data to the PHP backend
                const response = await fetch('../php/invoices.php', { 
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json' // Indicate JSON payload
                    },
                    body: JSON.stringify(data) // Send data as JSON
                });

                const result = await response.json(); // Parse JSON response from PHP

                if (result.success) {
                    messageDiv.textContent = result.message;
                    messageDiv.classList.add('success', 'block'); // Show success message
                    invoiceForm.reset(); // Clear the form on success
                } else {
                    messageDiv.textContent = result.message || 'An unknown error occurred.';
                    messageDiv.classList.add('error', 'block'); // Show error message
                }
            } catch (error) {
                console.error('Error:', error);
                messageDiv.textContent = 'Failed to connect to the server. Please try again.';
                messageDiv.classList.add('error', 'block'); // Show connection error
            }
        });
    }
});
