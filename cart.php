<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - ClothingShop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Cart table text color */
        .table {
            color: #DFD0B8;
            background-color: #393E46;
        }
        
        .table thead th {
            color: #DFD0B8;
            border-color: #4a5159;
            background-color: #2c3137;
            font-weight: 600;
        }
        
        .table tbody td {
            color: #DFD0B8;
            border-color: #4a5159;
        }
        
        .table tbody tr:hover {
            background-color: #4a5159;
        }
        
        #cart-container h4 {
            color: #DFD0B8;
        }
        
        #cart-container p {
            color: #DFD0B8;
        }
        
        .login-required-alert {
            background: linear-gradient(135deg, #948979 0%, #7a6e60 100%);
            color: #222831;
            padding: 40px 20px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
            font-weight: 600;
        }
        
        .login-required-alert h3 {
            margin-bottom: 20px;
            font-weight: 700;
            color: #222831;
        }
        
        .login-required-alert p {
            margin: 10px 0;
            font-size: 16px;
            color: #222831;
        }
        
        .login-register-buttons {
            margin-top: 25px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-login, .btn-register {
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border: 2px solid #DFD0B8;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn-login {
            background-color: #DFD0B8;
            color: #222831;
        }
        
        .btn-login:hover {
            background-color: #e8dcc8;
            color: #222831;
        }
        
        .btn-register {
            background-color: transparent;
            color: #DFD0B8;
        }
        
        .btn-register:hover {
            background-color: rgba(223, 208, 184, 0.1);
            color: #DFD0B8;
        }
    </style>
</head>
<body>
    <?php require_once 'session.php'; ?>
    <header>
        <div class="mylogo" onclick="window.location.href='index.php'">CLOTHINGSHOP</div>
        <nav>
            <a href="index.php">HOME</a>
            <a href="about.php">ABOUT</a>
            <a href="products.php">PRODUCTS</a>
            <a href="contact.php">CONTACT</a>
        </nav>

        <div class="logsign">
            <?php if(isLoggedIn()): ?>
                <a href="profile.php"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fa-jelly-fill fa-regular fa-user"></i></a>
            <?php endif; ?>
            <a href="cart.php" id="cart-link"><i class="fa-solid fa-cart-shopping"></i><span id="cart-count" class="cart-badge">0</span></a>
        </div>
    </header>

    <div class="container" style="margin-top:40px;margin-bottom:40px;">
        <h2>Your Cart</h2>
        
        <!-- Login Required Alert (shown when user is not logged in and tries to checkout) -->
        <?php if(!isLoggedIn()): ?>
        <div class="login-required-alert">
            <h3>
                <i class="fas fa-lock"></i> Login Required to Checkout
            </h3>
            <p>You need to log in to your account before you can proceed with checkout.</p>
            <p><small>Already have an account? Sign in below to continue shopping!</small></p>
            
            <div class="login-register-buttons">
                <a href="login.php" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login to Your Account
                </a>
            </div>
            
            <hr style="border-color: rgba(255, 255, 255, 0.3); margin: 25px 0;">
            
            <p><strong>Don't have an account yet?</strong></p>
            <p><small>Creating an account only takes a few minutes and gives you access to faster checkout and order tracking.</small></p>
            
            <div class="login-register-buttons">
                <a href="login.php" class="btn-register">
                    <i class="fas fa-user-plus"></i> Register New Account
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <div id="cart-container"></div>
        
        <?php if(isLoggedIn()): ?>
        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            <button id="checkout-btn" class="btn btn-success ms-2">Checkout</button>
        </div>
        <?php else: ?>
        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            <button id="checkout-btn" class="btn btn-success ms-2" disabled style="opacity: 0.5; cursor: not-allowed;" title="Login required">Checkout (Login Required)</button>
        </div>
        <?php endif; ?>
    </div>

        <!-- Checkout Modal -->
        <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Checkout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="checkout-items"></div>
                        <h4 id="checkout-total" class="mt-3"></h4>
                    </div>
                    <div class="modal-footer">
                        <button id="place-order-btn" class="btn btn-primary">Place Order</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col">
                    <p class="Clo">CLOTHINGSHOP</p>
                    <p>Empowering customers with choice, confidence, and convenience—ClothingShop is your trusted destination for modern online shopping.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        function getCart(){ try{ return JSON.parse(localStorage.getItem('cart')||'[]') }catch(e){return[]} }
        function saveCart(c){ localStorage.setItem('cart', JSON.stringify(c)); }
        function updateCartCount(){
            const cart = getCart();
            const count = cart.reduce((s,i)=>s + (parseInt(i.qty||i.quantity||1)||0),0);
            const el = document.getElementById('cart-count'); if(el) el.textContent = count;
        }

        function render(){
            const cart = getCart();
            const container = document.getElementById('cart-container');
            container.innerHTML = '';
            if(!cart.length){ container.innerHTML = '<p>Your cart is empty.</p>'; updateCartCount(); return; }

            const table = document.createElement('table'); table.className = 'table';
            table.innerHTML = `<thead><tr><th></th><th>Product</th><th>Size</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>`;
            const tbody = document.createElement('tbody');
            let total = 0;
            cart.forEach((item, idx) =>{
                const tr = document.createElement('tr');
                const subtotal = (item.price||0) * (item.qty||1);
                total += subtotal;
                tr.innerHTML = `
                    <td style="width:80px"><img src="${item.img}" style="width:70px;height:70px;object-fit:cover;border-radius:6px"/></td>
                    <td>${item.title}</td>
                    <td>${item.size || 'N/A'}</td>
                    <td>$${item.price}</td>
                    <td>
                        <div class="input-group" style="max-width:140px">
                            <button class="btn btn-outline-secondary btn-decrease" data-idx="${idx}">-</button>
                            <input type="text" class="form-control text-center qty-input" value="${item.qty||1}" data-idx="${idx}" />
                            <button class="btn btn-outline-secondary btn-increase" data-idx="${idx}">+</button>
                        </div>
                    </td>
                    <td>$${subtotal.toFixed(2)}</td>
                    <td><button class="btn btn-sm btn-danger btn-remove" data-idx="${idx}">Remove</button></td>
                `;
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);

            const tfoot = document.createElement('div');
            tfoot.className = 'mt-3';
            tfoot.innerHTML = `<h4>Total: $${total.toFixed(2)}</h4>`;

            container.appendChild(table);
            container.appendChild(tfoot);

            // wire buttons
            container.querySelectorAll('.btn-increase').forEach(btn=> btn.addEventListener('click', ()=>{
                const i = Number(btn.dataset.idx);
                cart[i].qty = (cart[i].qty||1) + 1; saveCart(cart); render(); updateCartCount();
            }));
            container.querySelectorAll('.btn-decrease').forEach(btn=> btn.addEventListener('click', ()=>{
                const i = Number(btn.dataset.idx);
                cart[i].qty = Math.max(1, (cart[i].qty||1) - 1); saveCart(cart); render(); updateCartCount();
            }));
            container.querySelectorAll('.qty-input').forEach(inp=> inp.addEventListener('change', ()=>{
                const i = Number(inp.dataset.idx); const v = parseInt(inp.value) || 1; cart[i].qty = Math.max(1,v); saveCart(cart); render(); updateCartCount();
            }));
            container.querySelectorAll('.btn-remove').forEach(btn=> btn.addEventListener('click', ()=>{
                const i = Number(btn.dataset.idx); cart.splice(i,1); saveCart(cart); render(); updateCartCount();
            }));
        }

            render();

        // Checkout button and modal wiring
        const checkoutBtn = document.getElementById('checkout-btn');
        const checkoutModalEl = document.getElementById('checkoutModal');
        const bsCheckout = checkoutModalEl ? new bootstrap.Modal(checkoutModalEl) : null;
        const checkoutItemsEl = document.getElementById('checkout-items');
        const checkoutTotalEl = document.getElementById('checkout-total');
        const placeOrderBtn = document.getElementById('place-order-btn');

        function openCheckout(){
            const cart = getCart();
            if(!cart.length){ alert('Your cart is empty.'); return; }
            
            // Check if user is logged in (PHP sets this variable in the page)
            const isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
            if(!isLoggedIn) {
                alert('Please log in to your account before checking out.');
                window.location.href = 'login.php';
                return;
            }
            
            checkoutItemsEl.innerHTML = '';
            let total = 0;
            cart.forEach(it =>{
                const sub = (it.price||0) * (it.qty||1);
                total += sub;
                const div = document.createElement('div');
                div.className = 'd-flex align-items-center gap-3 mb-2';
                div.innerHTML = `<img src="${it.img}" style="width:60px;height:60px;object-fit:cover;border-radius:6px"/>
                    <div>
                      <div><strong>${it.title}</strong> <small class="text-muted">(${it.size||'N/A'})</small></div>
                      <div>$${it.price} × ${it.qty} = $${sub.toFixed(2)}</div>
                    </div>`;
                checkoutItemsEl.appendChild(div);
            });
            checkoutTotalEl.textContent = 'Total: $' + total.toFixed(2);
            if(bsCheckout) bsCheckout.show();
        }

        if(checkoutBtn) checkoutBtn.addEventListener('click', openCheckout);

        if(placeOrderBtn){
            placeOrderBtn.addEventListener('click', function(){
                // simulate placing order
                localStorage.removeItem('cart');
                if(bsCheckout) bsCheckout.hide();
                render();
                updateCartCount();
                alert('Order placed successfully — thank you!');
            });
        }
    });
    </script>
</body>
</html>
