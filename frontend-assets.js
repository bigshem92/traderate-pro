// public/assets/js/main.js
const TradeRate = {
    init() {
        this.setupEventListeners();
        this.setupFormValidation();
        this.setupImageUpload();
    },

    setupEventListeners() {
        document.querySelectorAll('[data-submit-quote]').forEach(button => {
            button.addEventListener('click', this.handleQuoteSubmission.bind(this));
        });

        document.querySelectorAll('[data-review]').forEach(button => {
            button.addEventListener('click', this.handleReviewSubmission.bind(this));
        });
    },

    async handleQuoteSubmission(e) {
        e.preventDefault();
        const form = e.target.closest('form');
        const data = new FormData(form);

        try {
            const response = await fetch('/api/quotes', {
                method: 'POST',
                body: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();
            if (result.success) {
                this.showNotification('Quote submitted successfully', 'success');
                window.location.reload();
            } else {
                this.showNotification(result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to submit quote', 'error');
        }
    },

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    },

    setupImageUpload() {
        const dropZone = document.getElementById('image-drop-zone');
        if (!dropZone) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        dropZone.addEventListener('drop', handleDrop.bind(this));

        async function handleDrop(e) {
            const files = e.dataTransfer.files;
            const formData = new FormData();
            
            Array.from(files).forEach(file => {
                formData.append('images[]', file);
            });

            try {
                const response = await fetch('/api/upload-images', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    this.showNotification('Images uploaded successfully', 'success');
                    this.updateImagePreview(result.images);
                }
            } catch (error) {
                this.showNotification('Failed to upload images', 'error');
            }
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    TradeRate.init();
});
