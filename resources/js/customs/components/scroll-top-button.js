/**
 * Animate scroll to top button in/off view (02.10.2025)
 */

export default (() => {
    let button = document.querySelector('.btn-scroll-top');
    if (!button) return;

    let progress = button.querySelector('svg rect');
    if (!progress) return;

    let length = progress.getTotalLength();
    progress.style.strokeDasharray = length;
    progress.style.strokeDashoffset = length;

    const showProgress = () => {
        let scrollPosition = window.pageYOffset;
        let scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        let scrollPercent = scrollPosition / scrollHeight || 0;
        let draw = length * scrollPercent;
        progress.style.strokeDashoffset = length - draw;
    };

    const handleScroll = () => {
        if (window.pageYOffset > 500) {
            button.classList.add('show');
        } else {
            button.classList.remove('show');
        }
        showProgress();
    };

    window.addEventListener('scroll', handleScroll);
})();



