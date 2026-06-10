/**
 * Selection Manager Module
 * Handles checkbox selection logic including shift-click range selection,
 * ctrl/cmd-click toggle, selection state tracking across pagination,
 * and selection counter management.
 */

class SelectionManager {
    constructor(options = {}) {
        // Configuration
        this.checkboxSelector = options.checkboxSelector || '.photo-checkbox';
        this.selectAllSelector = options.selectAllSelector || '#select-all';
        this.counterSelector = options.counterSelector || '#selection-count';
        this.containerSelector = options.containerSelector || '#photo-list';

        // State tracking
        this.selectedIds = new Set();
        this.lastCheckedIndex = null;
        this.isInitialized = false;

        // Bind methods to preserve context
        this.handleCheckboxClick = this.handleCheckboxClick.bind(this);
        this.handleSelectAllClick = this.handleSelectAllClick.bind(this);
        this.updateCounter = this.updateCounter.bind(this);
        this.restoreSelectionState = this.restoreSelectionState.bind(this);
    }

    /**
     * Initialize the selection manager
     */
    init() {
        if (this.isInitialized) {
            return;
        }

        // Use event delegation for checkbox clicks
        const container = document.querySelector(this.containerSelector);
        if (container) {
            container.addEventListener('click', this.handleCheckboxClick);
        }

        // Select all checkbox
        const selectAllCheckbox = document.querySelector(this.selectAllSelector);
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', this.handleSelectAllClick);
        }

        // Restore selection state on page load
        this.restoreSelectionState();

        // Update counter on initialization
        this.updateCounter();

