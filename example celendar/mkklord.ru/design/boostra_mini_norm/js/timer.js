const timerElement = document.getElementById('timer');

let timeLeft = 15 * 60;

function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;

    timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

    timeLeft--;

    if (timeLeft < 0) {
        clearInterval(intervalId);
        timerElement.textContent = "00:00";
    }
}

const intervalId = setInterval(updateTimer, 1000);

updateTimer();