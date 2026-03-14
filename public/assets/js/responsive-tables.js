/**
 * Responsive Tables JavaScript
 * Handles dynamic table responsiveness and mobile card view switching
 */

class ResponsiveTableManager {
    constructor() {
        this.breakpoints = {
            mobile: 575.98,
            tablet: 767.98,
            laptop: 991.98
        };
        
        this.init();
    }

    init() {
        this.handleWindowResize();
        this.initializeExistingTables();
        this.setupEventListeners();
    }

    setupEventListeners() {
        window.addEventListener('resize', this.debounce(() => {
            this.handleWindowResize();
        }, 250));

        // Handle DataTables responsive events
        $(document).on('responsive-resize.dt', '.dataTable', (e, datatable, columns) => {
            this.handleDataTableResize(datatable, columns);
        });
    }

    handleWindowResize() {
        const width = window.innerWidth;
        
        // Update all responsive tables
        document.querySelectorAll('.responsive-table-wrapper').forEach(wrapper => {
            this.updateTableResponsiveness(wrapper, width);
        });

        // Update mobile card views
        this.toggleMobileCardViews(width);
    }

    initializeExistingTables() {
        // Convert existing tables to responsive
        document.querySelectorAll('table:not(.responsive-table)').forEach(table => {
            if (!table.closest('.dataTable')) {
                this.makeTableResponsive(table);
            }
        });

        // Initialize DataTables with responsive options
        this.initializeDataTables();
    }

    makeTableResponsive(table) {
        // Skip if already wrapped
        if (table.closest('.responsive-table-wrapper')) {
            return;
        }

        // Add responsive classes
        table.classList.add('responsive-table', 'table', 'table-hover');

        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'responsive-table-wrapper';
        
        // Wrap the table
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);

        // Create mobile card view
        this.createMobileCardView(table);

