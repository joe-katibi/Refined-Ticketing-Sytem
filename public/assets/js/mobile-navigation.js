/**
 * Mobile Navigation JavaScript
 * Handles responsive navigation, sidebar toggling, and mobile menu interactions
 */

class MobileNavigationManager {
    constructor() {
        this.breakpoint = 1199.98;
        this.isMenuOpen = false;
        this.init();
    }

    init() {
        this.createMobileToggle();
        this.setupEventListeners();
        this.handleWindowResize();
        this.initializeMenuState();
    }

    createMobileToggle() {
        // Create mobile menu toggle button if it doesn't exist
        if (!document.querySelector('.mobile-menu-toggle')) {
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'mobile-menu-toggle';
            toggleBtn.innerHTML = '<i class="bx bx-menu"></i>';
            toggleBtn.setAttribute('aria-label', 'Toggle navigation menu');
            
            document.body.appendChild(toggleBtn);
        }
    }

    setupEventListeners() {
        // Mobile toggle button
        document.addEventListener('click', (e) => {
            if (e.target.closest('.mobile-menu-toggle')) {
                this.toggleMobileMenu();
            }
        });

        // Layout overlay click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('layout-overlay')) {
                this.closeMobileMenu();
            }
        });

        // Menu toggle functionality
        document.addEventListener('click', (e) => {
            if (e.target.closest('.menu-toggle')) {
                e.preventDefault();
                this.toggleSubmenu(e.target.closest('.menu-item'));
            }
        });

        // Window resize
        window.addEventListener('resize', this.debounce(() => {
            this.handleWindowResize();
        }, 250));

        // Escape key to close menu
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen) {
                this.closeMobileMenu();
            }
        });

        // Touch events for mobile
        this.setupTouchEvents();
    }

    setupTouchEvents() {
        let startX = 0;
        let currentX = 0;
        let isDragging = false;

        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
        });

        document.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentX = e.touches[0].clientX;
        });

        document.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;

            const diffX = currentX - startX;
            const threshold = 100;

            // Swipe right to open menu (from left edge)
            if (startX < 50 && diffX > threshold && !this.isMenuOpen) {
                this.openMobileMenu();
            }
            // Swipe left to close menu
            else if (diffX < -threshold && this.isMenuOpen) {
                this.closeMobileMenu();
            }
        });
    }

    toggleMobileMenu() {
        if (this.isMenuOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    }

    openMobileMenu() {
        const menu = document.getElementById('layout-menu');
        const overlay = document.querySelector('.layout-overlay');
        const toggleBtn = document.querySelector('.mobile-menu-toggle');

        if (menu) {
            menu.classList.add('show');
            this.isMenuOpen = true;
        }

        if (overlay) {
            overlay.classList.add('show');
        }

        if (toggleBtn) {
            toggleBtn.innerHTML = '<i class="bx bx-x"></i>';
        }

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Focus management
        this.trapFocus(menu);
    }

    closeMobileMenu() {
        const menu = document.getElementById('layout-menu');
        const overlay = document.querySelector('.layout-overlay');
        const toggleBtn = document.querySelector('.mobile-menu-toggle');

        if (menu) {
            menu.classList.remove('show');
            this.isMenuOpen = false;
        }

        if (overlay) {
            overlay.classList.remove('show');
        }

        if (toggleBtn) {
            toggleBtn.innerHTML = '<i class="bx bx-menu"></i>';
        }

        // Restore body scroll
        document.body.style.overflow = '';
    }

    toggleSubmenu(menuItem) {
        if (!menuItem) return;

        const submenu = menuItem.querySelector('.menu-sub');
        const isOpen = menuItem.classList.contains('open');

        // Close other open submenus (accordion behavior)
        if (!isOpen) {
            const openItems = document.querySelectorAll('.menu-item.open');
            openItems.forEach(item => {
                if (item !== menuItem) {
                    item.classList.remove('open');
                }
            });
        }

        // Toggle current submenu
        menuItem.classList.toggle('open');

        // Animate submenu
        if (submenu) {
            if (menuItem.classList.contains('open')) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
            } else {
                submenu.style.maxHeight = '0';
            }
        }
    }

    handleWindowResize() {
        const width = window.innerWidth;

        if (width > this.breakpoint) {
            // Desktop view
            this.closeMobileMenu();
            this.showDesktopMenu();
        } else {
            // Mobile view
            this.showMobileMenu();
        }

        this.updateToggleVisibility();
    }

    showDesktopMenu() {
        const menu = document.getElementById('layout-menu');
        if (menu) {
            menu.classList.remove('show');
            menu.style.transform = '';
        }
    }

    showMobileMenu() {
        const menu = document.getElementById('layout-menu');
        if (menu && !this.isMenuOpen) {
            menu.style.transform = 'translateX(-100%)';
        }
    }

    updateToggleVisibility() {
        const toggleBtn = document.querySelector('.mobile-menu-toggle');
        if (toggleBtn) {
            if (window.innerWidth <= this.breakpoint) {
                toggleBtn.style.display = 'block';
            } else {
                toggleBtn.style.display = 'none';
            }
        }
    }

    initializeMenuState() {
        // Set initial menu state based on current route
        const currentPath = window.location.pathname;
        const menuItems = document.querySelectorAll('.menu-item');

        menuItems.forEach(item => {
            const link = item.querySelector('.menu-link');
            const submenu = item.querySelector('.menu-sub');

            if (link && submenu) {
                const href = link.getAttribute('href');
                const submenuLinks = submenu.querySelectorAll('.menu-link');
                
                let hasActiveSubmenu = false;
                submenuLinks.forEach(subLink => {
                    const subHref = subLink.getAttribute('href');
                    if (subHref && currentPath.includes(subHref.replace(window.location.origin, ''))) {
                        hasActiveSubmenu = true;
                        subLink.classList.add('active');
                    }
                });

                if (hasActiveSubmenu || (href && currentPath.includes(href.replace(window.location.origin, '')))) {
                    item.classList.add('open');
                    if (submenu) {
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    }
                    link.classList.add('active');
                }
            }
        });
    }

    trapFocus(element) {
        if (!element) return;

        const focusableElements = element.querySelectorAll(
            'a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select'
        );

        const firstFocusableElement = focusableElements[0];
        const lastFocusableElement = focusableElements[focusableElements.length - 1];

        element.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusableElement) {
                        lastFocusableElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusableElement) {
                        firstFocusableElement.focus();
                        e.preventDefault();
                    }
                }
            }
        });

        // Focus first element
        if (firstFocusableElement) {
            firstFocusableElement.focus();
        }
    }

    // Utility function for debouncing
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Public method to refresh navigation
    refresh() {
        this.handleWindowResize();
        this.initializeMenuState();
    }

    // Public method to close menu programmatically
    close() {
        this.closeMobileMenu();
    }

    // Public method to open menu programmatically
    open() {
        if (window.innerWidth <= this.breakpoint) {
            this.openMobileMenu();
        }
    }
}

