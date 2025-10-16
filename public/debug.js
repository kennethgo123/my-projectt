// Debug script for lawyer details
console.log('Debug script loaded');

document.addEventListener('alpine:init', () => {
    console.log('Alpine initialized');
});

document.addEventListener('show-lawyer-detail', (e) => {
    console.log('Captured show-lawyer-detail event:', e.detail);
});

// Create a MutationObserver to watch for modal content appearing
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.addedNodes && mutation.addedNodes.length > 0) {
            console.log('DOM changed, checking for modal content');
            
            // Check if our modal is present and visible
            const modal = document.querySelector('[x-data="lawyerReviews()"]');
            if (modal && getComputedStyle(modal).display !== 'none') {
                console.log('Modal is visible');
                
                // Check star ratings
                const stars = modal.querySelectorAll('.text-yellow-400');
                console.log('Yellow stars found:', stars.length);
                
                // Check address section
                const addressSection = modal.querySelector('a[href*="openstreetmap"]');
                console.log('Address link found:', !!addressSection);
                if (addressSection) {
                    console.log('Address text:', addressSection.innerText);
                }
            }
        }
    });
});

// Start observing the document
observer.observe(document.body, {
    childList: true,
    subtree: true
}); 