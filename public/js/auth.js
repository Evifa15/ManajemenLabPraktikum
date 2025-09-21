// File: ManajemenLabPraktikum/public/js/auth.js

// Menjalankan semua skrip setelah dokumen HTML dimuat
document.addEventListener('DOMContentLoaded', () => {
    
    // --- LOGIKA UNTUK HALAMAN PILIH PERAN ---
    const roleForm = document.getElementById('roleForm');
    if (roleForm) {
        const roleCards = document.querySelectorAll('.role-card');
        const selectedRoleInput = document.getElementById('selected-role');
        const continueButton = document.querySelector('.continue-btn');

        roleCards.forEach(card => {
            card.addEventListener('click', () => {
                roleCards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                selectedRoleInput.value = card.getAttribute('data-role');
                continueButton.disabled = false;
            });
        });
    }


        const passwordInput = document.getElementById('password-input');
        const showPasswordBtn = document.getElementById('show-password-btn');

        if (showPasswordBtn && passwordInput) {
            showPasswordBtn.addEventListener('click', () => {
                const currentType = passwordInput.getAttribute('type');
                if (currentType === 'password') {
                    passwordInput.setAttribute('type', 'text');
                    showPasswordBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-10-7-10-7a1.8 1.8 0 0 1 2.5-2.5M17.84 5.84A10.07 10.07 0 0 1 12 4c7 0 10 7 10 7a1.8 1.8 0 0 1-2.5 2.5"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                            <path d="M9.9 12.3a2 2 0 0 0 2.8 2.8M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>
                        </svg>
                    `;
                } else {
                    passwordInput.setAttribute('type', 'password');
                    showPasswordBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    `;
                }
            });
        }
    }
);/* This block of code is responsible for automatically removing alert messages after they have been
displayed for 5 seconds. Here's a breakdown of what it does: */

