/**
 * UI Fixes for Theme Toggle and Menu Minimize Buttons
 */

'use strict';

document.addEventListener('DOMContentLoaded', function() {
    // Fix for theme toggle button
    const styleSwitcher = document.querySelector('.dropdown-style-switcher');
    if (styleSwitcher) {
        const styleSwitcherItems = [].slice.call(styleSwitcher.querySelectorAll('.dropdown-item'));
        const styleSwitcherIcon = styleSwitcher.querySelector('i');
        
        // Simple direct theme switching function
        function applyTheme(theme) {
            // Save the theme preference to localStorage
            localStorage.setItem('templateCustomizer-' + window.templateName + '--Style', theme);
            
            const html = document.documentElement;
            
            if (theme === 'dark') {
                // Apply dark theme
                html.classList.add('dark-mode');
                html.setAttribute('data-theme', 'dark');
                
                // Update icon
                if (styleSwitcherIcon) {
                    styleSwitcherIcon.classList.remove('ti-sun', 'ti-moon', 'ti-device-desktop');
                    styleSwitcherIcon.classList.add('ti-moon');
                }
                
                // Also try to use the built-in theme system
                if (window.templateCustomizer) {
                    try {
                        window.templateCustomizer.setStyle('dark');
                    } catch (e) {
                        console.log('Using fallback dark mode');
                    }
                }
            } else if (theme === 'light') {
                // Apply light theme
                html.classList.remove('dark-mode');
                html.setAttribute('data-theme', 'light');
                
                // Update icon
                if (styleSwitcherIcon) {
                    styleSwitcherIcon.classList.remove('ti-sun', 'ti-moon', 'ti-device-desktop');
                    styleSwitcherIcon.classList.add('ti-sun');
                }
                
                // Also try to use the built-in theme system
                if (window.templateCustomizer) {
                    try {
                        window.templateCustomizer.setStyle('light');
                    } catch (e) {
                        console.log('Using fallback light mode');
                    }
                }
            } else if (theme === 'system') {
                // Check system preference
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                if (prefersDark) {
                    // Apply dark theme
                    html.classList.add('dark-mode');
                    html.setAttribute('data-theme', 'dark');
                    
                    // Update icon
                    if (styleSwitcherIcon) {
                        styleSwitcherIcon.classList.remove('ti-sun', 'ti-moon', 'ti-device-desktop');
                        styleSwitcherIcon.classList.add('ti-device-desktop');
                    }
                    
                    // Also try to use the built-in theme system
                    if (window.templateCustomizer) {
                        try {
                            window.templateCustomizer.setStyle('dark');
                        } catch (e) {
                            console.log('Using fallback dark mode (system)');
                        }
                    }
                } else {
                    // Apply light theme
                    html.classList.remove('dark-mode');
                    html.setAttribute('data-theme', 'light');
                    
                    // Update icon
                    if (styleSwitcherIcon) {
                        styleSwitcherIcon.classList.remove('ti-sun', 'ti-moon', 'ti-device-desktop');
                        styleSwitcherIcon.classList.add('ti-device-desktop');
                    }
                    
                    // Also try to use the built-in theme system
                    if (window.templateCustomizer) {
                        try {
                            window.templateCustomizer.setStyle('light');
                        } catch (e) {
                            console.log('Using fallback light mode (system)');
                        }
                    }
                }
            }
        }
        
        // Set the initial icon and theme based on current theme
        const storedStyle = localStorage.getItem('templateCustomizer-' + window.templateName + '--Style') || 'light';
        applyTheme(storedStyle);

        
        // Ensure the theme toggle button works properly
        styleSwitcherItems.forEach(function(item) {
            // Remove any existing event listeners
            const newItem = item.cloneNode(true);
            item.parentNode.replaceChild(newItem, item);
            
            // Add new event listener with our direct theme switching
            newItem.addEventListener('click', function() {
                const currentStyle = this.getAttribute('data-theme');
                applyTheme(currentStyle);
            });
        });
        
        // Apply the current theme on page load
        const currentTheme = localStorage.getItem('templateCustomizer-' + window.templateName + '--Style') || 'light';
        applyTheme(currentTheme);
    }

    // Fix for menu minimizing button
    const menuToggler = document.querySelectorAll('.layout-menu-toggle');
    if (menuToggler.length > 0) {
        menuToggler.forEach(item => {
            // Ensure existing event listeners are removed to prevent duplicates
            const newItem = item.cloneNode(true);
            item.parentNode.replaceChild(newItem, item);
            
            // Add new event listener
            newItem.addEventListener('click', event => {
                event.preventDefault();
                
                // Toggle menu collapsed state
                if (window.Helpers && typeof window.Helpers.toggleCollapsed === 'function') {
                    window.Helpers.toggleCollapsed();
                    
                    // Enable menu state with local storage support
                    if (window.config && window.config.enableMenuLocalStorage && !window.Helpers.isSmallScreen()) {
                        try {
                            localStorage.setItem(
                                'templateCustomizer-' + window.templateName + '--LayoutCollapsed',
                                String(window.Helpers.isCollapsed())
                            );
                            
                            // Update customizer checkbox state
                            const layoutCollapsedCustomizerOptions = document.querySelector('.template-customizer-layouts-options');
                            if (layoutCollapsedCustomizerOptions) {
                                const layoutCollapsedVal = window.Helpers.isCollapsed() ? 'collapsed' : 'expanded';
                                const targetInput = layoutCollapsedCustomizerOptions.querySelector(`input[value="${layoutCollapsedVal}"]`);
                                if (targetInput) {
                                    targetInput.click();
                                }
                            }
                        } catch (e) {
                            console.error('Error updating menu state:', e);
                        }
                    }
                }
            });
        });
    }

    // Set default font size to 13px
    const fontSizeSwitcher = window.FontSizeSwitcher;
    if (fontSizeSwitcher && typeof fontSizeSwitcher.setFontSize === 'function') {
        // Set default font size to 13px
        fontSizeSwitcher.setFontSize('13px');
    } else {
        // If FontSizeSwitcher is not available, set font size directly
        document.documentElement.style.setProperty('--bs-body-font-size', '13px');
        document.documentElement.style.setProperty('--bs-root-font-size', '13px');
        
        // Store the preference in localStorage
        localStorage.setItem('app-font-size', '13px');
    }
});
