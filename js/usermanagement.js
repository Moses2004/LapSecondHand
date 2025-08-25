document.addEventListener('DOMContentLoaded', () => {
    // DOM elements for displaying users and general UI
    const usersTableBody = document.getElementById('usersTableBody');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const alertContainer = document.getElementById('alertContainer');
    const searchInput = document.getElementById('searchInput');
    const clearSearchButton = document.getElementById('clearSearch');

    // DOM elements for the Add/Edit User Modal
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    const userForm = document.getElementById('userForm');
    const userModalLabel = document.getElementById('userModalLabel');
    const submitBtn = document.getElementById('submitBtn');
    const userIdInput = document.getElementById('userId'); // Hidden input for user ID (crucial for edit)
    const passwordInput = document.getElementById('password'); // Password input

    // DOM elements for the Delete Confirmation Modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let currentUserIdToDelete = null; 

    // Global arrays to hold user data for client-side filtering
    let allUsers = [];
    let filteredUsers = [];

    // --- Utility Functions ---

    /**
     * Toggles the visibility of the loading spinner.
     * @param {boolean} show - True to show the spinner, false to hide.
     */
    function showLoadingSpinner(show = true) {
        loadingSpinner.classList.toggle('d-none', !show);
    }

    /**
     * Displays an alert message to the user.
     * @param {string} message - The message to display.
     * @param {string} type - The Bootstrap alert type (e.g., 'success', 'danger', 'info').
     */
    function showAlert(message, type) {
        alertContainer.innerHTML = ''; // Clear previous alerts
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show fade-in`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertContainer.appendChild(alertDiv);

        setTimeout(() => {
            const bootstrapAlert = bootstrap.Alert.getInstance(alertDiv);
            if (bootstrapAlert) {
                bootstrapAlert.hide();
            } else {
                alertDiv.remove();
            }
        }, 5000);
    }

    /**
     * Clears the user form fields and validation feedback.
     */
    function clearForm() {
        userForm.reset();
        userIdInput.value = ''; // Clear hidden user ID
        passwordInput.required = true; // Password is required for NEW users
        userForm.classList.remove('was-validated');
        userForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        userForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    // --- Render and Filter Functions ---

    /**
     * Renders a list of users into the table.
     * @param {Array<Object>} users - An array of user objects to render.
     */
    const renderUsers = (users) => {
        usersTableBody.innerHTML = ''; // Clear existing table rows

        if (users.length > 0) {
            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.user_id}</td>
                    <td>${user.email}</td>
                    <td>${user.first_name}</td>
                    <td>${user.last_name}</td>
                    <td>${user.phone_number}</td>
                    <td><span class="badge ${user.role === 'admin' ? 'bg-primary' : 'bg-success'}">${user.role.toUpperCase()}</span></td>
                    <td>${user.timestamp}</td>
                    <td>
                        <div class="d-flex flex-wrap">
                            <!-- Edit button is now active -->
                            <button class="btn btn-warning btn-sm me-1 mb-1" onclick="openEditUserModal(${user.user_id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm me-1 mb-1" onclick="confirmDeleteUser(${user.user_id}, '${user.first_name} ${user.last_name}', '${user.email}')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                `;
                usersTableBody.appendChild(row);
            });
        } else {
            usersTableBody.innerHTML = '<tr><td colspan="8" class="text-center">No users found.</td></tr>';
        }
    };

    /**
     * Filters the `allUsers` array based on a search term and updates the table.
     * @param {string} searchTerm - The string to search for in user data.
     */
    const filterUsers = (searchTerm) => {
        const lowerCaseSearchTerm = searchTerm.trim().toLowerCase();
        if (!lowerCaseSearchTerm) {
            filteredUsers = allUsers;
        } else {
            filteredUsers = allUsers.filter(user =>
                user.first_name.toLowerCase().includes(lowerCaseSearchTerm) ||
                user.last_name.toLowerCase().includes(lowerCaseSearchTerm) ||
                user.email.toLowerCase().includes(lowerCaseSearchTerm) ||
                user.phone_number.toLowerCase().includes(lowerCaseSearchTerm)
            );
        }
        renderUsers(filteredUsers);
    };

    // --- Data Fetching ---

    /**
     * Fetches all users from the backend, updates local arrays, and renders to the table.
     */
    const fetchUsers = async () => {
        showLoadingSpinner(true);
        try {
            const response = await fetch('../php/usermanagement.php?action=read');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();

            if (data.status === 'success') {
                allUsers = data.users;
                filterUsers(searchInput.value);
            } else {
                allUsers = [];
                renderUsers([]);
                showAlert(data.message, 'danger');
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            usersTableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Failed to load users. Please check the server and database connection.</td></tr>`;
            showAlert('An error occurred while fetching users. Check console for details.', 'danger');
        } finally {
            showLoadingSpinner(false);
        }
    };

    // --- Create/Update User Functionality ---

    /**
     * Opens the "Add New User" modal, clearing the form and setting required fields.
     */
    window.openAddUserModal = () => {
        userModalLabel.textContent = 'Add New User';
        passwordInput.required = true; // Password is required for a new user
        clearForm();
        userModal.show();
    };

    /**
     * Opens the "Edit User" modal, populating the form with existing user data.
     * @param {number} userId - The ID of the user to edit.
     */
    window.openEditUserModal = async (userId) => {
        userModalLabel.textContent = 'Edit User';
        passwordInput.required = false; // Password is NOT required for editing (only if user changes it)
        clearForm(); // Clear the form before populating

        try {
            const response = await fetch(`../php/usermanagement.php?action=read_single&user_id=${userId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.status === 'success' && data.user) {
                const user = data.user;
                userIdInput.value = user.user_id; // Set the hidden ID for update
                document.getElementById('email').value = user.email;
                document.getElementById('firstName').value = user.first_name;
                document.getElementById('lastName').value = user.last_name;
                document.getElementById('phoneNumber').value = user.phone_number;
                document.getElementById('role').value = user.role;
                
                userModal.show(); // Show the modal with populated data
            } else {
                showAlert('Failed to load user data for editing.', 'danger');
            }
        } catch (error) {
            console.error('Error fetching user for edit:', error);
            showAlert('An error occurred while fetching user data for editing. Check console for details.', 'danger');
        }
    };

    /**
     * Handles the submission of the user form for both 'create' and 'update' actions.
     */
    userForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        let isValid = true;
        const requiredInputs = userForm.querySelectorAll('[required]');
        requiredInputs.forEach(input => {
            // Password is only required if the form is for a new user (userIdInput is empty)
            // or if it's an edit and the password field is specifically being filled
            if (input.id === 'password' && userIdInput.value && !input.value.trim()) {
                // For edit mode, password is not required if left blank
                input.classList.remove('is-invalid');
                if (input.nextElementSibling) {
                    input.nextElementSibling.textContent = '';
                }
                return; // Skip password validation if it's an edit and left blank
            }

            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                if (input.nextElementSibling) {
                    input.nextElementSibling.textContent = `${input.previousElementSibling.textContent.replace(' *', '')} is required.`;
                }
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
                if (input.nextElementSibling) {
                    input.nextElementSibling.textContent = '';
                }
            }
        });

        const emailInput = document.getElementById('email');
        if (emailInput.value && !/\S+@\S+\.\S+/.test(emailInput.value)) {
            emailInput.classList.add('is-invalid');
            if (emailInput.nextElementSibling) {
                emailInput.nextElementSibling.textContent = 'Please enter a valid email address.';
            }
            isValid = false;
        }

        if (!isValid) {
            userForm.classList.add('was-validated');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const formData = new FormData(userForm);
        // Determine action based on whether userIdInput has a value
        const action = userIdInput.value ? 'update' : 'create'; 
        formData.append('action', action);

        try {
            const response = await fetch('../php/usermanagement.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.status === 'success') {
                userModal.hide();
                showAlert(data.message, 'success');
                fetchUsers(); // Refresh the user list
            } else {
                showAlert(data.message, 'danger');
            }
        } catch (error) {
            console.error('User submission error:', error);
            showAlert('An error occurred during user submission. Please try again.', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save User';
        }
    });

    // --- Delete User Functionality ---

    /**
     * Opens the delete confirmation modal.
     * @param {number} userId - The ID of the user to be deleted.
     * @param {string} name - The full name of the user.
     * @param {string} email - The email of the user.
     */
    window.confirmDeleteUser = (userId, name, email) => {
        currentUserIdToDelete = userId;
        document.getElementById('deleteUserName').textContent = name;
        document.getElementById('deleteUserEmail').textContent = email;
        deleteModal.show();
    };

    /**
     * Sends a request to delete a user.
     * @param {number} userId - The ID of the user to delete.
     */
    const deleteUser = async (userId) => {
        try {
            const response = await fetch('../php/usermanagement.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete&user_id=${userId}`
            });
            const data = await response.json();

            if (data.status === 'success') {
                showAlert(data.message, 'success');
                fetchUsers();
            } else {
                showAlert(data.message, 'danger');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showAlert('An error occurred while deleting the user. Please try again.', 'danger');
        } finally {
            currentUserIdToDelete = null; // Reset the stored ID
        }
    };

    // Event listener for the actual delete button inside the confirmation modal
    confirmDeleteBtn.addEventListener('click', () => {
        if (currentUserIdToDelete) {
            deleteUser(currentUserIdToDelete);
            deleteModal.hide();
        }
    });

    // --- Event Listeners ---
    
    searchInput.addEventListener('input', () => {
        filterUsers(searchInput.value);
    });

    clearSearchButton.addEventListener('click', () => {
        searchInput.value = '';
        filterUsers('');
        searchInput.focus();
    });

    // Initial fetch of users when the page loads
    fetchUsers();
});
