// FreshProvision App JS
document.addEventListener('DOMContentLoaded', () => {
    // Auto-hide flash messages
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transition = 'opacity 0.4s';
            setTimeout(() => flash.remove(), 400);
        }, 4000);
    }

    // Quantity buttons on product pages / cart
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.parentElement.querySelector('.qty-input, .qty-value');
            if (!input) return;
            let val = parseInt(input.value || input.textContent || 1);
            if (this.dataset.action === 'plus') val++;
            if (this.dataset.action === 'minus') val = Math.max(1, val - 1);
            if (input.tagName === 'INPUT') {
                input.value = val;
            } else {
                input.textContent = val;
            }
        });
    });
});

// Simple form loading state
function setLoading(btn, loading = true) {
    if (loading) {
        btn.disabled = true;
        btn.dataset.original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Please wait...';
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.original || btn.innerHTML;
    }
}
