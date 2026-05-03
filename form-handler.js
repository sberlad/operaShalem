// Form submission handler for Opera Shalem contact form
// Replace the existing form handler in index.html with this code

const partnerForm = document.getElementById('partnerForm');
if (partnerForm) {
    partnerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = partnerForm.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';
        
        // Get form data
        const formData = new FormData(partnerForm);
        
        try {
            const response = await fetch('contact-form.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                // Success
                alert(result.message || 'Thank you for your interest. We will be in touch soon.');
                partnerForm.reset();
            } else {
                // Error from server
                alert(result.message || 'Something went wrong. Please try again.');
            }
        } catch (error) {
            // Network error
            console.error('Form submission error:', error);
            alert('Failed to send message. Please email us directly at info@operashalem.com');
        } finally {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}
