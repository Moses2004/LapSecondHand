// LAPSECONDHA.../js/phone_detail.js

document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const phoneId = urlParams.get('id'); // Get phone ID from URL

    const phoneDetailsDiv = document.getElementById('phoneDetails');

    if (!phoneId) {
        phoneDetailsDiv.innerHTML = '<p style="color: red;">Error: No phone ID provided.</p>';
        return;
    }

    try {
        // Fetch phone details from the backend
       
        const response = await fetch(`../php/get_phones_details.php?id=${phoneId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();

        if (result.success && result.data) {
            const phone = result.data;
            
            const imageUrl = phone.image_url && phone.image_url !== 'null' && phone.image_url !== '' ? phone.image_url : 'https://via.placeholder.com/400x300?text=No+Image';

            phoneDetailsDiv.innerHTML = `
                <img src="${imageUrl}" alt="${phone.brand} ${phone.model}">
                <div class="phone-info">
                    <h1>${phone.brand} ${phone.model}</h1>
                    <p><strong>Color:</strong> ${phone.color || 'N/A'}</p>
                    <p><strong>Stock:</strong> ${phone.stock > 0 ? phone.stock : '<span style="color: red;">Out of Stock</span>'}</p>
                    <p><strong>Description:</strong> ${phone.description || 'No description available.'}</p>
                    <p class="price">Price: $${parseFloat(phone.price).toFixed(2)}</p>
                </div>
            `;
        } else {
            phoneDetailsDiv.innerHTML = `<p style="color: red;">${result.message || 'Phone details not found.'}</p>`;
        }
    } catch (error) {
        console.error('Error fetching phone details:', error);
        phoneDetailsDiv.innerHTML = '<p style="color: red;">Failed to load phone details. Please try again later.</p>';
    }
});