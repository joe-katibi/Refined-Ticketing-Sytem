/**
 * Font Size Switcher
 * Allows users to switch between 12px, 13px, and 14px font sizes
 */

class FontSizeSwitcher {
    constructor() {
        this.currentSize = '13'; // Default size
        this.sizes = ['12', '13', '14'];
        this.init();
    }

    init() {
        this.loadSavedFontSize();
        this.applyFontSize(this.currentSize);
        // Set default 13px font size on page load
        this.setDefaultFontSize();
        // Listen for font size changes from existing UI
        this.setupExistingUIIntegration();
    }

    setDefaultFontSize() {
        // Apply default 13px font size immediately
        document.body.classList.add('font-size-13');
    }

    setupExistingUIIntegration() {
        // Wait for DOM to be fully loaded
        const setupFontSizeSelector = () => {
            const fontSizeSelector = document.getElementById('fontSizeSelector');
            if (fontSizeSelector) {
                // Listen for changes on the existing font size dropdown
                fontSizeSelector.addEventListener('change', (e) => {
                    const selectedValue = e.target.value;
                    let size = '13'; // default
                    
                    // Map the existing values to our font sizes
                    switch (selectedValue) {
                        case 'text-xs':
                            size = '12';
                            break;
                        case 'text-sm':
                            size = '13';
                            break;
                        case 'text-base':
                            size = '14';
                            break;
                    }
                    
                    this.switchFontSize(size);
                });

                // Set the dropdown to match the current saved font size
                this.updateDropdownSelection();
            }
        };

        // Try to setup immediately, or wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupFontSizeSelector);
        } else {
            setupFontSizeSelector();
        }

        // Also try after a short delay in case the dropdown is loaded dynamically
        setTimeout(setupFontSizeSelector, 1000);

        // Expose global function for existing UI to call
        window.setFontSize = (size) => {
            this.switchFontSize(size);
        };
    }

    updateDropdownSelection() {
        const fontSizeSelector = document.getElementById('fontSizeSelector');
        if (fontSizeSelector) {
            let value = 'text-sm'; // default
            
            switch (this.currentSize) {
                case '12':
                    value = 'text-xs';
                    break;
                case '13':
                    value = 'text-sm';
                    break;
                case '14':
                    value = 'text-base';
                    break;
            }
            
            fontSizeSelector.value = value;
        }
    }

    switchFontSize(size) {
        if (!this.sizes.includes(size)) {
            console.warn('Invalid font size:', size);
            return;
        }

        this.currentSize = size;
        this.applyFontSize(size);
        this.saveFontSize(size);
        this.updateDropdownSelection();
    }

    applyFontSize(size) {
        const body = document.body;
        
        // Remove existing font size classes
        this.sizes.forEach(s => {
            body.classList.remove(`font-size-${s}`);
        });

        // Add new font size class
        body.classList.add(`font-size-${size}`);

        // Update CSS custom property for dynamic use
        document.documentElement.style.setProperty('--current-font-size', `${size}px`);
        
        // Trigger a custom event for other components that might need to respond
        window.dispatchEvent(new CustomEvent('fontSizeChanged', { 
            detail: { size: size } 
        }));
    }



    saveFontSize(size) {
        try {
            localStorage.setItem('preferred-font-size', size);
        } catch (e) {
            console.warn('Could not save font size preference:', e);
        }
    }

    loadSavedFontSize() {
        try {
            const saved = localStorage.getItem('preferred-font-size');
            if (saved && this.sizes.includes(saved)) {
                this.currentSize = saved;
            }
        } catch (e) {
            console.warn('Could not load font size preference:', e);
        }
    }

    // Public method to get current font size
    getCurrentSize() {
        return this.currentSize;
    }

    // Public method to set font size programmatically
    setFontSize(size) {
        this.switchFontSize(size);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Create global instance
    window.fontSizeSwitcher = new FontSizeSwitcher();
});

// Also initialize if DOM is already loaded (for dynamic loading)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.fontSizeSwitcher) {
            window.fontSizeSwitcher = new FontSizeSwitcher();
        }
    });
} else {
    if (!window.fontSizeSwitcher) {
        window.fontSizeSwitcher = new FontSizeSwitcher();
    }
}
