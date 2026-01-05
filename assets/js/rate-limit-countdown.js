// Countdown timer
function startCountdown(seconds) {
    const updateTimer = () => {
        if (seconds <= 0) {
            enableSubmitButton();
            return;
        }
        
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        const display = `${mins}:${secs.toString().padStart(2, '0')}`;
        
        $('#ffc-countdown').text(display);
        $('#ffc-countdown-short').text(display);
        
        seconds--;
        setTimeout(updateTimer, 1000);
    };
    
    updateTimer();
}