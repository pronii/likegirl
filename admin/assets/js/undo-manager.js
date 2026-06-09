/**
 * Undo Manager Module
 * Manages undo operations for photo deletions and transfers
 * Uses sessionStorage for temporary operation tracking
 */

(function(window) {
    'use strict';

    const UndoManager = {
        // Configuration
        STORAGE_KEY: 'undoOperations',
        UNDO_TIMEOUT: 10000, // 10 seconds
        TOAST_CONTAINER_ID: 'undo-toast-container',

        // Initialize the undo manager
        init: function() {
            this.createToastContainer();
            this.clearExpiredOperations();
            // Check for pending operations on page load
            this.checkPendingOperations();
        },

        // Create toast container if it doesn't exist
        createToastContainer: function() {
            if (!document.getElementById(this.TOAST_CONTAINER_ID)) {
                const container = document.createElement('div');
                container.id = this.TOAST_CONTAINER_ID;
                container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
                document.body.appendChild(container);
            }
        },

        // Store an operation for potential undo
        trackOperation: function(operationType, data) {
            const operation = {
                id: this.generateOperationId(),
                type: operationType,
                data: data,
                timestamp: Date.now(),
                expiresAt: Date.now() + this.UNDO_TIMEOUT
            };

            const operations = this.getOperations();
            operations.push(operation);
            sessionStorage.setItem(this.STORAGE_KEY, JSON.stringify(operations));

            return operation.id;
        },

        // Generate unique operation ID
        generateOperationId: function() {
            return 'undo_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },

        // Get all stored operations
        getOperations: function() {
            try {
                const data = sessionStorage.getItem(this.STORAGE_KEY);
                return data ? JSON.parse(data) : [];
            } catch (e) {
                console.error('Error reading undo operations:', e);
                return [];
            }
        },

        // Get specific operation by ID
        getOperation: function(operationId) {
            const operations = this.getOperations();
            return operations.find(op => op.id === operationId);
        },

        // Remove operation from storage
        removeOperation: function(operationId) {
            let operations = this.getOperations();
            operations = operations.filter(op => op.id !== operationId);
            sessionStorage.setItem(this.STORAGE_KEY, JSON.stringify(operations));
        },

        // Clear expired operations
        clearExpiredOperations: function() {
            const now = Date.now();
            let operations = this.getOperations();
            const validOperations = operations.filter(op => op.expiresAt > now);

            if (validOperations.length !== operations.length) {
                sessionStorage.setItem(this.STORAGE_KEY, JSON.stringify(validOperations));
            }
        },

        // Check for pending operations on page load
        checkPendingOperations: function() {
            this.clearExpiredOperations();
            const operations = this.getOperations();

            operations.forEach(operation => {
                const remainingTime = operation.expiresAt - Date.now();
                if (remainingTime > 0) {
                    this.showUndoToast(operation, remainingTime);
                }
            });
        },

        // Show undo toast notification
        showUndoToast: function(operation, initialTime) {
            const toastId = 'toast_' + operation.id;

            // Check if toast already exists
            if (document.getElementById(toastId)) {
                return;
            }

            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = 'undo-toast';
            toast.style.cssText = `
                background: #323a46;
                color: #ffffff;
                padding: 15px 20px;
                border-radius: 4px;
                margin-bottom: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                justify-content: space-between;
                min-width: 300px;
                animation: slideInRight 0.3s ease-out;
            `;

            const message = this.getOperationMessage(operation);
            const messageSpan = document.createElement('span');
            messageSpan.style.cssText = 'flex: 1; margin-right: 15px;';
            messageSpan.textContent = message;

            const countdownSpan = document.createElement('span');
            countdownSpan.className = 'undo-countdown';
            countdownSpan.style.cssText = 'margin-right: 15px; font-size: 12px; opacity: 0.8;';

            const undoButton = document.createElement('button');
            undoButton.textContent = '撤销';
            undoButton.className = 'btn btn-sm btn-light';
            undoButton.style.cssText = 'margin-left: 10px; padding: 4px 12px; font-size: 13px;';
            undoButton.onclick = () => this.handleUndo(operation.id);

            toast.appendChild(messageSpan);
            toast.appendChild(countdownSpan);
            toast.appendChild(undoButton);

            const container = document.getElementById(this.TOAST_CONTAINER_ID);
            container.appendChild(toast);

            // Start countdown
            this.startCountdown(operation.id, countdownSpan, Math.ceil(initialTime / 1000));
        },

        // Get operation message
        getOperationMessage: function(operation) {
            switch (operation.type) {
                case 'delete':
                    const photoCount = operation.data.photoIds ? operation.data.photoIds.length : 1;
                    return `已删除 ${photoCount} 张图片`;
                case 'transfer':
                    const transferCount = operation.data.photoIds ? operation.data.photoIds.length : 1;
                    return `已转移 ${transferCount} 张图片`;
                default:
                    return '操作已完成';
            }
        },

        // Start countdown timer
        startCountdown: function(operationId, countdownElement, seconds) {
            let remaining = seconds;

            const updateCountdown = () => {
                if (remaining <= 0) {
                    this.removeToast(operationId);
                    this.removeOperation(operationId);
                    return;
                }

                countdownElement.textContent = `${remaining}秒`;
                remaining--;

                setTimeout(updateCountdown, 1000);
            };

            updateCountdown();
        },

        // Remove toast from DOM
        removeToast: function(operationId) {
            const toastId = 'toast_' + operationId;
            const toast = document.getElementById(toastId);

            if (toast) {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        },

        // Handle undo action
        handleUndo: function(operationId) {
            const operation = this.getOperation(operationId);

            if (!operation) {
                this.showNotification('操作记录不存在', 'error');
                return;
            }

            // Check if operation has expired
            if (Date.now() > operation.expiresAt) {
                this.showNotification('操作已过期，无法撤销', 'error');
                this.removeToast(operationId);
                this.removeOperation(operationId);
                return;
            }

            // Call restore endpoint
            this.executeUndo(operation);
        },

        // Execute undo via API
        executeUndo: function(operation) {
            const restoreData = {
                operationType: operation.type,
                photoIds: operation.data.photoIds || [],
                sourceAlbumId: operation.data.sourceAlbumId || null,
                targetAlbumId: operation.data.targetAlbumId || null
            };

            // Show loading state
            const toastId = 'toast_' + operation.id;
            const toast = document.getElementById(toastId);
            if (toast) {
                const button = toast.querySelector('button');
                if (button) {
                    button.disabled = true;
                    button.textContent = '撤销中...';
                }
            }

            fetch('restorePhotos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(restoreData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification('撤销成功', 'success');
                    this.removeToast(operation.id);
                    this.removeOperation(operation.id);

                    // Reload page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    this.showNotification(data.message || '撤销失败', 'error');
                    // Re-enable button
                    if (toast) {
                        const button = toast.querySelector('button');
                        if (button) {
                            button.disabled = false;
                            button.textContent = '撤销';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Undo error:', error);
                this.showNotification('撤销失败，请稍后重试', 'error');
                // Re-enable button
                if (toast) {
                    const button = toast.querySelector('button');
                    if (button) {
                        button.disabled = false;
                        button.textContent = '撤销';
                    }
                }
            });
        },

        // Show notification message
        showNotification: function(message, type) {
            // Use existing notification system if available
            if (typeof toastr !== 'undefined') {
                toastr[type || 'info'](message);
            } else {
                alert(message);
            }
        },

        // Clear all operations
        clearAll: function() {
            sessionStorage.removeItem(this.STORAGE_KEY);
            const container = document.getElementById(this.TOAST_CONTAINER_ID);
            if (container) {
                container.innerHTML = '';
            }
        }
    };

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .undo-toast:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }
    `;
    document.head.appendChild(style);

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            UndoManager.init();
        });
    } else {
        UndoManager.init();
    }

    // Expose to global scope
    window.UndoManager = UndoManager;

})(window);