        // Update responsiveness
        this.updateTableResponsiveness(wrapper, window.innerWidth);
    }

    createMobileCardView(table) {
        const wrapper = table.closest('.responsive-table-wrapper').parentNode;
        
        // Create mobile card container
        const mobileView = document.createElement('div');
        mobileView.className = 'mobile-card-view d-none';
        
        // Get table data
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        const rows = Array.from(table.querySelectorAll('tbody tr'));

        rows.forEach((row, index) => {
            const cells = Array.from(row.querySelectorAll('td'));
            
            const card = document.createElement('div');
            card.className = 'mobile-card';
            
            // Card header with primary info
            const cardHeader = document.createElement('div');
            cardHeader.className = 'mobile-card-header';
            cardHeader.textContent = cells[0]?.textContent.trim() || `Item ${index + 1}`;
            card.appendChild(cardHeader);

            // Card rows for each data field
            cells.forEach((cell, cellIndex) => {
                if (cellIndex === 0) return; // Skip first cell as it's used in header

                const cardRow = document.createElement('div');
                cardRow.className = 'mobile-card-row';

                const label = document.createElement('div');
                label.className = 'mobile-card-label';
                label.textContent = headers[cellIndex] || `Field ${cellIndex}`;

                const value = document.createElement('div');
                value.className = 'mobile-card-value';
                value.innerHTML = cell.innerHTML;

                cardRow.appendChild(label);
                cardRow.appendChild(value);
                card.appendChild(cardRow);
            });

            mobileView.appendChild(card);
        });

        // Insert mobile view after table wrapper
        wrapper.insertBefore(mobileView, table.closest('.responsive-table-wrapper').nextSibling);
    }

    updateTableResponsiveness(wrapper, width) {
        const table = wrapper.querySelector('.responsive-table');
        if (!table) return;

        // Remove existing responsive classes
        table.querySelectorAll('th, td').forEach(cell => {
            cell.classList.remove('d-none-mobile', 'd-none-tablet', 'd-none-laptop');
        });

        // Apply responsive classes based on screen size
        if (width <= this.breakpoints.mobile) {
            this.applyMobileHiding(table);
        } else if (width <= this.breakpoints.tablet) {
            this.applyTabletHiding(table);
        } else if (width <= this.breakpoints.laptop) {
            this.applyLaptopHiding(table);
        }
    }

    applyMobileHiding(table) {
        // Hide less important columns on mobile
        const hideColumns = [2, 3, 4, 5]; // Adjust based on your table structure
        
        hideColumns.forEach(colIndex => {
            const cells = table.querySelectorAll(`th:nth-child(${colIndex + 1}), td:nth-child(${colIndex + 1})`);
            cells.forEach(cell => cell.classList.add('d-none-mobile'));
        });
    }

    applyTabletHiding(table) {
        // Hide some columns on tablet
        const hideColumns = [3, 4]; // Adjust based on your table structure
        
        hideColumns.forEach(colIndex => {
            const cells = table.querySelectorAll(`th:nth-child(${colIndex + 1}), td:nth-child(${colIndex + 1})`);
            cells.forEach(cell => cell.classList.add('d-none-tablet'));
        });
    }

    applyLaptopHiding(table) {
        // Hide fewer columns on laptop
        const hideColumns = [4]; // Adjust based on your table structure
        
        hideColumns.forEach(colIndex => {
            const cells = table.querySelectorAll(`th:nth-child(${colIndex + 1}), td:nth-child(${colIndex + 1})`);
            cells.forEach(cell => cell.classList.add('d-none-laptop'));
        });
    }

    toggleMobileCardViews(width) {
        document.querySelectorAll('.responsive-table-wrapper').forEach(wrapper => {
            const table = wrapper.querySelector('.responsive-table');
            const mobileView = wrapper.parentNode.querySelector('.mobile-card-view');
            
            if (!table || !mobileView) return;

            if (width <= this.breakpoints.mobile) {
                // Show mobile cards, hide table
                wrapper.style.display = 'none';
                mobileView.classList.remove('d-none');
            } else {
                // Show table, hide mobile cards
                wrapper.style.display = 'block';
                mobileView.classList.add('d-none');
            }
        });
    }

    initializeDataTables() {
        // Enhanced DataTables responsive configuration
        const responsiveConfig = {
            responsive: {
                breakpoints: [
                    { name: 'bigdesktop', width: Infinity },
                    { name: 'meddesktop', width: 1480 },
                    { name: 'smalldesktop', width: 1280 },
                    { name: 'medium', width: 1188 },
                    { name: 'tabletl', width: 1024 },
                    { name: 'btwtabllandp', width: 848 },
                    { name: 'tabletp', width: 768 },
                    { name: 'mobilel', width: 480 },
                    { name: 'mobilep', width: 320 }
                ],
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            columnDefs: [
                {
                    className: 'control',
                    orderable: false,
                    targets: 0,
                    responsivePriority: 1
                }
            ],
            order: [[1, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                lengthMenu: "_MENU_ records per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries to show",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                },
                emptyTable: "No data available in table"
            }
        };

        // Apply to existing DataTables
        if (typeof $.fn.DataTable !== 'undefined') {
            $('.dataTable').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable(responsiveConfig);
                }
            });
        }
    }

    handleDataTableResize(datatable, columns) {
        // Custom handling for DataTable responsive events
        console.log('DataTable resized:', datatable, columns);
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

    // Public method to refresh all tables
    refresh() {
        this.handleWindowResize();
    }

    // Public method to add responsive behavior to new tables
    addTable(tableElement) {
        this.makeTableResponsive(tableElement);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.responsiveTableManager = new ResponsiveTableManager();
});

// jQuery plugin for easy integration
if (typeof $ !== 'undefined') {
    $.fn.makeResponsive = function() {
        return this.each(function() {
            if (window.responsiveTableManager) {
                window.responsiveTableManager.addTable(this);
            }
        });
    };
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ResponsiveTableManager;
}
