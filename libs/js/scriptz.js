// Updated JavaScript for login functionality
const wrapper = document.querySelector('.wrapper');
const bodyElement = document.querySelector('body');
const headerElement = document.querySelector('header');
const loginLink = document.querySelector('.login-link');
const forgotLink = document.querySelector('.forgot-link');
const btnPopup = document.querySelector('.btnLogin-popup');
const iconClose = document.querySelector('.icon-close');
const front = document.querySelector('.front');
const overlay = document.querySelector('.overlay');

// Show login popup
btnPopup.addEventListener('click', () => {
    wrapper.classList.add('active-popup');
    overlay.classList.add('active');
    bodyElement.style.setProperty('--blur-amount', '10px'); // Add blur effect
    headerElement.style.filter = 'blur(10px)';
    front.classList.add('blurred'); // Apply blur to all content except wrapper
});

// Close popup function
function closePopup() {
    wrapper.classList.remove('active-popup');
    wrapper.classList.remove('active');
    overlay.classList.remove('active');
    headerElement.style.filter = 'blur(0px)';
    bodyElement.style.setProperty('--blur-amount', '0px'); // Remove blur effect
    front.classList.remove('blurred'); // Remove blur effect
}

// Close popup when clicking close icon
iconClose.addEventListener('click', closePopup);

// Close popup when clicking outside (on overlay)
overlay.addEventListener('click', closePopup);

// Switch to forgot password form
forgotLink.addEventListener('click', (e) => {
    e.preventDefault();
    wrapper.classList.add('active');
});

// Switch back to login form
loginLink.addEventListener('click', (e) => {
    e.preventDefault();
    wrapper.classList.remove('active');
});

// Prevent wrapper clicks from closing the popup
wrapper.addEventListener('click', (e) => {
    e.stopPropagation();
});

// Handle escape key to close popup
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && wrapper.classList.contains('active-popup')) {
        closePopup();
    }
});