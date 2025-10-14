document.addEventListener('DOMContentLoaded', function () {
    const sliders = document.querySelectorAll('.calculator-slider.slider');

    const initializeSlider = (slider) => {
        const progress = slider.querySelector('.calculator-slider__progress');
        const container = slider.closest('.slider-container');
        const currentValueSpan = container.querySelector('.slider-current-value');
        const min = parseFloat(slider.dataset.min);
        const max = parseFloat(slider.dataset.max);
        const current = parseFloat(slider.dataset.currentValue);

        const percent = ((current - min) / (max - min)) * 100;
        progress.style.width = percent + '%';
        currentValueSpan.textContent = current;
    };

    sliders.forEach(slider => {
        initializeSlider(slider);

        const progress = slider.querySelector('.calculator-slider__progress');
        const container = slider.closest('.slider-container');
        const currentValueSpan = container.querySelector('.slider-current-value');

        let isDragging = false;

        const updateSlider = (x) => {
            const rect = slider.getBoundingClientRect();
            let offsetX = x - rect.left;
            let width = rect.width;

            if (offsetX < 0) offsetX = 0;
            if (offsetX > width) offsetX = width;

            const percent = (offsetX / width) * 100;
            progress.style.width = percent + '%';

            const min = parseFloat(slider.dataset.min);
            const max = parseFloat(slider.dataset.max);
            const step = parseFloat(slider.dataset.step);

            let value = min + (max - min) * (offsetX / width);
            value = Math.round(value / step) * step;

            if (currentValueSpan) {
                currentValueSpan.textContent = value;
            }
        };

        slider.addEventListener('mousedown', (e) => {
            isDragging = true;
            updateSlider(e.clientX);
        });

        document.addEventListener('mousemove', (e) => {
            if (isDragging) {
                updateSlider(e.clientX);
            }
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });

        slider.addEventListener('touchstart', (e) => {
            isDragging = true;
            updateSlider(e.touches[0].clientX);
        });

        document.addEventListener('touchmove', (e) => {
            if (isDragging) {
                updateSlider(e.touches[0].clientX);
            }
        });

        document.addEventListener('touchend', () => {
            isDragging = false;
        });
    });
});