// Enhanced dropdown functionality
class ResponsiveDropdowns {
    constructor() {
        this.init();
    }

    init() {
        this.setupDropdownToggles();
        this.setupClickOutside();
    }

    setupDropdownToggles() {
        document.addEventListener('click', (e) => {
            const dropdownToggle = e.target.closest('[data-bs-toggle="dropdown"]');
            if (dropdownToggle) {
                e.preventDefault();
                this.toggleDropdown(dropdownToggle);
            }
        });
    }

    setupClickOutside() {
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                this.closeAllDropdowns();
            }
        });
    }

    toggleDropdown(toggle) {
        const dropdown = toggle.closest('.dropdown');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (!dropdown || !menu) return;

        const isOpen = dropdown.classList.contains('show');

        // Close all other dropdowns
        this.closeAllDropdowns();

        if (!isOpen) {
            dropdown.classList.add('show');
            menu.classList.add('show');
            
            // Position dropdown for mobile
            if (window.innerWidth <= 575.98) {
                this.positionMobileDropdown(menu);
            }
        }
    }

    closeAllDropdowns() {
        const openDropdowns = document.querySelectorAll('.dropdown.show');
        openDropdowns.forEach(dropdown => {
            dropdown.classList.remove('show');
            const menu = dropdown.querySelector('.dropdown-menu');
            if (menu) {
                menu.classList.remove('show');
            }
        });
    }

    positionMobileDropdown(menu) {
        menu.style.position = 'static';
        menu.style.float = 'none';
        menu.style.width = '100%';
        menu.style.marginTop = '0';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.mobileNavigationManager = new MobileNavigationManager();
    window.responsiveDropdowns = new ResponsiveDropdowns();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MobileNavigationManager, ResponsiveDropdowns };
}
