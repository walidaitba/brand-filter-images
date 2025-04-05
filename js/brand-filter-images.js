document.addEventListener("DOMContentLoaded", function() {
    // Use the brand images data passed from PHP
    const brandImages = brandFilterData.brandImages;

    // Log the data for debugging
    console.log('brandFilterData:', brandFilterData);
    console.log('brandImages:', brandImages);

    // Check if brandImages is not empty
    if (!brandImages || Object.keys(brandImages).length === 0) {
        console.warn('Brand images data is empty.');
        return;
    }

    // Select all brand list items
    const brandItems = document.querySelectorAll('.woof_list li');

    if (!brandItems.length) {
        console.warn('No brand filter items found.');
    }

    brandItems.forEach(li => {
        const span = li.querySelector('.woof_label_term');
        const label = span ? span.querySelector('label') : null;
        const checkbox = span ? span.querySelector('input[type="checkbox"]') : null;
        const brandName = label ? label.textContent.trim() : null;

        if (brandName && brandImages[brandName]) {
            // Remove text nodes from span (the brand name)
            span.childNodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    node.textContent = '';
                }
            });

            // Create an image element
            const img = document.createElement('img');
            img.src = brandImages[brandName];
            img.alt = brandName;
            img.style.width = '100px'; // Adjust as needed
            img.style.height = 'auto';  // Maintain aspect ratio
            img.style.cursor = 'pointer';

            // Append the image to the span
            span.insertBefore(img, label);

            // Hide the text label
            label.style.display = 'none';

            // Add click event to the image to toggle the checkbox
            img.addEventListener('click', function() {
                // Toggle checkbox state
                checkbox.checked = !checkbox.checked;
                // Trigger change event
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
        } else {
            console.warn(`No image found for brand: ${brandName}`);
        }
    });
});
