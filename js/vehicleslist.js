document.addEventListener('DOMContentLoaded', () => {
    const vehiclesTableBody = document.getElementById('vehiclesTableBody');
    const searchInput = document.getElementById('searchInput');
    const clearSearchButton = document.getElementById('clearSearch');
    const searchInfo = document.getElementById('searchInfo');
    
    let allVehicles = []; // Store all vehicles for filtering
    let filteredVehicles = []; // Store currently filtered vehicles

    // Function to render vehicles in the table
    const renderVehicles = (vehicles) => {
        vehiclesTableBody.innerHTML = '';

        if (vehicles.length > 0) {
            vehicles.forEach(vehicle => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${vehicle.vehicle_id}</td>
                    <td>${vehicle.driver_name}</td>
                    <td>${vehicle.license_plate}</td>
                    <td>${vehicle.weight_kg}</td>`;
                vehiclesTableBody.appendChild(row);
            });
        } else {
            vehiclesTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No vehicles found.</td></tr>';
        }

        // Update search info
        updateSearchInfo(vehicles.length);
    };

    // Function to update search info
    const updateSearchInfo = (resultCount) => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            searchInfo.textContent = `Showing ${resultCount} of ${allVehicles.length} vehicles for "${searchTerm}"`;
            searchInfo.style.display = 'block';
        } else {
            searchInfo.style.display = 'none';
        }
    };

    // Function to filter vehicles by driver name
    const filterVehicles = (searchTerm) => {
        if (!searchTerm.trim()) {
            filteredVehicles = allVehicles;
        } else {
            filteredVehicles = allVehicles.filter(vehicle => 
                vehicle.driver_name.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }
        renderVehicles(filteredVehicles);
    };

    // Function to fetch and display vehicles
    const fetchVehicles = async () => {
        try {
            const response = await fetch('../php/vehicleslist.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const vehicles = await response.json();

            // Store all vehicles
            allVehicles = vehicles;
            filteredVehicles = vehicles;

            // Render all vehicles initially
            renderVehicles(vehicles);

        } catch (error) {
            console.error('Error fetching vehicles:', error);
            vehiclesTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load vehicles. Please check the server and database connection.</td></tr>';
            searchInfo.style.display = 'none';
        }
    };

    // Search functionality
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value;
        filterVehicles(searchTerm);
    });

    // Clear search functionality
    clearSearchButton.addEventListener('click', () => {
        searchInput.value = '';
        filterVehicles('');
        searchInput.focus();
    });

    // Enter key functionality
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const searchTerm = searchInput.value;
            filterVehicles(searchTerm);
        }
    });

    // Initial fetch
    fetchVehicles();
});