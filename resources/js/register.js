document.addEventListener('DOMContentLoaded', () => {
    import('./floating-chat').then(({ mountFloatingChatWidget }) => mountFloatingChatWidget());
    if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
        window.jalaliDatepicker.startWatch({ time: false });
    }
});
