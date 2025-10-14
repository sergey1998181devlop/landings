// Функция, которая форматирует число с разделителями тысяч
function formatNumber(number) {
    return number.toLocaleString('ru-RU'); // Используем 'ru-RU' для формата с разделителями тысяч
}

// Функция, которая перемещает ползунок и обновляет значение и ширину slider-progress
function moveSlider(slider, sliderProgress, sliderCurrentValue, clickX) {
    const sliderWidth = slider.clientWidth;
    const min = parseFloat(slider.getAttribute('data-min'));
    const max = parseFloat(slider.getAttribute('data-max'));
    const step = parseFloat(slider.getAttribute('data-step'));

    // Вычисляем значение на основе клика и ограничиваем его по минимальному и максимальному значению
    let value = min + ((clickX / sliderWidth) * (max - min));
    value = Math.max(min, Math.min(max, value));

    // Округляем значение с учетом шага
    value = Math.round(value / step) * step;

    // Устанавливаем ширину полосы прогресса и текст в sliderCurrentValue
    sliderProgress.style.width = `${((value - min) / (max - min)) * 100}%`;
    sliderCurrentValue.textContent = formatNumber(value); // Форматируем число с разделителями тысяч
}

const sliderContainers = document.querySelectorAll('.slider-container');

sliderContainers.forEach((container, index) => {
    const slider = container.querySelector('.slider');
    const sliderProgress = container.querySelector('.slider-progress');
    const sliderCurrentValue = container.querySelector('.slider-current-value');

    // Получаем начальное значение из атрибута data-current-value и устанавливаем его
    const initialValue = parseFloat(slider.getAttribute('data-current-value'));
    sliderCurrentValue.textContent = formatNumber(initialValue); // Форматируем начальное значение

    // Устанавливаем начальную ширину для slider-progress
    const min = parseFloat(slider.getAttribute('data-min'));
    const max = parseFloat(slider.getAttribute('data-max'));
    const initialWidth = ((initialValue - min) / (max - min)) * 100;
    sliderProgress.style.width = `${initialWidth}%`;

    // Флаг для отслеживания нажатия на слайдер
    let isDragging = false;

    slider.addEventListener('mousedown', () => {
        isDragging = true;
        slider.style.cursor = 'grabbing'; // Изменяем курсор при зажатой кнопке
    });

    document.addEventListener('mousemove', (event) => {
        if (isDragging) {
            // Если зажата кнопка мыши, вызываем функцию перемещения
            moveSlider(slider, sliderProgress, sliderCurrentValue, event.clientX - slider.getBoundingClientRect().left);
        }
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
        slider.style.cursor = 'grab'; // Возвращаем обычный курсор
    });

    // События касания для мобильных устройств
    slider.addEventListener('touchstart', (event) => {
        isDragging = true;
        moveSlider(slider, sliderProgress, sliderCurrentValue, event.touches[0].clientX - slider.getBoundingClientRect().left);
    }, { passive: false });

    document.addEventListener('touchmove', (event) => {
        if (isDragging) {
            event.preventDefault(); // Отключаем прокрутку
            moveSlider(slider, sliderProgress, sliderCurrentValue, event.touches[0].clientX - slider.getBoundingClientRect().left);
        }
    }, { passive: false });

    document.addEventListener('touchend', () => {
        isDragging = false;
    });


    // Добавляем обработчик для обычного клика на слайдер
    slider.addEventListener('click', (event) => {
        if (!isDragging) {
            moveSlider(slider, sliderProgress, sliderCurrentValue, event.clientX - slider.getBoundingClientRect().left);
        }
    });
});