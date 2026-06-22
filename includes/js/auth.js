// js/auth.js
document.addEventListener("DOMContentLoaded", function () {
    
    // 1. Get the current page and clean off any ? or # parameters
    let rawPage = window.location.pathname.split('/').pop().toLowerCase();
    const currentPage = rawPage.split('?')[0].split('#')[0];
    
    // 2. Define public pages
    const publicPages = ['login.html', 'signup.html', '']; // Added blank '' for root localhost folders

    fetch('includes/php/check_session.php')
        .then(response => {
            if (!response.ok) throw new Error("Network or PHP error");
            return response.json();
        })
        .then(data => {
            
            console.log("Auth Check:", data.status, "| Current Page:", currentPage);

            if (data.status === 'logged_out') {
                // Only redirect to login if they are NOT on a public page
                if (!publicPages.includes(currentPage)) {
                    window.location.replace('login.html');
                }
            } 
            else if (data.status === 'logged_in') {
                // SUCCESS: Save ID
                sessionStorage.setItem('user_id', data.user_id); 
                
                // UPDATE NAVBAR PROFILE PIC
                if (data.profile_pic) {
                    const navProfilePics = document.querySelectorAll('.profile-pic');
                    navProfilePics.forEach(img => {
                        img.src = data.profile_pic;
                    });
                }

                // If they are logged in but sitting on the login page, push to dashboard
                if (publicPages.includes(currentPage)) {
                    window.location.replace('dashboard.html');
                }
            }
        })
        .catch(error => {
            console.error("Auth check failed (PHP error or network issue):", error);
            
            // Failsafe: Only redirect to login if we are NOT already on the login page
            if (!publicPages.includes(currentPage)) {
                window.location.replace('login.html');
            }
        });
});