<?php
require_once '../auth/check_auth.php';
requireLogin();
?>
<!-- Add Font Awesome and animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<style>
    .cart-icon-wrapper {
        position: relative;
        margin-right: 20px;
        cursor: pointer;
        padding: 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    .cart-icon-wrapper:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }
    .cart-icon {
        font-size: 1.6rem;
        color: white;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        transition: all 0.3s ease;
    }
    .cart-icon-wrapper:hover .cart-icon {
        transform: scale(1.1) rotate(-5deg);
    }
    .cart-count {
        position: absolute;
        top: -10px;
        right: -10px;
        background: linear-gradient(45deg, #ff6b6b, #ff4757);
        color: white;
        border-radius: 20px;
        padding: 4px 10px;
        font-size: 0.9rem;
        font-weight: bold;
        box-shadow: 0 3px 8px rgba(255,75,87,0.5);
        border: 2px solid rgba(255,255,255,0.8);
        min-width: 25px;
        text-align: center;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    .cart-animation {
        position: fixed;
        width: 25px;
        height: 25px;
        background: linear-gradient(45deg, #00b894, #00cec9);
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        box-shadow: 0 2px 8px rgba(0,184,148,0.6);
        animation: flyToCart 0.8s cubic-bezier(0.47, 0, 0.745, 0.715) forwards;
    }
    @keyframes flyToCart {
        0% {
            transform: scale(0.3) rotate(0deg);
            opacity: 0.5;
        }
        50% {
            transform: scale(1.2) rotate(180deg);
            opacity: 0.8;
        }
        100% {
            transform: scale(0) rotate(360deg);
            opacity: 0;
        }
    }
    .shake {
        animation: cartShake 0.82s cubic-bezier(.36,.07,.19,.97) both;
        transform: translate3d(0, 0, 0);
        backface-visibility: hidden;
        perspective: 1000px;
    }
    @keyframes cartShake {
        10%, 90% { transform: translate3d(-1px, 0, 0) rotate(-3deg); }
        20%, 80% { transform: translate3d(2px, 0, 0) rotate(3deg); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0) rotate(-3deg); }
        40%, 60% { transform: translate3d(4px, 0, 0) rotate(3deg); }
    }
    .cart-added {
        animation: cartSuccess 0.5s ease forwards;
    }
    @keyframes cartSuccess {
        0% { transform: scale(1); }
        50% { transform: scale(1.3); }
        100% { transform: scale(1); }
    }
    /* Sparkle effect */
    .sparkle {
        position: absolute;
        width: 20px;
        height: 20px;
        pointer-events: none;
        background: radial-gradient(circle, #fff 10%, transparent 60%);
        animation: sparkleAnim 0.8s ease forwards;
    }
    @keyframes sparkleAnim {
        0% { transform: scale(0) rotate(0deg); opacity: 1; }
        100% { transform: scale(1.5) rotate(180deg); opacity: 0; }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Bookstore</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>" href="books.php">Books</a>
                </li>
                <?php if (basename($_SERVER['PHP_SELF']) != 'books.php'): ?>
                <li class="nav-item">
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">My Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact Admin</a>
                </li>
            </ul>
            <div class="navbar-nav">
                <?php if (basename($_SERVER['PHP_SELF']) != 'cart.php'): ?>
                <a href="cart.php" class="cart-icon-wrapper">
                    <i class="fas fa-shopping-cart cart-icon"></i>
                    <span class="cart-count animate__animated animate__bounceIn"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : '0'; ?></span>
                </a>
                <?php endif; ?>
                <span class="nav-item nav-link text-light">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<script>
function createSparkle(x, y) {
    const sparkle = document.createElement('div');
    sparkle.className = 'sparkle';
    sparkle.style.left = x + 'px';
    sparkle.style.top = y + 'px';
    document.body.appendChild(sparkle);
    setTimeout(() => sparkle.remove(), 800);
}

function animateCartAdd(event) {
    // Create multiple flying dots with different paths
    for (let i = 0; i < 3; i++) {
        const dot = document.createElement('div');
        dot.className = 'cart-animation';
        dot.style.left = (event.clientX + (i * 10 - 10)) + 'px';
        dot.style.top = (event.clientY + (i * 10 - 10)) + 'px';
        document.body.appendChild(dot);
        
        // Remove dot after animation
        setTimeout(() => dot.remove(), 800);
    }
    
    // Get cart icon and count elements
    const cart = document.querySelector('.cart-icon');
    const cartCount = document.querySelector('.cart-count');
    const cartWrapper = document.querySelector('.cart-icon-wrapper');
    
    // Create sparkles around the cart
    setTimeout(() => {
        const rect = cartWrapper.getBoundingClientRect();
        for (let i = 0; i < 5; i++) {
            const angle = (i / 5) * Math.PI * 2;
            const x = rect.left + rect.width/2 + Math.cos(angle) * 30;
            const y = rect.top + rect.height/2 + Math.sin(angle) * 30;
            createSparkle(x, y);
        }
    }, 600);

    // Update cart count with animation
    setTimeout(() => {
        const currentCount = parseInt(cartCount.textContent);
        cartCount.textContent = currentCount + 1;
        cartCount.classList.add('animate__bounceIn');
        
        // Add shake effect to cart
        cart.classList.add('shake');
        cartWrapper.classList.add('cart-added');
        
        // Remove animation classes
        setTimeout(() => {
            cart.classList.remove('shake');
            cartWrapper.classList.remove('cart-added');
            cartCount.classList.remove('animate__bounceIn');
        }, 500);
    }, 800);
}

// Add click event listener to all "Add to Cart" buttons
document.addEventListener('DOMContentLoaded', () => {
    const addToCartButtons = document.querySelectorAll('form[action="add_to_cart.php"] button');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            animateCartAdd(e);
        });
    });
});
</script>