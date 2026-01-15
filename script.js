function showForm(formId) {
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
    document.getElementById(formId).classList.add("active");
}

document.addEventListener('DOMContentLoaded', function(){
    let currentProduct = null;

    const modalEl = document.getElementById('productModal');
    const bsModal = modalEl ? new bootstrap.Modal(modalEl) : null;

    function getCart(){
        try{ return JSON.parse(localStorage.getItem('cart')||'[]') }catch(e){return[]}
    }
    function saveCart(cart){ localStorage.setItem('cart', JSON.stringify(cart)); }
    function updateCartCount(){
        const cart = getCart();
        const count = cart.reduce((s,i)=>s + (parseInt(i.qty||i.quantity||1)||0),0);
        const el = document.getElementById('cart-count');
        if(el) el.textContent = count;
    }

    function addToCart(product){
        const cart = getCart();
        const exists = cart.find(i=>i.title === product.title && i.price==product.price && i.size === product.size);
        if(exists){ exists.qty = (exists.qty||1) + 1; }
        else { product.qty = 1; cart.push(product); }
        saveCart(cart);
        updateCartCount();
    }

    // Init listeners for product cards
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function(e){
            const title = this.dataset.title || '';
            const price = this.dataset.price || '';
            const img = this.dataset.img || '';
            const desc = this.dataset.desc || title;

            currentProduct = { title: title, price: Number(price)||0, img: img, desc: desc, size: 'M' };

            const titleEl = document.getElementById('modal-product-title');
            const priceEl = document.getElementById('modal-product-price');
            const imgEl = document.getElementById('modal-product-img');
            const descEl = document.getElementById('modal-product-desc');
            if(titleEl) titleEl.textContent = title;
            if(priceEl) priceEl.textContent = price;
            if(descEl) descEl.textContent = desc;
            if(imgEl){ imgEl.src = img; imgEl.alt = title }

            // set default selected size to M and update buttons
            if(modalEl){
                modalEl.querySelectorAll('.size-btn').forEach(b=> b.classList.remove('active'));
                const defaultBtn = modalEl.querySelector('.size-btn[data-size="M"]');
                if(defaultBtn) defaultBtn.classList.add('active');
            }

            if(bsModal) bsModal.show();
        });
    });

    // size selector handling
    document.addEventListener('click', function(e){
        const btn = e.target.closest && e.target.closest('.size-btn');
        if(!btn) return;
        const size = btn.dataset.size;
        // toggle active within modal
        const parent = btn.closest('.sizes');
        if(parent){ parent.querySelectorAll('.size-btn').forEach(b=> b.classList.remove('active')); }
        btn.classList.add('active');
        if(currentProduct) currentProduct.size = size;
    });

    // Add to cart button
    const addBtn = document.getElementById('add-to-cart-btn');
    if(addBtn){
        addBtn.addEventListener('click', function(){
            if(!currentProduct) return;
            addToCart(Object.assign({}, currentProduct));
            const prev = addBtn.textContent;
            addBtn.textContent = 'Added';
            setTimeout(()=> addBtn.textContent = prev, 800);
        });
    }

    // initialize count on load
    updateCartCount();
});
