/**
 * UI Fixes for Theme Toggle and Menu Minimize Buttons
 */

'use strict';

document.addEventListener('DOMContentLoaded', function() {
    // Theme switching (light/dark/system) is handled by main.js via
    // window.templateCustomizer.setStyle(), backed by the official Sneat
    // core-dark.css/theme-default-dark.css stylesheets. This file used to
    // clone the dropdown items and re-implement switching itself, but that
    // ran *after* main.js's listeners and replaced them via cloneNode,
    // permanently disabling the real switcher — and its own fallback set a
    // 'dark-mode' class that no stylesheet targets (the template keys off
    // the 'dark-style' class + swapped <link> hrefs), so toggling changed
    // the icon but never repainted the page. Do not reintroduce a second
    // listener here.

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
