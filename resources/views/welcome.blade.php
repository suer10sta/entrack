<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Accueil - DailyTrac</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="welcome/css/bootstrap.min.css">
    <link rel="stylesheet" href="welcome/css/font-awesome.min.css">
    <link rel="stylesheet" href="welcome/css/aos.css">
    <link rel="stylesheet" href="welcome/css/owl.carousel.min.css">
    <link rel="stylesheet" href="welcome/css/owl.theme.default.min.css">
    

    <!-- MAIN CSS -->
    <link rel="stylesheet" href="welcome/css/templatemo-digital-trend.css">
    
    <!-- Vite Assets (Tailwind CSS) -->
    @vite('resources/css/app.css')
    
    <style>
        /* Custom Navbar Styles */
        .navbar {
            background: linear-gradient(135deg, #3AB0E2 0%, #2BA3D8 100%);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-brand:hover {
            color: white !important;
        }
        
        .brand-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3AB0E2;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .navbar-nav {
            align-items: center;
            gap: 2rem;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            font-size: 1rem;
            transition: opacity 0.3s ease;
            padding: 0.5rem 0 !important;
        }
        
        .nav-link:hover {
            opacity: 0.8;
        }
        
        .login-btn {
            background: white;
            color: #3AB0E2 !important;
            padding: 0.6rem 2rem !important;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid white;
        }
        
        .login-btn:hover {
            background: transparent;
            color: white !important;
            transform: translateY(-2px);
        }
        
        .navbar-toggler {
            border-color: white;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
    </style>

  </head>

<body class="antialiased pt-20">

    <x-Home_page.header />


    <!-- HERO -->
    <x-Home_page.HeroSection />


    <!-- ABOUT -->
    <x-Home_page.FeaturesSection />

    
    <!-- ACCOUNT_TYPES -->
    <x-Home_page.Account_Types />


    <!-- PROJECT -->
    <x-Home_page.ClientTestimonials />



    <x-Home_page.footer/>


    <!-- SCRIPTS -->
    <script src="welcome/js/jquery.min.js"></script>
    <script src="welcome/js/bootstrap.min.js"></script>
    <script src="welcome/js/aos.js"></script>
    <script src="welcome/js/owl.carousel.min.js"></script>
    <script src="welcome/js/smoothscroll.js"></script>
    <script src="welcome/js/custom.js"></script>

  </body>

</html>
