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

    // Ensure every page has the unified site footer (if not present)
    const footerExists = document.querySelector('.site-footer');
    if (!footerExists) {
        const footerHTML = `
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-brand">
                <div class="logo">
                    <span class="campus">CAMPUS </span><span class="taskhub">TASKHUB</span>
                </div>
                <p class="footer-tagline">Empowering students to hustle, help out, and get things done on campus.</p>
            </div>

            <div class="footer-links-group">
                <div class="footer-column">
                    <h4>Platform</h4>
                    <a href="browseTask.html">Browse Tasks</a>
                    <a href="posttasks.html">Post a Task</a>
                    <a href="leaderboard.html">Leaderboard</a>
                </div>

                <div class="footer-column">
                    <h4>Support</h4>
                    <a href="#">FAQ / Help Center</a>
                    <a href="#">Safety Guidelines</a>
                    <a href="#">Contact Us</a>
                </div>

                <div class="footer-column">
                    <h4>Legal</h4>
                    <a href="#">Terms of Service</a>
                    <a href="#">Privacy Policy</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 Campus TaskHub. All rights reserved.</p>
            <div class="social-icons">
                <a href="#">📱 TikTok</a>
                <a href="#">📸 Instagram</a>
                <a href="#">✖️ X (Twitter)</a>
            </div>
        </div>
    </footer>`;

        try {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = footerHTML;
            document.body.appendChild(wrapper.firstElementChild);
        } catch (e) {
            console.error('Failed to append site footer:', e);
        }
    }
});