/** (02.10.2025)
 * Open Dropdown on "hover" (Bootstrap's default is "click")
 * @requires https://getbootstrap.com
 * @requires https://popper.js.org/
 */

export default (() => {
    if (window.matchMedia('(hover: hover)').matches) {
        const dropdownTriggerList = document.querySelectorAll(
            '[data-bs-toggle="dropdown"][data-bs-trigger="hover"]'
        );

        dropdownTriggerList.forEach((dropdownTriggerEl) => {
            const bsDropdown = new bootstrap.Dropdown(dropdownTriggerEl);

            dropdownTriggerEl.addEventListener('click', (e) => {
                if (dropdownTriggerEl.getAttribute('href') === '#') {
                    e.preventDefault();
                }
            });

            dropdownTriggerEl.addEventListener('mouseenter', () => {
                bsDropdown.show();
            });

            dropdownTriggerEl.parentNode.addEventListener('mouseleave', () => {
                bsDropdown.hide();
            });

            dropdownTriggerEl.addEventListener('focus', () => {
                bsDropdown.show();
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    const trigger = menu.closest('.dropdown')?.querySelector('[data-bs-toggle="dropdown"]');
                    if (trigger) {
                        bootstrap.Dropdown.getInstance(trigger)?.hide();
                    }
                });
            }
        });
    }
})();
