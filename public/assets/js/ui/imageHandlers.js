function changeMainImage(thumbnail) {
    const mainImage = document.querySelector('.main-product-image');
    mainImage.src = thumbnail.src;
}

function initImageZoom() {
    const zoomContainer = document.querySelector('.image-zoom-container');
    const zoomTargets = [
        document.querySelector('.main-product-image'),
        ...document.querySelectorAll('.image-thumbnails img')
    ];

    zoomTargets.forEach(target => {
        target.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width * 100;
            const y = (e.clientY - rect.top) / rect.height * 100;

            zoomContainer.style.backgroundImage = `url(${this.src})`;
            zoomContainer.style.backgroundPosition = `${x}% ${y}%`;
            zoomContainer.style.backgroundSize = '500%';
            zoomContainer.style.opacity = '1';
        });

        target.addEventListener('mouseleave', function() {
            zoomContainer.style.opacity = '0';
        });
    });
}

export { changeMainImage, initImageZoom };