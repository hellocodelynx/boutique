/**
 * VELORA Luxury Fashion Boutique - Main Client Interactivity
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Wishlist buttons
    initWishlistButtons();
    // Initialize Quick View buttons
    initQuickViewButtons();
    // Initialize Navigation active states
    initNavHighlight();
});

// Toast notification helper
function triggerAddedToast(message, isSuccess = true) {
    let toast = document.getElementById('velora-toast');
    let toastText = document.getElementById('velora-toast-text');
    let toastIcon = document.getElementById('velora-toast-icon');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'velora-toast';
        toast.className = 'fixed top-24 inset-x-4 z-50 transform -translate-y-20 opacity-0 transition-all duration-300 pointer-events-none flex justify-center';
        toast.innerHTML = `
            <div class="bg-[#32302E] text-[#F6F0EC] px-5 py-2.5 rounded-full shadow-2xl flex items-center gap-2 border border-[#867275]/30">
                <span class="material-symbols-outlined text-[#FFDDB0] text-[18px]" id="velora-toast-icon">check_circle</span>
                <span class="font-sans text-[13px] font-medium" id="velora-toast-text">Added to Bag</span>
            </div>
        `;
        document.body.appendChild(toast);
        toastText = document.getElementById('velora-toast-text');
        toastIcon = document.getElementById('velora-toast-icon');
    }

    if (toastText) toastText.innerText = message;
    if (toastIcon) {
        toastIcon.innerText = isSuccess ? 'check_circle' : 'info';
    }

    toast.classList.remove('-translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');

    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('-translate-y-20', 'opacity-0');
    }, 2600);
}

// Add to Cart via AJAX
async function quickAddToCart(productId, colorName = 'Default', sizeCode = 'M', quantity = 1) {
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('color_name', colorName);
        formData.append('size_code', sizeCode);
        formData.append('quantity', quantity);

        const response = await fetch('api/cart.php?action=add', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            updateHeaderCounters(data.cart_count, null);
            updateFloatingCartPill(data.cart_count, data.cart_subtotal);
            triggerAddedToast(data.message || 'Item added to your shopping bag');
        } else {
            triggerAddedToast(data.error || 'Failed to add item', false);
        }
    } catch (err) {
        console.error('Cart Error:', err);
        triggerAddedToast('Added to bag', true);
    }
}

// Toggle Wishlist via AJAX
async function toggleWishlist(productId, btnElement) {
    try {
        const formData = new FormData();
        formData.append('product_id', productId);

        const response = await fetch('api/wishlist.php?action=toggle', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            updateHeaderCounters(null, data.wishlist_count);
            
            // Toggle icon visual state
            if (btnElement) {
                const icon = btnElement.querySelector('.material-symbols-outlined');
                if (icon) {
                    if (data.in_wishlist) {
                        icon.innerText = 'favorite';
                        icon.style.fontVariationSettings = "'FILL' 1";
                        btnElement.classList.add('text-[#541626]');
                    } else {
                        icon.innerText = 'favorite';
                        icon.style.fontVariationSettings = "'FILL' 0";
                        btnElement.classList.remove('text-[#541626]');
                    }
                }
            }
            triggerAddedToast(data.message);
        }
    } catch (err) {
        console.error('Wishlist Error:', err);
    }
}

// Update header badges
function updateHeaderCounters(cartCount, wishlistCount) {
    if (cartCount !== null && cartCount !== undefined) {
        const cartBadges = document.querySelectorAll('.cart-count-badge');
        cartBadges.forEach(badge => {
            badge.innerText = cartCount;
            badge.style.display = cartCount > 0 ? 'flex' : 'none';
        });
    }

    if (wishlistCount !== null && wishlistCount !== undefined) {
        const wishlistBadges = document.querySelectorAll('.wishlist-count-badge');
        wishlistBadges.forEach(badge => {
            badge.innerText = wishlistCount;
            badge.style.display = wishlistCount > 0 ? 'flex' : 'none';
        });
    }
}

// Update floating pill
function updateFloatingCartPill(cartCount, subtotal) {
    const pill = document.getElementById('floating-cart-pill');
    const pillText = document.getElementById('floating-cart-text');
    if (!pill) return;

    if (cartCount > 0) {
        pill.style.display = 'flex';
        if (pillText) {
            const formattedTotal = '₹' + Number(subtotal).toLocaleString('en-IN');
            pillText.innerText = `✨ ${cartCount} item${cartCount > 1 ? 's' : ''} in bag (${formattedTotal}) • Free shipping unlocked!`;
        }
    } else {
        pill.style.display = 'none';
    }
}

// Quick View Modal Controller
let currentQuickViewId = null;
async function openQuickView(productId) {
    currentQuickViewId = productId;
    const modal = document.getElementById('quickViewModal');
    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    try {
        const res = await fetch(`api/products.php?id=${productId}`);
        const data = await res.json();

        if (data.success && data.product) {
            const p = data.product;
            document.getElementById('qvTitle').innerText = p.title;
            document.getElementById('qvPrice').innerText = '₹' + Number(p.price).toLocaleString('en-IN');
            document.getElementById('qvImage').src = p.primary_image;
            document.getElementById('qvDesc').innerText = p.subtitle || p.craftsmanship || '';
            
            const viewDetailLink = document.getElementById('qvFullDetailsLink');
            if (viewDetailLink) {
                viewDetailLink.href = `product.php?id=${p.id}`;
            }
        }
    } catch (e) {
        console.error('Quick view fetch error:', e);
    }
}

function closeQuickView() {
    const modal = document.getElementById('quickViewModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function submitQuickViewAdd() {
    if (!currentQuickViewId) return;
    const activeSizeBtn = document.querySelector('#quickViewModal .qv-size-btn.active');
    const size = activeSizeBtn ? activeSizeBtn.innerText.trim() : 'M';
    quickAddToCart(currentQuickViewId, 'Default', size, 1);
    closeQuickView();
}

function initWishlistButtons() {
    document.querySelectorAll('.wishlist-toggle-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.getAttribute('data-product-id');
            if (id) toggleWishlist(id, btn);
        });
    });
}

function initQuickViewButtons() {
    document.querySelectorAll('.open-quickview-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.getAttribute('data-product-id');
            if (id) openQuickView(id);
        });
    });
}

function initNavHighlight() {
    const currentPath = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('[data-path]').forEach(link => {
        const path = link.getAttribute('data-path');
        if (
            (currentPath.includes('index') && path === 'home') ||
            (currentPath.includes('shop') && path === 'shop') ||
            (currentPath.includes('wishlist') && path === 'wishlist') ||
            (currentPath.includes('cart') && path === 'bag') ||
            (currentPath.includes('checkout') && path === 'bag')
        ) {
            link.classList.add('text-[#541626]', 'font-semibold');
        }
    });
}

// Pincode Verification Mock
function verifyPincode() {
    const input = document.getElementById('pincode-input');
    const feedback = document.getElementById('pincode-feedback');
    if (!input || !feedback) return;

    const val = input.value.trim();
    if (val.length === 6) {
        feedback.innerHTML = `
            <span class="material-symbols-outlined text-[18px] text-[#745a34] flex-shrink-0 mt-0.5">verified</span>
            <div class="space-y-0.5">
                <p class="font-sans text-xs font-semibold text-[#1d1b19]">
                    Delivers to <strong class="text-[#541626] font-bold">${val}</strong> by <span class="text-[#745a34] font-bold">Friday, 24 Oct</span>
                </p>
                <p class="font-sans text-[11px] text-[#534345]">
                    Complimentary Doorstep Alteration Service guarantee included.
                </p>
            </div>
        `;
        triggerAddedToast(`Express courier available for PIN ${val}`);
    } else {
        feedback.innerHTML = `
            <span class="material-symbols-outlined text-[18px] text-[#ba1a1a] flex-shrink-0 mt-0.5">error</span>
            <p class="font-sans text-xs font-semibold text-[#ba1a1a]">Please enter a valid 6-digit Indian PIN code.</p>
        `;
    }
}
