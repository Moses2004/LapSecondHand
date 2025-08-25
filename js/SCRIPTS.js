// SCRIPTS.js
// --- Start of New Code for Login Logic ---
let isUserLoggedIn = false; 

async function checkLoginStatus() {
    try {
        const response = await fetch('../php/check_login_status.php'); 
        if (!response.ok) {
            throw new Error('Failed to check login status.');
        }
        const data = await response.json();
        isUserLoggedIn = data.logged_in;
        console.log("User login status:", isUserLoggedIn); // For debugging
    } catch (error) {
        console.error('Error checking login status:', error);
        isUserLoggedIn = false; 
    }
}

function adjustUIForLoginStatus() {
    const searchSection = document.getElementById('home-section').querySelector('.search-section');
    if (searchSection) {
        searchSection.style.display = isUserLoggedIn ? 'flex' : 'none'; 
    }
    const authBox = document.querySelector('.auth-box');
    if (authBox) {
        if (isUserLoggedIn) {
            authBox.innerHTML = `
                <a href="#" id="logout-link" class="signup">Logout</a>
            `;
            document.getElementById('logout-link').addEventListener('click', async (e) => {
                e.preventDefault();
                await fetch('../php/logout.php');
                window.location.reload(); 
            });
        } else {
            authBox.innerHTML = `
                <a href="../php/login.php" class="login">Login</a>
                <a href="../php/signup.php" class="signup">Signup</a>
            `;
        }
    }
}
// --- End of New Code for Login Logic ---

