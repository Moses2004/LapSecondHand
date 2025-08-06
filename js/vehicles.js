// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
    'use strict'
    
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
    .forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent default form submission
            event.stopPropagation(); // Stop propagation for Bootstrap validation

            const messageDiv = document.getElementById('vehicleMessage');
            messageDiv.classList.add('hidden'); // Hide previous messages

            if (!form.checkValidity()) {
                form.classList.add('was-validated'); // Show Bootstrap validation feedback
                return; // Stop if form is not valid
            }
            
            // If form is valid, proceed with AJAX submission
            form.classList.add('was-validated'); // Apply was-validated class
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries()); // Convert FormData to a plain object

            fetch(form.action, { // Use the form's action attribute as the URL
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data) // Send data as JSON
            })
            .then(response => {
                if (!response.ok) {
                    // Handle HTTP errors (e.g., 404, 500)
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json(); // Parse the JSON response from PHP
            })
            .then(result => {
                // Display message based on server response
                if (result.success) {
                    messageDiv.classList.remove('alert-danger');
                    messageDiv.classList.add('alert', 'alert-success');
                    form.reset(); // Clear the form on success
                    form.classList.remove('was-validated'); // Reset validation state
                } else {
                    messageDiv.classList.remove('alert-success');
                    messageDiv.classList.add('alert', 'alert-danger');
                }
                messageDiv.textContent = result.message;
                messageDiv.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.classList.remove('alert-success');
                messageDiv.classList.add('alert', 'alert-danger');
                messageDiv.textContent = 'An error occurred while creating the vehicle.';
                messageDiv.classList.remove('hidden');
            });

        }, false)
    })
})()