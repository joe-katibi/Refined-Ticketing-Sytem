/**
 * Menu Toggle Fix
 * Ensures proper menu expansion/collapse functionality including nested submenus
 */

document.addEventListener('DOMContentLoaded', function() {
    // Wait for the main menu to be initialized
    setTimeout(function() {
        initializeMenuToggle();
    }, 500);
});

function initializeMenuToggle() {
    // Get all menu toggle links and menu items with submenus
    const menuToggles = document.querySelectorAll('.menu-toggle');
    const menuItemsWithSubmenus = document.querySelectorAll('.menu-item.has-sub, .menu-item[data-has-submenu]');
    
    // Handle standard menu toggles
    menuToggles.forEach(function(toggle) {
        // Remove any existing event listeners to avoid conflicts
        toggle.removeEventListener('click', handleMenuToggle);
        
        // Add our custom click handler
        toggle.addEventListener('click', handleMenuToggle);
    });
    
    // Handle menu items that might not have the .menu-toggle class but have submenus
    menuItemsWithSubmenus.forEach(function(menuItem) {
        const menuLink = menuItem.querySelector('a:first-child');
        if (menuLink && menuItem.querySelector('.menu-sub')) {
            menuLink.removeEventListener('click', handleMenuToggle);
            menuLink.addEventListener('click', handleMenuToggle);
        }
    });
    
    // Handle all clickable menu items that contain submenus (catch-all approach)
    const allMenuItems = document.querySelectorAll('.menu-item');
    allMenuItems.forEach(function(menuItem) {
        const submenu = menuItem.querySelector('.menu-sub');
        const menuLink = menuItem.querySelector('a:first-child');
        
        if (submenu && menuLink) {
            // Check if this menu item should be toggleable
            const href = menuLink.getAttribute('href');
            if (!href || href === '#' || href === 'javascript:void(0);') {
                menuLink.removeEventListener('click', handleMenuToggle);
                menuLink.addEventListener('click', handleMenuToggle);
            }
        }
    });
}

function handleMenuToggle(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const toggleLink = event.currentTarget;
    const menuItem = toggleLink.closest('.menu-item');
    const submenu = menuItem.querySelector('.menu-sub');
    
    if (!menuItem || !submenu) {
        return;
    }
    
    // Toggle the open class
    if (menuItem.classList.contains('open')) {
        // Close the menu and all nested submenus
        menuItem.classList.remove('open');
        submenu.style.display = 'none';
        
        // Close any nested submenus
        const nestedOpenItems = submenu.querySelectorAll('.menu-item.open');
        nestedOpenItems.forEach(function(nestedItem) {
            nestedItem.classList.remove('open');
            const nestedSubmenu = nestedItem.querySelector('.menu-sub');
            if (nestedSubmenu) {
                nestedSubmenu.style.display = 'none';
            }
        });
    } else {
        // Open the menu
        menuItem.classList.add('open');
        submenu.style.display = 'block';
        
        // Optional: Close other open menus at the same level (accordion behavior)
        const parentContainer = menuItem.parentElement;
        if (parentContainer) {
            const siblingOpenItems = parentContainer.querySelectorAll(':scope > .menu-item.open');
            siblingOpenItems.forEach(function(item) {
                if (item !== menuItem) {
                    item.classList.remove('open');
                    const otherSubmenu = item.querySelector('.menu-sub');
                    if (otherSubmenu) {
                        otherSubmenu.style.display = 'none';
                        
                        // Close nested items too
                        const nestedOpenItems = otherSubmenu.querySelectorAll('.menu-item.open');
                        nestedOpenItems.forEach(function(nestedItem) {
                            nestedItem.classList.remove('open');
                            const nestedSubmenu = nestedItem.querySelector('.menu-sub');
                            if (nestedSubmenu) {
                                nestedSubmenu.style.display = 'none';
                            }
                        });
                    }
                }
            });
        }
    }
}

// Reinitialize if the page is dynamically updated
window.reinitializeMenuToggle = initializeMenuToggle;
