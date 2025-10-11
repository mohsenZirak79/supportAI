document.addEventListener('DOMContentLoaded', () => {
    if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
        window.jalaliDatepicker.startWatch({ time: false });
    }
});
