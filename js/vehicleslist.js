document.addEventListener('DOMContentLoaded', () => {
    const vehiclesTableBody = document.getElementById('vehiclesTableBody');

    // Function to fetch and display vehicles
    const fetchVehicles = async () => {
        try {
            const response = await fetch('../php/vehicleslist.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const vehicles = await response.json();

            // Clear loading message
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

        } catch (error) {
            console.error('Error fetching vehicles:', error);
            vehiclesTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load vehicles. Please check the server and database connection.</td></tr>';
        }
    };

    fetchVehicles();
});