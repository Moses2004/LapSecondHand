// --- Configuration ---
// Change this to your actual API base URL.
// If your PHP API files are in 'htdocs/phoneshop_reports/api/':
const API_BASE_URL = '/phoneshop_reports/api';

// Chart instances (to destroy and re-create for updates)
let salesByBrandChart, salesByMonthChart, ordersByStatusChart, inventoryByBrandChart, inventoryByColorChart;

// --- Helper Functions ---

/**
 * Fetches data from a given URL with optional parameters.
 * @param {string} url - The API endpoint URL.
 * @param {object} params - Query parameters for the request.
 * @returns {Promise<object|null>} - The fetched JSON data or null on error.
 */
async function fetchData(url, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = `${url}?${queryString}`;
    console.log(`Fetching data from: ${fullUrl}`); // For debugging

    try {
        const response = await fetch(fullUrl);
        if (!response.ok) {
            // Handle HTTP errors (e.g., 404, 500)
            const errorText = await response.text();
            console.error(`HTTP error! Status: ${response.status}, Details: ${errorText}`);
            throw new Error(`Failed to load data: ${response.statusText}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching data:', error);
        alert('Failed to load report data. Please check your network or server configuration.');
        return null;
    }
}

/**
 * Renders a bar chart.
 * @param {HTMLCanvasElement} canvasElement - The canvas element to draw on.
 * @param {string[]} labels - Array of labels for the chart.
 * @param {number[]} data - Array of data values.
 * @param {string} chartLabel - Label for the dataset.
 * @param {string} color - Bar color.
 * @param {Chart} existingChart - Existing chart instance to destroy (optional).
 * @returns {Chart} - New Chart.js instance.
 */
function renderBarChart(canvasElement, labels, data, chartLabel, color, existingChart) {
    if (existingChart) existingChart.destroy();
    return new Chart(canvasElement.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: chartLabel,
                data: data,
                backgroundColor: color,
                borderColor: color.replace('0.6', '1'), // Solid border
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allows chart to fill parent div
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { display: true }
            }
        }
    });
}

/**
 * Renders a line chart.
 * @param {HTMLCanvasElement} canvasElement - The canvas element to draw on.
 * @param {string[]} labels - Array of labels for the chart.
 * @param {number[]} data - Array of data values.
 * @param {string} chartLabel - Label for the dataset.
 * @param {string} color - Line color.
 * @param {Chart} existingChart - Existing chart instance to destroy (optional).
 * @returns {Chart} - New Chart.js instance.
 */
function renderLineChart(canvasElement, labels, data, chartLabel, color, existingChart) {
    if (existingChart) existingChart.destroy();
    return new Chart(canvasElement.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: chartLabel,
                data: data,
                borderColor: color,
                backgroundColor: color.replace('1)', '0.2)'), // Light fill
                tension: 0.3,
                fill: true,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointHoverRadius: 6,
                pointHoverBackgroundColor: color,
                pointHoverBorderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { display: true }
            }
        }
    });
}

/**
 * Renders a pie/doughnut chart.
 * @param {HTMLCanvasElement} canvasElement - The canvas element to draw on.
 * @param {string[]} labels - Array of labels for the chart.
 * @param {number[]} data - Array of data values.
 * @param {string[]} colors - Array of background colors for segments.
 * @param {Chart} existingChart - Existing chart instance to destroy (optional).
 * @param {'pie'|'doughnut'} type - Type of chart.
 * @returns {Chart} - New Chart.js instance.
 */
function renderPieDoughnutChart(canvasElement, labels, data, colors, existingChart, type = 'pie') {
    if (existingChart) existingChart.destroy();
    return new Chart(canvasElement.getContext('2d'), {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right', // Place legend on the right for better space
                    labels: {
                        boxWidth: 20
                    }
                }
            }
        }
    });
}

// --- Report Specific Functions ---

// Sales Report
async function fetchSalesReport() {
    const startDate = document.getElementById('sales-start-date').value;
    const endDate = document.getElementById('sales-end-date').value;
    const brand = document.getElementById('sales-brand').value;

    const params = {};
    if (startDate) params.start_date = startDate;
    if (endDate) params.end_date = endDate;
    if (brand) params.brand = brand;

    const data = await fetchData(`${API_BASE_URL}/sales.php`, params);
    if (!data) {
        document.getElementById('total-sold').textContent = 'N/A';
        if (salesByBrandChart) salesByBrandChart.destroy();
        if (salesByMonthChart) salesByMonthChart.destroy();
        return;
    }

    document.getElementById('total-sold').textContent = data.totalSold;

    // Render Sales by Brand Chart
    salesByBrandChart = renderBarChart(
        document.getElementById('salesByBrandChart'),
        data.salesByBrand.map(item => item.brand),
        data.salesByBrand.map(item => item.sales_count),
        'Sales Count by Brand',
        'rgba(0, 123, 255, 0.8)', // Blue
        salesByBrandChart
    );

    // Render Sales by Month Chart
    salesByMonthChart = renderLineChart(
        document.getElementById('salesByMonthChart'),
        data.salesByMonth.map(item => item.month),
        data.salesByMonth.map(item => item.sales_count),
        'Sales Count by Month',
        'rgba(220, 53, 69, 1)', // Red
        salesByMonthChart
    );
}

// Order Report
async function fetchOrderReport() {
    const startDate = document.getElementById('orders-start-date').value;
    const endDate = document.getElementById('orders-end-date').value;
    const status = document.getElementById('orders-status').value;

    const params = {};
    if (startDate) params.start_date = startDate;
    if (endDate) params.end_date = endDate;
    if (status) params.status = status;

    const data = await fetchData(`${API_BASE_URL}/orders.php`, params);
    if (!data) {
        if (ordersByStatusChart) ordersByStatusChart.destroy();
        document.getElementById('recent-orders-table').getElementsByTagName('tbody')[0].innerHTML = '<tr><td colspan="8">No data available.</td></tr>';
        return;
    }

    // Orders by Status Chart
    const statusColors = {
        'pending': '#FFC107', // Warning yellow
        'processing': '#20C997', // Teal
        'shipped': '#17A2B8', // Info cyan
        'ready_for_pickup': '#6F42C1', // Purple
        'delivered': '#28A745', // Success green
        'cancelled': '#DC3545' // Danger red
    };
    ordersByStatusChart = renderPieDoughnutChart(
        document.getElementById('ordersByStatusChart'),
        data.orderByStatus.map(item => item.order_status),
        data.orderByStatus.map(item => item.status_count),
        data.orderByStatus.map(item => statusColors[item.order_status] || '#6c757d'), // Default grey for unknown
        ordersByStatusChart,
        'doughnut'
    );

    // Recent Orders Table
    const tableBody = document.getElementById('recent-orders-table').getElementsByTagName('tbody')[0];
    tableBody.innerHTML = ''; // Clear previous data
    if (data.recentOrders && data.recentOrders.length > 0) {
        data.recentOrders.forEach(order => {
            const row = tableBody.insertRow();
            row.insertCell().textContent = order.order_id;
            row.insertCell().textContent = order.buyer_name;
            row.insertCell().textContent = `${order.brand} ${order.model}`;
            row.insertCell().textContent = order.quantity;
            row.insertCell().textContent = order.delivery_method;
            row.insertCell().textContent = order.shipping_address || 'N/A';
            row.insertCell().textContent = order.order_status;
            row.insertCell().textContent = new Date(order.ordered_at_datetime).toLocaleString();
        });
    } else {
        tableBody.innerHTML = '<tr><td colspan="8">No recent orders found with applied filters.</td></tr>';
    }
}

// Inventory Report
async function fetchInventoryReport() {
    const brand = document.getElementById('inventory-brand').value;
    const color = document.getElementById('inventory-color').value;

    const params = {};
    if (brand) params.brand = brand;
    if (color) params.color = color;

    const data = await fetchData(`${API_BASE_URL}/inventory.php`, params);
    if (!data) {
        document.getElementById('total-available').textContent = 'N/A';
        if (inventoryByBrandChart) inventoryByBrandChart.destroy();
        if (inventoryByColorChart) inventoryByColorChart.destroy();
        return;
    }

    document.getElementById('total-available').textContent = data.totalAvailable;

    // Inventory by Brand Chart
    inventoryByBrandChart = renderBarChart(
        document.getElementById('inventoryByBrandChart'),
        data.phonesByBrand.map(item => item.brand),
        data.phonesByBrand.map(item => item.count),
        'Phones by Brand (Available)',
        'rgba(40, 167, 69, 0.8)', // Green
        inventoryByBrandChart
    );

    // Inventory by Color Chart
    const colorChartColors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9933', '#C0C0C0', '#6A5ACD', '#8A2BE2'
    ]; // More varied colors
    inventoryByColorChart = renderPieDoughnutChart(
        document.getElementById('inventoryByColorChart'),
        data.phonesByColor.map(item => item.color),
        data.phonesByColor.map(item => item.count),
        colorChartColors,
        inventoryByColorChart,
        'doughnut'
    );
}


// --- Event Listeners and Initial Load ---
document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-button');
    const reportContents = document.querySelectorAll('.report-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const reportType = button.dataset.reportType;

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            reportContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button and its content
            button.classList.add('active');
            document.getElementById(`${reportType}-report-content`).classList.add('active');

            // Fetch data for the newly active tab
            if (reportType === 'sales') {
                fetchSalesReport();
            } else if (reportType === 'orders') {
                fetchOrderReport();
            } else if (reportType === 'inventory') {
                fetchInventoryReport();
            }
        });
    });

    // Initial load: Fetch data for the first active tab (Sales Report)
    fetchSalesReport();
});