        this.isInitialized = true;
    }

    /**
     * Handle individual checkbox clicks with shift and ctrl/cmd support
     */
    handleCheckboxClick(event) {
        const checkbox = event.target.closest(this.checkboxSelector);
        if (!checkbox) return;

        const checkboxes = this.getAllCheckboxes();
        const currentIndex = Array.from(checkboxes).indexOf(checkbox);

        if (currentIndex === -1) return;

        // Handle Shift-click for range selection
        if (event.shiftKey && this.lastCheckedIndex !== null) {
            event.preventDefault();
            this.handleRangeSelection(checkboxes, currentIndex);
        }
        // Handle Ctrl/Cmd-click for toggle (default checkbox behavior)
        else if (event.ctrlKey || event.metaKey) {
            // Let default checkbox behavior handle the toggle
            setTimeout(() => {
                this.updateSelectionState(checkbox);
                this.updateCounter();
            }, 0);
        }
        // Normal click
        else {
            setTimeout(() => {
                this.updateSelectionState(checkbox);
                this.updateCounter();
            }, 0);
        }

        this.lastCheckedIndex = currentIndex;
        this.updateSelectAllState();
    }

    /**
     * Handle range selection with shift-click
     */
    handleRangeSelection(checkboxes, currentIndex) {
        const start = Math.min(this.lastCheckedIndex, currentIndex);
        const end = Math.max(this.lastCheckedIndex, currentIndex);

        // Determine the state to apply (use the state of the last checked item)
        const lastCheckbox = checkboxes[this.lastCheckedIndex];
        const shouldCheck = lastCheckbox.checked;

        // Apply the state to all checkboxes in range
        for (let i = start; i <= end; i++) {
            const cb = checkboxes[i];
            if (cb.checked !== shouldCheck) {
                cb.checked = shouldCheck;
                this.updateSelectionState(cb);
            }
        }

        this.updateCounter();
    }

    /**
     * Handle select all checkbox
     */
    handleSelectAllClick(event) {
        const selectAllCheckbox = event.target;
        const checkboxes = this.getAllCheckboxes();
        const isChecked = selectAllCheckbox.checked;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked !== isChecked) {
                checkbox.checked = isChecked;
                this.updateSelectionState(checkbox);
            }
        });

        this.updateCounter();
    }

    /**
     * Update the selection state for a single checkbox
     */
    updateSelectionState(checkbox) {
        const photoId = this.getPhotoId(checkbox);
        if (!photoId) return;

        if (checkbox.checked) {
            this.selectedIds.add(photoId);
        } else {
            this.selectedIds.delete(photoId);
        }

        // Store in sessionStorage for persistence across pagination
        this.saveSelectionState();
    }

    /**
     * Get photo ID from checkbox element
     */
    getPhotoId(checkbox) {
        // Try data attribute first
        if (checkbox.dataset.photoId) {
            return checkbox.dataset.photoId;
        }

        // Try value attribute
        if (checkbox.value) {
            return checkbox.value;
        }

        // Try ID attribute pattern
        if (checkbox.id) {
            const match = checkbox.id.match(/photo[_-]?(\d+)/i);
            if (match) return match[1];
        }

        // Try name attribute pattern
        if (checkbox.name) {
            const match = checkbox.name.match(/photo[_-]?(\d+)/i);
            if (match) return match[1];
        }

        return null;
    }

    /**
     * Get all checkboxes in the current view
     */
    getAllCheckboxes() {
        return document.querySelectorAll(this.checkboxSelector);
    }

    /**
     * Update the selection counter display
     */
    updateCounter() {
        const counter = document.querySelector(this.counterSelector);
        if (!counter) return;

        const count = this.selectedIds.size;
        counter.textContent = count;

        // Optional: Add/remove classes based on selection
        if (count > 0) {
            counter.classList.add('has-selection');
        } else {
            counter.classList.remove('has-selection');
        }

        // Dispatch custom event for other components
        const event = new CustomEvent('selectionChanged', {
            detail: {
                count: count,
                selectedIds: Array.from(this.selectedIds)
            }
        });
        document.dispatchEvent(event);
    }

    /**
     * Update the select all checkbox state
     */
    updateSelectAllState() {
        const selectAllCheckbox = document.querySelector(this.selectAllSelector);
        if (!selectAllCheckbox) return;

        const checkboxes = this.getAllCheckboxes();
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

        if (checkedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount === checkboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    /**
     * Save selection state to sessionStorage
     */
    saveSelectionState() {
        try {
            const state = Array.from(this.selectedIds);
            sessionStorage.setItem('photoSelection', JSON.stringify(state));
        } catch (error) {
        }
    }

    /**
     * Restore selection state from sessionStorage
     */
    restoreSelectionState() {
        try {
            const savedState = sessionStorage.getItem('photoSelection');
            if (!savedState) return;

            const selectedIds = JSON.parse(savedState);
            this.selectedIds = new Set(selectedIds);

            // Apply to current checkboxes
            const checkboxes = this.getAllCheckboxes();
            checkboxes.forEach(checkbox => {
                const photoId = this.getPhotoId(checkbox);
                if (photoId && this.selectedIds.has(photoId)) {
                    checkbox.checked = true;
                }
            });

            this.updateSelectAllState();
            this.updateCounter();
        } catch (error) {
            // Clear corrupted state
            sessionStorage.removeItem('photoSelection');
        }
    }

    /**
     * Get selected photo IDs
     */
    getSelectedIds() {
        return Array.from(this.selectedIds);
    }

    /**
     * Get selected photo count
     */
    getSelectedCount() {
        return this.selectedIds.size;
    }

    /**
     * Clear all selections
     */
    clearSelection() {
        this.selectedIds.clear();

        const checkboxes = this.getAllCheckboxes();
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        const selectAllCheckbox = document.querySelector(this.selectAllSelector);
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }

        this.saveSelectionState();
        this.updateCounter();

    }

    /**
     * Select specific photo IDs
     */
    selectPhotos(photoIds) {
        if (!Array.isArray(photoIds)) {
            return;
        }

        photoIds.forEach(id => this.selectedIds.add(String(id)));
        this.restoreSelectionState();
        this.saveSelectionState();
        this.updateCounter();
    }

    /**
     * Deselect specific photo IDs
     */
    deselectPhotos(photoIds) {
        if (!Array.isArray(photoIds)) {
            return;
        }

        photoIds.forEach(id => this.selectedIds.delete(String(id)));
        this.restoreSelectionState();
        this.saveSelectionState();
        this.updateCounter();
    }

    /**
     * Check if a photo is selected
     */
    isSelected(photoId) {
        return this.selectedIds.has(String(photoId));
    }

    /**
     * Destroy the selection manager and clean up
     */
    destroy() {
        const container = document.querySelector(this.containerSelector);
        if (container) {
            container.removeEventListener('click', this.handleCheckboxClick);
        }

        const selectAllCheckbox = document.querySelector(this.selectAllSelector);
        if (selectAllCheckbox) {
            selectAllCheckbox.removeEventListener('change', this.handleSelectAllClick);
        }

        this.clearSelection();
        this.isInitialized = false;

    }
}

// Export for use in modules or make globally available
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SelectionManager;
} else {
    window.SelectionManager = SelectionManager;
}
