function generateStars(numberOfStars = 15, containerSelector = '.stars-container') {
    const container = document.querySelector(containerSelector);

    if (container) {
        container.innerHTML = '';

        const colors = ['#ffffff', '#b3d9ff', '#ffffcc', '#ffccdd', '#ccffcc'];

        const containerRect = container.getBoundingClientRect();
        const containerWidth = containerRect.width || container.clientWidth;
        const containerHeight = containerRect.height || container.clientHeight;

        for (let i = 0; i < numberOfStars; i++) {
            const star = document.createElement('div');
            star.className = 'star';

            const size = Math.random() * 3 + 1;
            const x = Math.random() * Math.max(0, containerWidth - size);
            const y = Math.random() * Math.max(0, containerHeight - size);
            const delay = Math.random() * 3;
            const duration = Math.random() * 2 + 2;
            const color = colors[Math.floor(Math.random() * colors.length)];

            star.style.cssText = `
                left: ${x}px;
                top: ${y}px;
                width: ${size}px;
                height: ${size}px;
                background: ${color};
                box-shadow: 0 0 ${size + 2}px ${color};
                animation-delay: ${delay}s;
                animation-duration: ${duration}s;
                position: absolute;
                animation: twinkle ${duration}s infinite ease-in-out ${delay}s;
            `;

            container.appendChild(star);
        }
    }
}

function createTwinkleAnimation() {
    const styleId = 'twinkle-animation';

    if (document.getElementById(styleId)) {
        return;
    }

    const style = document.createElement('style');
    style.id = styleId;
    style.textContent = `
        @keyframes twinkle {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            25% {
                opacity: 0.2;
                transform: scale(0.8);
            }
            50% {
                opacity: 0.05;
                transform: scale(0.6);
            }
            75% {
                opacity: 0.3;
                transform: scale(0.9);
            }
        }
    `;

    document.head.appendChild(style);
}

document.addEventListener('DOMContentLoaded', () => {
    createTwinkleAnimation();
    generateStars(30, '.stars-container');
});