document.addEventListener('DOMContentLoaded', async () => {
    await checkLoginStatus(); 
    adjustUIForLoginStatus(); 
    
    const mainContent = document.getElementById('main-content');
    const navLinks = document.querySelectorAll('.nav-link');
    const homeSectionElement = document.getElementById('home-section');
    const homeSectionHTML = homeSectionElement ? homeSectionElement.innerHTML : '';

    const searchInput = document.getElementById('searchInput');
    const categoryDropdown = document.getElementById('categoryDropdownContent');
    const searchButton = document.getElementById('searchButton');
    let selectedCategory = 'All'; 
    const categoryBtn = document.querySelector('.dropdown-btn');

    if (categoryDropdown) {
        categoryDropdown.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (link) {
                event.preventDefault();
                selectedCategory = link.dataset.category;
                categoryBtn.textContent = `${link.textContent} ▾`;
                searchInput.value = ''; // FIX: Clear search input on category click
                searchPhones(searchInput.value, selectedCategory);
            }
        });
    }

    if (searchButton) {
        searchButton.addEventListener('click', () => {
            const searchTerm = searchInput.value;
            searchPhones(searchTerm, selectedCategory);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                const searchTerm = searchInput.value;
                searchPhones(searchTerm, selectedCategory);
            }
        });
    }

    const searchPhones = async (term, category) => {
        const currentPhoneContainer = document.getElementById('phoneContainer');
        if (!currentPhoneContainer) return;

        currentPhoneContainer.innerHTML = '<p>Searching phones...</p>';

        try {
            const url = `../php/search_phones.php?q=${encodeURIComponent(term)}&category=${encodeURIComponent(category === 'All' ? '' : category)}`;
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            currentPhoneContainer.innerHTML = ''; 

            if (data.success && data.data.length > 0) {
                data.data.forEach(phone => {
                    const phoneCard = document.createElement('div');
                    phoneCard.classList.add('phone-card');
                    phoneCard.dataset.phoneId = phone.phone_id; 

                    const imageUrl = phone.image_url && phone.image_url !== 'null' && phone.image_url !== ''
                                    ? `../uploadimages/${phone.image_url}` 
                                    : 'https://via.placeholder.com/200x150?text=No+Image';

                    phoneCard.innerHTML = `
                        <img src="${imageUrl}" alt="${phone.brand} ${phone.model}">
                        <h3>${phone.brand} ${phone.model}</h3>
                        <p>Price: $${parseFloat(phone.price).toFixed(2)}</p>
                        <p>Stock: ${phone.stock}</p>
                    `;
                    currentPhoneContainer.appendChild(phoneCard);
                });
                setupDragScroll(); 
                currentPhoneContainer.removeEventListener('click', handlePhoneCardClick);
                currentPhoneContainer.addEventListener('click', handlePhoneCardClick);
            } else {
                currentPhoneContainer.innerHTML = '<p>No phones found matching your search criteria.</p>';
            }
        } catch (error) {
            console.error('Error searching phones:', error);
            currentPhoneContainer.innerHTML = '<p style="color: red;">Failed to perform search. Please try again.</p>';
        }
    };
    
    window.scrollPhones = function(direction) {
        const currentPhoneContainer = document.getElementById("phoneContainer");
        if (currentPhoneContainer) {
            const scrollAmount = currentPhoneContainer.clientWidth / 2;
            currentPhoneContainer.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });
        }
    };

    let isDragging = false;
    let startX;
    let scrollLeft;

    function setupDragScroll(containerId = "phoneContainer") {
        const container = document.getElementById(containerId);
        if (container) {
            container.removeEventListener("mousedown", handleMouseDown);
            container.removeEventListener("mouseleave", handleMouseLeave);
            container.removeEventListener("mouseup", handleMouseUp);
            container.removeEventListener("mousemove", handleMouseMove);

            container.addEventListener("mousedown", handleMouseDown);
            container.addEventListener("mouseleave", handleMouseLeave);
            container.addEventListener("mouseup", handleMouseUp);
            container.addEventListener("mousemove", handleMouseMove);
        }
    }

    function handleMouseDown(e) {
        const container = this;
        isDragging = true;
        container.classList.add("dragging");
        startX = e.pageX - container.offsetLeft;
        scrollLeft = container.scrollLeft;
    }

    function handleMouseLeave() {
        isDragging = false;
        const container = document.getElementById("phoneContainer"); 
        if (container) container.classList.remove("dragging");
    }

    function handleMouseUp() {
        isDragging = false;
        const container = document.getElementById("phoneContainer"); 
        if (container) container.classList.remove("dragging");
    }

    function handleMouseMove(e) {
        if (!isDragging) return;
        e.preventDefault();
        const container = this;
        const x = e.pageX - container.offsetLeft;
        const walk = (x - startX) * 2;
        container.scrollLeft = scrollLeft - walk;
    }

    const loadFeaturedPhones = async () => {
        const currentPhoneContainer = document.getElementById('phoneContainer');
        if (!currentPhoneContainer) {
            console.warn("phoneContainer element not found.");
            return;
        }

        currentPhoneContainer.innerHTML = '<p>Loading featured phones...</p>'; 

        try {
            const response = await fetch('../php/get_phones.php'); 
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
            }
            const data = await response.json();

            if (data.success && data.data.length > 0) {
                currentPhoneContainer.innerHTML = ''; 

                data.data.forEach(phone => {
                    const phoneCard = document.createElement('div');
                    phoneCard.classList.add('phone-card');
                    phoneCard.dataset.phoneId = phone.phone_id; 

                    const imageUrl = phone.image_url && phone.image_url !== 'null' && phone.image_url !== ''
                                    ? `../uploadimages/${phone.image_url}` 
                                    : 'https://via.placeholder.com/200x150?text=No+Image';

                    phoneCard.innerHTML = `
                        <img src="${imageUrl}" alt="${phone.brand} ${phone.model}">
                        <h3>${phone.brand} ${phone.model}</h3>
                        <p>Price: $${parseFloat(phone.price).toFixed(2)}</p>
                        <p>Stock: ${phone.stock}</p>
                    `;
                    currentPhoneContainer.appendChild(phoneCard);
                });

                currentPhoneContainer.removeEventListener('click', handlePhoneCardClick); 
                currentPhoneContainer.addEventListener('click', handlePhoneCardClick);
                setupDragScroll();
            } else {
                currentPhoneContainer.innerHTML = '<p>No featured phones available at the moment.</p>';
            }
        } catch (error) {
            console.error('Error loading featured phones:', error);
            currentPhoneContainer.innerHTML = '<p style="color: red;">Failed to load featured phones. Please try again.</p>';
        }
    };

    const handlePhoneCardClick = (event) => {
        const phoneCard = event.target.closest('.phone-card');
        if (phoneCard) {
            const phoneId = phoneCard.dataset.phoneId;
            if (phoneId) {
                loadContent('phone_detail', phoneId);
                window.location.hash = `phone_detail?id=${phoneId}`; 
            }
        }
    };

    async function loadContent(pageName, phoneId = null) {
        const sections = mainContent.querySelectorAll('.content-section');
        sections.forEach(sec => sec.style.display = 'none');

        if (pageName === 'home') {
            mainContent.innerHTML = `<section id="home-section" class="active-section content-section">${homeSectionHTML}</section>`;
            
            // Re-get element references after a new home section is injected
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const categoryDropdown = document.getElementById('categoryDropdownContent');
            const categoryBtn = document.querySelector('.dropdown-btn');
            let selectedCategory = 'All'; // Reset selected category

            // Re-attach all event listeners
            if (searchButton) {
                searchButton.addEventListener('click', () => {
                    const searchTerm = searchInput.value;
                    searchPhones(searchTerm, selectedCategory);
                });
            }
            if (searchInput) {
                searchInput.addEventListener('keypress', (event) => {
                    if (event.key === 'Enter') {
                        const searchTerm = searchInput.value;
                        searchPhones(searchTerm, selectedCategory);
                    }
                });
            }
            if (categoryDropdown) {
                categoryDropdown.addEventListener('click', (event) => {
                    const link = event.target.closest('a');
                    if (link) {
                        event.preventDefault();
                        selectedCategory = link.dataset.category;
                        categoryBtn.textContent = `${link.textContent} ▾`;
                        searchInput.value = ''; // Clear input on category click
                        searchPhones(searchInput.value, selectedCategory);
                    }
                });
            }
            
            loadFeaturedPhones();
            adjustUIForLoginStatus(); 
        } else if (pageName === 'phone_detail' && phoneId) {
            mainContent.innerHTML = `
                <section class="content-section" id="phone-detail-section">
                    <h2>Phone Details</h2>
                    <div id="phone-details-container">
                        <p>Loading phone details for ID: ${phoneId}...</p>
                    </div>
                    <button id="backToHomeBtn" class="back-button" style="margin-top: 20px; padding: 10px 20px; background-color: #003366; color: white; border: none; border-radius: 5px; cursor: pointer;">Back to Home</button>
                </section>
            `;
            const phoneDetailSection = document.getElementById('phone-detail-section');
            if(phoneDetailSection) phoneDetailSection.style.display = 'block';

            const backBtn = document.getElementById('backToHomeBtn');
            if (backBtn) {
                backBtn.addEventListener('click', () => {
                    window.location.hash = 'home';
                    loadContent('home');
                });
            }
            await fetchPhoneDetails(phoneId); 
        } else {
            try {
                const response = await fetch(`../html/${pageName}.html`); 
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const htmlContent = await response.text();
                
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlContent;
                
                const contentSection = tempDiv.querySelector('.content-section'); 
                
                if (contentSection) {
                    mainContent.innerHTML = ''; 
                    mainContent.appendChild(contentSection); 
                    contentSection.style.display = 'block'; 
                } else {
                    console.warn(`No .content-section found in ../html/${pageName}.html. Check the HTML file structure.`);
                    mainContent.innerHTML = `<section class="content-section"><h2>Error</h2><p>Content section not found in ${pageName} page.</p></section>`;
                    const errorSection = mainContent.querySelector('.content-section');
                    if(errorSection) errorSection.style.display = 'block';
                }
                
            } catch (error) {
                console.error('Error loading content:', error);
                mainContent.innerHTML = `<section class="content-section"><h2>Error</h2><p>Could not load ${pageName} page.</p></section>`;
                const errorSection = mainContent.querySelector('.content-section');
                if(errorSection) errorSection.style.display = 'block';
            }
        }
    }

    async function fetchPhoneDetails(phoneId) {
        const phoneDetailsContainer = document.getElementById('phone-details-container');
        if (!phoneDetailsContainer) {
            console.error("Phone details container not found.");
            return;
        }

        try {
            const response = await fetch(`../php/get_phones_details.php?id=${phoneId}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();

            if (data.success && data.data) { 
                const phone = data.data; 
                const imageUrl = phone.image_url && phone.image_url !== 'null' && phone.image_url !== ''
                                            ? `../uploadimages/${phone.image_url}` 
                                            : 'https://via.placeholder.com/400x300?text=No+Image';
                
                let buyButtonHTML = '';
                if (isUserLoggedIn) { 
                    buyButtonHTML = `<button style="padding: 10px 20px; background-color: #003366; color: white; border: none; border-radius: 5px; cursor: pointer;">Buy Now</button>`;
                } else {
                    buyButtonHTML = `<p style="color: grey; font-size: 0.9em;">Please <a href="../php/login.php" class="login-prompt-link">log in</a> to purchase this phone.</p>`;
                }

                phoneDetailsContainer.innerHTML = `
                    <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
                        <img src="${imageUrl}" alt="${phone.brand} ${phone.model}" style="max-width: 400px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        <div>
                            <h3>${phone.brand} ${phone.model}</h3>
                            <p><strong>Price:</strong> $${parseFloat(phone.price).toFixed(2)}</p>
                            <p><strong>Color:</strong> ${phone.color || 'N/A'}</p>
                            <p><strong>Stock:</strong> ${phone.stock > 0 ? phone.stock : 'Out of Stock'}</p>
                            <p><strong>Description:</strong> ${phone.description || 'No description available.'}</p>
                            ${buyButtonHTML} 
                        </div>
                    </div>
                `;
            } else {
                phoneDetailsContainer.innerHTML = `<p style="color: red;">Phone details not found or error: ${data.message || 'Unknown error'}</p>`;
            }
        } catch (error) {
            console.error('Error fetching phone details:', error);
            phoneDetailsContainer.innerHTML = `<p style="color: red;">Failed to load phone details. Please check network or server configuration.</p>`;
        }
    }

    navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault(); 
            
            navLinks.forEach(nav => nav.classList.remove('active'));
            link.classList.add('active');

            const targetPage = link.getAttribute('data-target');
            window.location.hash = targetPage; 
            loadContent(targetPage);
        });
    });

    const initialHash = window.location.hash.substring(1); 
    const detailMatch = initialHash.match(/^phone_detail\?id=(\d+)$/);

    if (detailMatch) {
        const phoneId = detailMatch[1];
        loadContent('phone_detail', phoneId);
        document.querySelector('nav a[data-target="home"]').classList.add('active'); 
    } else if (initialHash && ['home', 'about', 'contact'].includes(initialHash)) {
        navLinks.forEach(link => {
            if (link.getAttribute('data-target') === initialHash) {
                link.classList.add('active');
            }
        });
        loadContent(initialHash);
    } else {
        document.querySelector('nav a[data-target="home"]').classList.add('active');
        loadContent('home');
        window.location.hash = 'home';
    }

    window.addEventListener('hashchange', () => {
        const newHash = window.location.hash.substring(1);
        const detailMatch = newHash.match(/^phone_detail\?id=(\d+)$/);

        navLinks.forEach(link => link.classList.remove('active')); 
        
        if (detailMatch) {
            const phoneId = detailMatch[1];
            loadContent('phone_detail', phoneId);
            document.querySelector('nav a[data-target="home"]').classList.add('active'); 
        } else if (['home', 'about', 'contact'].includes(newHash)) {
            navLinks.forEach(link => {
                if (link.getAttribute('data-target') === newHash) {
                    link.classList.add('active');
                }
            });
            loadContent(newHash);
        } else {
            document.querySelector('nav a[data-target="home"]').classList.add('active');
            loadContent('home');
            window.location.hash = 'home'; 
        }
    });
});