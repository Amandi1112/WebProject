<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allura Estrella - Premium Fashion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }

        .search-bar {
            flex-grow: 1;
            max-width: 400px;
            margin: 0 2rem;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-bar input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .cart-icon {
            position: relative;
            background: #ff6b6b;
            color: white;
            padding: 12px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cart-icon:hover {
            transform: scale(1.1);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff3838;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* Navigation Dashboard */
        .dashboard {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            margin: 2rem 0;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .nav-categories {
            display: flex;
            overflow-x: auto;
            padding: 0;
        }

        .nav-item {
            padding: 15px 25px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-item:hover, .nav-item.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-2px);
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }

        /* Filters Sidebar */
        .filters-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            height: fit-content;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .filter-section {
            margin-bottom: 2rem;
        }

        .filter-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .filter-options {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .filter-option:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        .color-option {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            border: 2px solid #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .color-option:hover {
            transform: scale(1.1);
            border-color: #667eea;
        }

        .price-range {
            width: 100%;
            margin: 10px 0;
        }

        /* Products Grid */
        .products-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .product-image {
            height: 300px;
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            position: relative;
            overflow: hidden;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .add-to-cart {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .add-to-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #667eea;
        }

        /* Cart Sidebar */
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 20px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 1500;
            overflow-y: auto;
        }

        .cart-sidebar.open {
            right: 0;
        }

        .cart-header {
            padding: 2rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-items {
            padding: 1rem;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .cart-item-image {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #f093fb, #f5576c);
            border-radius: 8px;
        }

        .cart-item-info {
            flex-grow: 1;
        }

        .cart-total {
            padding: 2rem;
            border-top: 2px solid #eee;
            text-align: center;
        }

        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            margin-top: 4rem;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #667eea, #764ba2, transparent);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            padding: 4rem 0 2rem;
        }

        .footer-section h3 {
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            color: #fff;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 12px;
        }

        .footer-section ul li a {
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-section ul li a:hover {
            color: #667eea;
            transform: translateX(5px);
        }

        .company-info {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .company-info .logo-footer {
            font-size: 2.2rem;
            font-weight: bold;
            background: linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .company-info p {
            color: #bdc3c7;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-link {
            width: 45px;
            height: 45px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 20px;
        }

        .social-link:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .newsletter-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .newsletter-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            outline: none;
            transition: all 0.3s ease;
        }

        .newsletter-form input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .newsletter-form input:focus {
            border-color: #667eea;
            background: rgba(255, 255, 255, 0.15);
        }

        .newsletter-form button {
            padding: 12px 20px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .newsletter-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #bdc3c7;
            transition: color 0.3s ease;
        }

        .contact-item:hover {
            color: #667eea;
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem 0;
            text-align: center;
            color: #bdc3c7;
            background: rgba(0, 0, 0, 0.2);
        }

        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .payment-methods {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .payment-icon {
            width: 40px;
            height: 25px;
            background: white;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .filters-sidebar {
                order: 2;
            }

            .products-section {
                order: 1;
            }

            .nav-categories {
                flex-wrap: wrap;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .search-bar {
                margin: 0;
                max-width: none;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
            }

            .social-links {
                justify-content: center;
            }

            .newsletter-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="#" class="logo">Allura Estrella</a>
                
                <div class="search-bar">
                    <input type="text" placeholder="Search for clothing..." id="searchInput">
                </div>
                
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="openModal('loginModal')">Login</button>
                    <button class="btn btn-primary" onclick="openModal('signupModal')">Sign Up</button>
                    <div class="cart-icon" onclick="toggleCart()">
                        🛒
                        <span class="cart-count" id="cartCount">0</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Navigation -->
    <div class="container">
        <div class="dashboard">
            <div class="nav-categories">
                <button class="nav-item active" onclick="filterCategory('home')">Home</button>
                <button class="nav-item" onclick="filterCategory('new')">New</button>
                <button class="nav-item" onclick="filterCategory('dresses')">Dresses</button>
                <button class="nav-item" onclick="filterCategory('tops')">Tops</button>
                <button class="nav-item" onclick="filterCategory('bottoms')">Bottoms</button>
                <button class="nav-item" onclick="filterCategory('rompers')">Rompers</button>
                <button class="nav-item" onclick="filterCategory('jumpsuits')">Jumpsuits</button>
                <button class="nav-item" onclick="filterCategory('office')">Office & Work</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Filters Sidebar -->
            <div class="filters-sidebar">
                <div class="filter-section">
                    <h3 class="filter-title">Filter by Size</h3>
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="checkbox" name="size" value="xs"> XS
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="size" value="s"> S
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="size" value="m"> M
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="size" value="l"> L
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="size" value="xl"> XL
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="size" value="xxl"> XXL
                        </label>
                    </div>
                </div>

                <div class="filter-section">
                    <h3 class="filter-title">Filter by Price</h3>
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="radio" name="price" value="0-25"> $0 - $25
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="price" value="25-50"> $25 - $50
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="price" value="50-100"> $50 - $100
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="price" value="100+"> $100+
                        </label>
                    </div>
                    <input type="range" class="price-range" min="0" max="200" value="100" id="priceRange">
                    <div>Max Price: $<span id="priceValue">100</span></div>
                </div>

                <div class="filter-section">
                    <h3 class="filter-title">Filter by Color</h3>
                    <div class="filter-options" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                        <div class="color-option" style="background: #ff0000;" onclick="filterColor('red')" title="Red"></div>
                        <div class="color-option" style="background: #00ff00;" onclick="filterColor('green')" title="Green"></div>
                        <div class="color-option" style="background: #0000ff;" onclick="filterColor('blue')" title="Blue"></div>
                        <div class="color-option" style="background: #ffff00;" onclick="filterColor('yellow')" title="Yellow"></div>
                        <div class="color-option" style="background: #ff00ff;" onclick="filterColor('pink')" title="Pink"></div>
                        <div class="color-option" style="background: #00ffff;" onclick="filterColor('cyan')" title="Cyan"></div>
                        <div class="color-option" style="background: #000000;" onclick="filterColor('black')" title="Black"></div>
                        <div class="color-option" style="background: #ffffff; border-color: #333;" onclick="filterColor('white')" title="White"></div>
                    </div>
                </div>

                <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="applyFilters()">Apply Filters</button>
                <button class="btn btn-secondary" style="width: 100%; margin-top: 0.5rem;" onclick="clearFilters()">Clear All</button>
            </div>

            <!-- Products Section -->
            <div class="products-section">
                <div class="products-header">
                    <h2>Featured Products</h2>
                    <select onchange="sortProducts(this.value)">
                        <option value="default">Sort by</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="name">Name A-Z</option>
                        <option value="newest">Newest First</option>
                    </select>
                </div>

                <div class="products-grid" id="productsGrid">
                    <!-- Products will be dynamically generated -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Company Information -->
                <div class="footer-section company-info">
                    <div class="logo-footer">Allura Estrella</div>
                    <p>Discover your unique style with our curated collection of premium fashion. From elegant dresses to everyday essentials, we bring you the latest trends and timeless classics.</p>
                    <div class="social-links">
                        <a href="#" class="social-link" onclick="openSocialMedia('facebook')" title="Facebook">📘</a>
                        <a href="#" class="social-link" onclick="openSocialMedia('instagram')" title="Instagram">📷</a>
                        <a href="#" class="social-link" onclick="openSocialMedia('twitter')" title="Twitter">🐦</a>
                        <a href="#" class="social-link" onclick="openSocialMedia('tiktok')" title="TikTok">🎵</a>
                        <a href="#" class="social-link" onclick="openSocialMedia('youtube')" title="YouTube">📺</a>
                    </div>
                </div>

                <!-- Shop Categories -->
                <div class="footer-section">
                    <h3>Shop</h3>
                    <ul>
                        <li><a href="#" onclick="filterCategory('new')">→ New Arrivals</a></li>
                        <li><a href="#" onclick="filterCategory('dresses')">→ Dresses</a></li>
                        <li><a href="#" onclick="filterCategory('tops')">→ Tops & Blouses</a></li>
                        <li><a href="#" onclick="filterCategory('bottoms')">→ Bottoms</a></li>
                        <li><a href="#" onclick="filterCategory('rompers')">→ Rompers</a></li>
                        <li><a href="#" onclick="filterCategory('jumpsuits')">→ Jumpsuits</a></li>
                        <li><a href="#" onclick="filterCategory('office')">→ Office & Work</a></li>
                        <li><a href="#">→ Sale Items</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="#">→ Contact Us</a></li>
                        <li><a href="#">→ Size Guide</a></li>
                        <li><a href="#">→ Shipping & Returns</a></li>
                        <li><a href="#">→ Order Tracking</a></li>
                        <li><a href="#">→ FAQ</a></li>
                        <li><a href="#">→ Care Instructions</a></li>
                        <li><a href="#">→ Privacy Policy</a></li>
                        <li><a href="#">→ Terms & Conditions</a></li>
                    </ul>
                </div>

                <!-- Contact & Newsletter -->
                <div class="footer-section">
                    <h3>Stay Connected</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">📍</div>
                            <div>
                                <div>216/3, Pamunuwa Road</div>
                                <div>Maharagama, Sri Lanka</div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">📞</div>
                            <div>
                                <div>+94 77 009 9003</div>
                                <div>+94 76 629 2920</div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">