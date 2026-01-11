<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Benefit Market Trade - Premier Investment Platform</title>
      <meta name="description" content="Trade and invest in stocks, ETFs, currencies, indices and commodities or copy leading investors on Benefit Market Trade, with 100% security and risk management, 24/7 online support to guide you through.">
    <meta name="keywords" content="forex, crypto, cryptocurrency, invest, investment, php, Trading, investors, stock, indices platform">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Custom CSS */
    .gradient-bg {
      background: linear-gradient(to right, #384c5e, #020113);
    }
    
    .gradient-bg-dark {
      background: linear-gradient(to right, #1e40af, #3730a3);
    }
    
    /* Smooth scrolling */
    html {
      scroll-behavior: smooth;
    }
    
    /* Additional animations */
    .hover-scale:hover {
      transform: scale(1);
      transition: transform 2.3s ease;
    }
    
    /* Chart animation */
    .chart-line {
      stroke-dasharray: 1000;
      stroke-dashoffset: 1000;
      animation: dash 2s ease-in-out forwards;
    }
    
    @keyframes dash {
      to {
        stroke-dashoffset: 0;
      }
    }
    
    /* Number counter animation */
    .counter-value {
      counter-reset: count 0;
      animation: count-up 1s forwards;
    }
    
    @keyframes count-up {
      to {
        counter-increment: count 100;
        content: counter(count);
      }
    }

    /* Mobile menu animation */
    .transform {
      transition-property: transform;
    }
    .transition-transform {
      transition-property: transform;
    }
    .duration-300 {
      transition-duration: 300ms;
    }
    .-translate-x-full {
      transform: translateX(-100%);
    }
  </style>

</head>
<body class="min-h-screen">
    

  <header class="bg-white shadow-sm sticky top-0 z-50">

    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <div class="text-2xl font-bold text-blue-600">Benefit Market Trade</div>
      
      <!-- Desktop Navigation -->
      <nav class="hidden md:flex space-x-8">
        <a href="#features" class="text-gray-600 hover:text-blue-600 transition-colors">Features</a>
        <a href="#how-it-works" class="text-gray-600 hover:text-blue-600 transition-colors">How It Works</a>
        <a href="#market-insights" class="text-gray-600 hover:text-blue-600 transition-colors">Market Insights</a>
        <a href="#testimonials" class="text-gray-600 hover:text-blue-600 transition-colors">Testimonials</a>
        <a href="#pricing" class="text-gray-600 hover:text-blue-600 transition-colors">Investment Plans</a>
        <a href="#contact" class="text-gray-600 hover:text-blue-600 transition-colors">Contact</a>
      </nav>
      
      <div class="flex items-center space-x-4">
        <a href="login.php" class="hidden md:inline-block text-gray-600 hover:text-blue-600 transition-colors">Login</a>
        <button class="hidden md:block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
          <a href="register.php"> Get Started</a>

        </button>
        
        <!-- Mobile Menu Button -->
        <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-blue-600 focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Mobile Navigation Menu -->
    <div id="mobile-menu" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden">
      <div class="bg-white h-full w-64 max-w-sm py-4 px-6 shadow-xl transform transition-transform duration-300 -translate-x-full">
        <div class="flex justify-between items-center mb-8">
          <div class="text-2xl font-bold text-blue-600">Benefit Market Trade</div>
          <button id="close-mobile-menu" class="text-gray-600 hover:text-blue-600 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <nav class="flex flex-col space-y-4">
          <a href="#features" class="text-gray-600 hover:text-blue-600 transition-colors py-2 border-b border-gray-100">Features</a>
          <a href="#how-it-works" class="text-gray-600 hover:text-blue-600 transition-colors py-2 border-b border-gray-100">How It Works</a>
          <a href="#market-insights" class="text-gray-600 hover:text-blue-600 transition-colors py-2 border-b border-gray-100">Market Insights</a>
          <a href="#testimonials" class="text-gray-600 hover:text-blue-600 transition-colors py-2 border-b border-gray-100">Testimonials</a>
          <a href="#pricing" class="text-gray-600 hover:text-blue-600 transition-colors py-2 border-b border-gray-100">Investment Plans</a>
          <a href="#contact" class="text-gray-600 hover:text-blue-600 transition-colors py-2 border-b border-gray-100">Contact</a>
          <a href="login.php" class="text-gray-600 hover:text-blue-600 transition-colors py-2 border-b border-gray-100">Login</a>
        </nav>
        <div class="mt-8">
          <button href="register.php" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
          <a href="register.php"> Get Started</a>

        </button>
        </div>
      </div>
    </div>
  </header>
  
  <!-- Hero Section -->
  <section class="gradient-bg text-white py-20">
    <div class="container mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center">
        <div class="md:w-1/2 mb-10 md:mb-0">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
            Trade Smarter, Not Harder
          </h1>
          <p class="text-xl mb-8 text-blue-100">
           Benefit Market Trade provides cutting-edge tools and real-time analytics to help you make informed trading decisions.
          </p>
          <div class="flex flex-col sm:flex-row gap-4">
            <button class="bg-white text-blue-600 px-6 py-3 rounded-md font-medium hover:bg-blue-50 transition-colors" href="login.php">
            <a href="login.php"> Start Trading</a>
          </button>
            <button class="bg-transparent border border-white text-white px-6 py-3 rounded-md font-medium hover:bg-white/10 transition-colors">
              Learn More
            </button>
          </div>
        </div>
        <div class="md:w-1/2">
          <img 
            src="https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" 
            alt="Trading platform dashboard with charts and data" 
            class="rounded-lg shadow-xl"
          >
        </div>
      </div>
    </div>
  </section>
  
  <!-- Stats Section -->
  <section class="py-12 bg-white">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div class="p-4">
          <div class="text-3xl md:text-4xl font-bold text-blue-600 mb-2">$2.5B+</div>
          <p class="text-gray-600">Trading Volume</p>
        </div>
        <div class="p-4">
          <div class="text-3xl md:text-4xl font-bold text-blue-600 mb-2">150K+</div>
          <p class="text-gray-600">Active Traders</p>
        </div>
        <div class="p-4">
          <div class="text-3xl md:text-4xl font-bold text-blue-600 mb-2">99.9%</div>
          <p class="text-gray-600">Uptime</p>
        </div>
        <div class="p-4">
          <div class="text-3xl md:text-4xl font-bold text-blue-600 mb-2">24/7</div>
          <p class="text-gray-600">Support</p>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Features Section -->
  <section id="features" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Why Choose Benefit Market Trade</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Our platform offers everything you need to succeed in today's dynamic markets.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Feature 1 -->
        <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow hover-scale">
          <div class="text-4xl mb-4">📊</div>
          <h3 class="text-xl font-bold mb-3">Real-time Analytics</h3>
          <p class="text-gray-600">Access live market data and analytics to make informed trading decisions.</p>
        </div>
        
        <!-- Feature 2 -->
        <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow hover-scale">
          <div class="text-4xl mb-4">🛠️</div>
          <h3 class="text-xl font-bold mb-3">Advanced Trading Tools</h3>
          <p class="text-gray-600">Utilize powerful tools designed for both beginners and professional traders.</p>
        </div>
        
        <!-- Feature 3 -->
        <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow hover-scale">
          <div class="text-4xl mb-4">🔒</div>
          <h3 class="text-xl font-bold mb-3">Secure Transactions</h3>
          <p class="text-gray-600">Trade with confidence knowing your transactions are protected by bank-level security.</p>
        </div>
        
        <!-- Feature 4 -->
        <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow hover-scale">
          <div class="text-4xl mb-4">🌐</div>
          <h3 class="text-xl font-bold mb-3">24/7 Market Access</h3>
          <p class="text-gray-600">Trade global markets anytime, anywhere with our mobile-friendly platform.</p>
        </div>
      </div>
    </div>
  </section>
  
  <!-- How It Works Section -->
  <section id="how-it-works" class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">How It Works</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Getting started with Benefit Market Trade is simple and straightforward.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
        <div class="text-center">
          <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-6">1</div>
          <h3 class="text-xl font-bold mb-3">Create an Account</h3>
          <p class="text-gray-600">Sign up in minutes with our simple registration process. Verify your identity to ensure account security.</p>
        </div>
        
        <div class="text-center">
          <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-6">2</div>
          <h3 class="text-xl font-bold mb-3">Choose Your Plan</h3>
          <p class="text-gray-600">Select an investment plan that matches your financial goals and risk tolerance.</p>
        </div>
        
        <div class="text-center">
          <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-6">3</div>
          <h3 class="text-xl font-bold mb-3">Start Trading</h3>
          <p class="text-gray-600">Fund your account and begin trading with our intuitive platform. Monitor your investments in real-time.</p>
        </div>
      </div>
      
     <div class="mt-16 text-center">
  <img 
    src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" 
    alt="Trading platform interface showing investment process" 
    class="rounded-lg shadow-xl max-w-4xl w-full mx-auto"
  >
</div>

    </div>
  </section>
  
  <!-- Market Insights Section -->
  <section id="market-insights" class="py-20 gradient-bg-dark text-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Market Insights</h2>
        <p class="text-xl text-blue-100 max-w-3xl mx-auto">
          Stay ahead of market trends with our advanced analytics and expert insights.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div>
        <img 
  src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" 
  alt="Advanced trading charts and analytics" 
  class="rounded-lg shadow-xl">

        </div>
        
        <div>
          <h3 class="text-2xl font-bold mb-6">Data-Driven Trading Decisions</h3>
          <ul class="space-y-4">
            <li class="flex items-start">
              <span class="text-blue-300 mr-3 text-xl">✓</span>
              <div>
                <h4 class="font-bold mb-1">Real-Time Market Data</h4>
                <p class="text-blue-100">Access up-to-the-second market data across all major global exchanges.</p>
              </div>
            </li>
            <li class="flex items-start">
              <span class="text-blue-300 mr-3 text-xl">✓</span>
              <div>
                <h4 class="font-bold mb-1">Technical Analysis Tools</h4>
                <p class="text-blue-100">Over 100+ technical indicators and drawing tools to analyze market trends.</p>
              </div>
            </li>
            <li class="flex items-start">
              <span class="text-blue-300 mr-3 text-xl">✓</span>
              <div>
                <h4 class="font-bold mb-1">AI-Powered Predictions</h4>
                <p class="text-blue-100">Our proprietary algorithms help predict market movements with high accuracy.</p>
              </div>
            </li>
            <li class="flex items-start">
              <span class="text-blue-300 mr-3 text-xl">✓</span>
              <div>
                <h4 class="font-bold mb-1">Expert Market Analysis</h4>
                <p class="text-blue-100">Daily reports and insights from our team of experienced market analysts.</p>
              </div>
            </li>
          </ul>
          <button class="mt-8 bg-white text-blue-700 px-6 py-3 rounded-md font-medium hover:bg-blue-50 transition-colors">
            Explore Market Tools
          </button>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Security Section -->
  <section class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div class="order-2 md:order-1">
          <h2 class="text-3xl md:text-4xl font-bold mb-6">Bank-Level Security</h2>
          <p class="text-xl text-gray-600 mb-8">
            Your security is our top priority. We implement the most advanced security measures to protect your investments.
          </p>
          <div class="space-y-6">
            <div class="flex items-start">
              <div class="bg-blue-100 p-3 rounded-full mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold mb-1">256-bit Encryption</h3>
                <p class="text-gray-600">All data is encrypted using military-grade encryption technology.</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="bg-blue-100 p-3 rounded-full mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold mb-1">Two-Factor Authentication</h3>
                <p class="text-gray-600">Add an extra layer of security to your account with 2FA.</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="bg-blue-100 p-3 rounded-full mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold mb-1">Cold Storage</h3>
                <p class="text-gray-600">Majority of assets are stored in offline cold storage vaults.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="order-1 md:order-2">
          <img 
            src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" 
            alt="Security and encryption concept" 
            class="rounded-lg shadow-xl"
          >
        </div>
      </div>
    </div>
  </section>
  
  <!-- Remove the Mobile App Section -->
  
  <!-- Testimonials Section -->
  <section id="testimonials" class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">What Our Traders Say</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Join thousands of satisfied traders who have chosenTradexpro for their trading journey.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Testimonial 1 -->
        <div class="bg-gray-50 p-8 rounded-lg hover-scale">
          <div class="text-blue-600 text-4xl mb-4">"</div>
          <p class="text-gray-700 mb-6">nextrade has completely transformed how I approach trading. The platform is intuitive and the analytics are game-changing.</p>
          <div class="flex items-center">
            <img 
              src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" 
              alt="Sarah Johnson" 
              class="w-12 h-12 rounded-full mr-4 object-cover"
            >
            <div>
              <h4 class="font-bold">Sarah Johnson</h4>
              <p class="text-gray-600 text-sm">Day Trader</p>
            </div>
          </div>
        </div>
        
        <!-- Testimonial 2 -->
        <div class="bg-gray-50 p-8 rounded-lg hover-scale">
          <div class="text-blue-600 text-4xl mb-4">"</div>
          <p class="text-gray-700 mb-6">As a beginner, I was intimidated by trading platforms.Benefit Market Trade made it easy to get started and learn the ropes.</p>
          <div class="flex items-center">
            <img 
              src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" 
              alt="Michael Chen" 
              class="w-12 h-12 rounded-full mr-4 object-cover"
            >
            <div>
              <h4 class="font-bold">Michael Chen</h4>
              <p class="text-gray-600 text-sm">Retail Investor</p>
            </div>
          </div>
        </div>
        
        <!-- Testimonial 3 -->
        <div class="bg-gray-50 p-8 rounded-lg hover-scale">
          <div class="text-blue-600 text-4xl mb-4">"</div>
          <p class="text-gray-700 mb-6">The security features and customer support atTradexpro are unmatched. I've tried many platforms, but this is my go-to.</p>
          <div class="flex items-center">
            <img 
              src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&q=80" 
              alt="David Rodriguez" 
              class="w-12 h-12 rounded-full mr-4 object-cover"
            >
            <div>
              <h4 class="font-bold">David Rodriguez</h4>
              <p class="text-gray-600 text-sm">Professional Trader</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Investment Plans Section -->
  <section id="pricing" class="py-20 gradient-bg text-white">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-3xl md:text-4xl font-bold mb-6">Investment Plans</h2>
      <p class="text-xl mb-10 max-w-3xl mx-auto">
        Choose the investment plan that best suits your financial goals and risk tolerance.
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Basic Plan -->
        <div class="bg-white text-gray-800 rounded-lg shadow-lg overflow-hidden hover-scale">
          <div class="p-8">
            <h3 class="text-2xl font-bold mb-4">Basic Plan</h3>
            <div class="text-xl font-bold mb-4">$200 - $1,100</div>
            <div class="bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold inline-block mb-4">
              0.50% Daily ROI
            </div>
            <ul class="space-y-3 mb-8 text-left">
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Every 30 Days Withdrawal
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Digital Currency
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> 3.00% Referral Bonus
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Low entry point
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Fixed daily returns for stability
              </li>
            </ul>
            <button class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition-colors">
              Get Started
            </button>
          </div>
        </div>
        
        <!-- Silver Plan -->
        <div class="bg-white text-gray-800 rounded-lg shadow-lg overflow-hidden hover-scale">
                      <div class="bg-blue-700 text-white text-center py-2 text-sm font-bold">POPULAR CHOICE</div>
          <div class="p-8">
            <h3 class="text-2xl font-bold mb-4">Silver Plan</h3>
            <div class="text-xl font-bold mb-4">$1,100 - $2,500</div>
            <div class="bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold inline-block mb-4">
              0.60% Daily ROI
            </div>
            <ul class="space-y-3 mb-8 text-left">
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Every 14 Days Withdrawal
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Bank Transfer Withdrawal
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> 7.00% Referral Bonus
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Moderate entry point
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Improved daily returns
              </li>
            </ul>
            <button class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition-colors">
              Get Started
            </button>
          </div>
        </div>
        
        <!-- Gold Plan -->
        <div class="bg-white text-gray-800 rounded-lg shadow-lg overflow-hidden transform scale-105 hover-scale">

          <div class="p-8">
            <h3 class="text-2xl font-bold mb-4">Gold Plan</h3>
            <div class="text-xl font-bold mb-4">$2,500 - $5,000</div>
            <div class="bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold inline-block mb-4">
              0.70% Daily ROI
            </div>
            <ul class="space-y-3 mb-8 text-left">
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Every 7 Days Withdrawal
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Digital Currency, Bank Transfer
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> 15.00% Referral Bonus
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Competitive ROI with balanced risk
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Suitable for intermediate investors
              </li>
            </ul>
            <button class="w-full bg-blue-700 text-white py-3 rounded-md hover:bg-blue-800 transition-colors">
              Get Started
            </button>
          </div>
        </div>
        
        <!-- Platinum Plan -->
        <div class="bg-white text-gray-800 rounded-lg shadow-lg overflow-hidden hover-scale">
          <div class="p-8">
            <h3 class="text-2xl font-bold mb-4">Platinum Plan</h3>
            <div class="text-xl font-bold mb-4">$5,001 - $10,000</div>
            <div class="bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold inline-block mb-4">
              0.80% Daily ROI
            </div>
            <ul class="space-y-3 mb-8 text-left">
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Every 3 Days Withdrawal
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Trading bot 
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> 22.00% Referral Bonus
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Advanced benefits
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Increased withdrawal frequency
              </li>
            </ul>
            <button class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition-colors">
              Get Started
            </button>
          </div>
        </div>
        
        <!-- Diamond Plan -->
        <div class="bg-white text-gray-800 rounded-lg shadow-lg overflow-hidden hover-scale">
          <div class="p-8">
            <h3 class="text-2xl font-bold mb-4">Diamond Plan</h3>
            <div class="text-xl font-bold mb-4">$10,001 - $18,000</div>
            <div class="bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold inline-block mb-4">
              0.90% Daily ROI
            </div>
            <ul class="space-y-3 mb-8 text-left">
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Unlimited Withdrawal
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Trade Bots and Signals
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> 25.00% Referral Bonus
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Premium features
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Optimized returns with minimal risk
              </li>
            </ul>
            <button class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition-colors">
              Get Started
            </button>
          </div>
        </div>
        
        <!-- Ultimate Plan -->
        <div class="bg-white text-gray-800 rounded-lg shadow-lg overflow-hidden hover-scale">
          <div class="p-8">
            <h3 class="text-2xl font-bold mb-4">Ultimate Plan</h3>
            <div class="text-xl font-bold mb-4">$20,001 and above</div>
            <div class="bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold inline-block mb-4">
              1.00% Daily ROI
            </div>
            <ul class="space-y-3 mb-8 text-left">
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Unlimited Withdrawal
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Digital Currency, Bank Transfer, Credit Card
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> 30.00% Referral Bonus
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Exclusive benefits
              </li>
              <li class="flex items-center">
                <span class="text-green-500 mr-2">✓</span> Highest daily ROI and personalized service
              </li>
            </ul>
            <button class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition-colors">
              Get Started
            </button>
          </div>
        </div>
      </div>
      
      <div class="mt-16 max-w-3xl mx-auto bg-white/10 p-6 rounded-lg">
        <h3 class="text-2xl font-bold mb-4">Not sure which plan is right for you?</h3>
        <p class="mb-6">Our investment advisors can help you choose the best plan based on your investment goals and risk tolerance.</p>
        <button class="bg-white text-blue-600 px-6 py-3 rounded-md font-medium hover:bg-blue-50 transition-colors">
          Schedule a Consultation
        </button>
      </div>
    </div>
  </section>
  
  <!-- ROI Calculator Section -->
  <section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Calculate Your Returns</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Use our calculator to estimate your potential returns based on your investment amount.
        </p>
      </div>
      
      <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h3 class="text-2xl font-bold mb-6">Investment Calculator</h3>
            <div class="space-y-6">
              <div>
                <label class="block text-gray-700 font-medium mb-2">Investment Amount ($)</label>
                <input type="number" id="investment-amount" min="100" value="20000" placeholder="Enter amount" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Investment Plan</label>
                <select id="investment-plan" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="0.5">Basic Plan (0.50% Daily)</option>
                  <option value="1.6">Silver Plan (1.60% Daily)</option>
                  <option value="2.7" selected>Gold Plan (2.70% Daily)</option>
                  <option value="5.8">Platinum Plan (5.80% Daily)</option>
                  <option value="10.9">Diamond Plan (10.90% Daily)</option>
                  <option value="15.0">Ultimate Plan (15.00% Daily)</option>
                </select>
              </div>
              <div>
                <label class="block text-gray-700 font-medium mb-2">Investment Period (Days)</label>
                <input type="number" id="investment-period" min="30" max="365" value="30" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <button id="calculate-btn" class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition-colors">
                Calculate Returns
              </button>
            </div>
          </div>
          <div class="bg-gray-50 p-6 rounded-lg">
            <h3 class="text-xl font-bold mb-6">Estimated Returns</h3>
            <div class="space-y-4">
              <div class="flex justify-between border-b border-gray-200 pb-2">
                <span class="text-gray-600">Initial Investment:</span>
                <span id="initial-investment" class="font-bold">$10,000.00</span>
              </div>
              <div class="flex justify-between border-b border-gray-200 pb-2">
                <span class="text-gray-600">Daily ROI:</span>
                <span id="daily-roi" class="font-bold">0.70%</span>
              </div>
              <div class="flex justify-between border-b border-gray-200 pb-2">
                <span class="text-gray-600">Daily Profit:</span>
                <span id="daily-profit" class="font-bold text-green-600">$70.00</span>
              </div>
              <div class="flex justify-between border-b border-gray-200 pb-2">
                <span class="text-gray-600">Monthly Profit:</span>
                <span id="monthly-profit" class="font-bold text-green-600">$2,100.00</span>
              </div>
              <div class="flex justify-between pt-2">
                <span class="text-gray-600">Total Return (<span id="period-display">30</span> days):</span>
                <span id="total-return" class="font-bold text-green-600">$12,100.00</span>
              </div>
            </div>
            <div class="mt-8">
              <div class="h-4 w-full bg-gray-200 rounded-full overflow-hidden">
                <div id="progress-bar" class="h-full bg-blue-600 rounded-full" style="width: 21%"></div>
              </div>
              <div class="flex justify-between mt-2 text-sm">
                <span id="initial-display">Initial: $10,000</span>
                <span id="return-display">Return: $12,100</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- FAQ Section -->
  <section class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Frequently Asked Questions</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Find answers to common questions about our investment plans and platform.
        </p>
      </div>
      
      <div class="max-w-4xl mx-auto">
        <div class="space-y-6">
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">How do I get started with Benefit Market Trade?</h3>
            <p class="text-gray-600">Getting started is simple. Create an account, verify your identity, choose an investment plan, and fund your account to begin trading.</p>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">What is the minimum investment amount?</h3>
            <p class="text-gray-600">The minimum investment amount is $1,700 with our Basic Plan. Each plan has its own minimum and maximum investment range.</p>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">How often can I withdraw my profits?</h3>
            <p class="text-gray-600">Withdrawal frequency depends on your investment plan, ranging from every 30 days for the Basic Plan to every 15 days for the Ultimate Plan.</p>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">What payment methods do you accept?</h3>
            <p class="text-gray-600">We accept various payment methods including digital currencies, bank transfers, and credit cards, depending on your selected plan.</p>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">Is my investment secure?</h3>
            <p class="text-gray-600">Yes, we implement bank-level security measures including 256-bit encryption, two-factor authentication, and cold storage for digital assets.</p>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2">How does the referral program work?</h3>
            <p class="text-gray-600">Our referral program allows you to earn a percentage of your referrals' investments. The bonus percentage ranges from 15% to 30% depending on your plan.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- CTA Section -->
  <section class="py-20 gradient-bg text-white">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Start Your Investment Journey?</h2>
      <p class="text-xl mb-10 max-w-3xl mx-auto">
        Join thousands of successful investors onTradexpro and start growing your wealth today.
      </p>
      <div class="flex flex-col sm:flex-row justify-center gap-4">
        <button class="bg-white text-blue-600 px-8 py-4 rounded-md font-medium hover:bg-blue-50 transition-colors text-lg">
          Create Account
        </button>
        <button class="bg-transparent border border-white text-white px-8 py-4 rounded-md font-medium hover:bg-white/10 transition-colors text-lg">
          Learn More
        </button>
      </div>
    </div>
  </section>
  
  <!-- Footer -->
  <footer id="contact" class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <h3 class="text-xl font-bold mb-4">Benefit Market Trade</h3>
          <p class="text-gray-400">
            Your trusted partner for online trading and investment solutions.
          </p>
          <div class="flex space-x-4 mt-6">
            <a href="login.php" class="text-gray-400 hover:text-white transition-colors">
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"></path>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path>
              </svg>
            </a>
          </div>
        </div>
        
        <div>
          <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
          <ul class="space-y-2">
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
            <li><a href="#features" class="text-gray-400 hover:text-white transition-colors">Features</a></li>
            <li><a href="#how-it-works" class="text-gray-400 hover:text-white transition-colors">How It Works</a></li>
            <li><a href="#testimonials" class="text-gray-400 hover:text-white transition-colors">Testimonials</a></li>
            <li><a href="#pricing" class="text-gray-400 hover:text-white transition-colors">Investment Plans</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="text-lg font-semibold mb-4">Resources</h4>
          <ul class="space-y-2">
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Market News</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Trading Guides</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Help Center</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Security</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
          <ul class="space-y-2 text-gray-400">
            <li class="flex items-start">
              <svg class="h-6 w-6 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>405 Rancho Arroyo Pky
              Fremont, California(CA), 94536</span>
            </li>
            <li class="flex items-start">
              <svg class="h-6 w-6 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <span>support@nextrade.com</span>
            </li>
<!--<li class="flex items-start">-->
<!--  <a href="https://wa.me/17703623253" target="_blank" rel="noopener noreferrer" class="flex items-center">-->
<!--    <svg class="h-6 w-6 mr-2 text-green-500" fill="currentColor" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg">-->
<!--      <path d="M380.9 97.1C339 55.2 283.8 32 224 32 100.3 32 0 132.3 0 256c0 45.2 12 88.1 34.8 125L0 480l105.6-33.9c36.3 19.8 77.6 30.2 118.4 30.2 123.7 0 224-100.3 224-224 0-59.8-23.2-115-97.1-158.9zM224 438c-39.8 0-78.7-10.5-112.3-30.3l-8-4.8-62.8 20.2 21.3-65.4-5.2-8C50.1 304.7 38 282.8 38 256 38 148.3 148.3 38 256 38c57.3 0 110.7 22.3 150.7 62.3C446 140.3 448 197.7 448 256c0 107.7-87.3 194-194 194zm101.7-138.4c-5.5-2.8-32.7-16.2-37.8-18-5.1-1.8-8.8-2.8-12.4 2.8-3.6 5.6-13.8 18-16.9 21.8-3.1 3.8-6.3 4.3-11.8 1.5-5.5-2.8-29.3-10.8-55.8-34.4-20.6-18.4-34.6-41.2-38.6-46.8-3.9-5.6-.4-8.6 2.9-11.4 3-2.8 6.7-7.1 10-10.7 3.3-3.6 4.4-5.6 6.6-9.4 2.2-3.8 1.1-7.2-.6-10-1.8-2.8-12.4-30-17-41.2-4.5-10.8-9.1-9.3-12.8-9.5-3.3-.2-7-.2-10.7-.2s-10.1 1.5-15.4 7.2c-5.3 5.7-20.2 19.7-20.2 48 0 28.3 20.6 55.7 23.5 59.3 2.8 3.6 39.7 60.7 96.3 84.9 13.5 5.8 24.1 9.3 32.3 11.9 13.5 4.3 25.8 3.7 35.6 2.3 10.8-1.5 32.7-13.3 37.3-26.3 4.5-12.8 4.5-23.8 3.2-26.3-1.3-2.5-5-3.8-10.5-6.6z"/>-->
<!--    </svg>-->
<!--    <span class="text-gray-500">+17703623253</span>-->
<!--  </a>-->
<!--</li>-->

          </ul>
          <div class="mt-6">
            <h5 class="font-semibold mb-2">Subscribe to our newsletter</h5>
            <div class="flex">
              <input type="email" placeholder="Your email" class="px-4 py-2 w-full rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <button class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 transition-colors">
                Subscribe
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
        <p class="text-gray-400 mb-4 md:mb-0">
          © 2012 - <script>document.write(new Date().getFullYear())</script> Benefit Market Trade. All rights reserved.
        </p>
        <div class="flex space-x-6">
          <a href="#" class="text-gray-400 hover:text-white transition-colors">
            Terms of Service
          </a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors">
            Privacy Policy
          </a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors">
            Cookie Policy
          </a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Back to Top Button -->
  <button id="back-to-top" class="fixed bottom-8 right-8 bg-blue-600 text-white p-3 rounded-full shadow-lg opacity-0 invisible transition-all duration-300">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
  </button>







<!-- GTranslate Widget -->
  <div class="gtranslate_wrapper"></div>
  <script>
    window.gtranslateSettings = {"default_language":"en","wrapper_selector":".gtranslate_wrapper"};
  </script>
  <script src="https://cdn.gtranslate.net/widgets/latest/float.js" defer></script>
  
  <!-- Combined JavaScript for Interactive Elements and Smartsupp Live Chat -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Current year for footer copyright
      const year = new Date().getFullYear();
      document.querySelector('footer .text-gray-400').innerHTML = 
        `© ${year} Benefit Market Trade. All rights reserved.`;
    
      // Back to top button functionality
      const backToTopButton = document.getElementById('back-to-top');
      if (backToTopButton) {
        window.addEventListener('scroll', function() {
          if (window.pageYOffset > 300) {
            backToTopButton.classList.remove('opacity-0', 'invisible');
            backToTopButton.classList.add('opacity-100', 'visible');
          } else {
            backToTopButton.classList.remove('opacity-100', 'visible');
            backToTopButton.classList.add('opacity-0', 'invisible');
          }
        });
      
        backToTopButton.addEventListener('click', function() {
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        });
      }
    
      // Mobile menu toggle functionality
      const mobileMenuButton = document.getElementById('mobile-menu-button');
      const closeMobileMenuButton = document.getElementById('close-mobile-menu');
      const mobileMenu = document.getElementById('mobile-menu');
      if (mobileMenuButton && closeMobileMenuButton && mobileMenu) {
        const mobileMenuContent = mobileMenu.querySelector('div');
    
        function openMobileMenu() {
          mobileMenu.classList.remove('hidden');
          setTimeout(() => { mobileMenuContent.classList.remove('-translate-x-full'); }, 10);
          document.body.style.overflow = 'hidden'; // Prevent scrolling
        }
    
        function closeMobileMenu() {
          mobileMenuContent.classList.add('-translate-x-full');
          setTimeout(() => {
            mobileMenu.classList.add('hidden');
            document.body.style.overflow = ''; // Re-enable scrolling
          }, 300);
        }
    
        mobileMenuButton.addEventListener('click', openMobileMenu);
        closeMobileMenuButton.addEventListener('click', closeMobileMenu);
    
        mobileMenu.addEventListener('click', function(e) {
          if (e.target === mobileMenu) closeMobileMenu();
        });
    
        const mobileMenuLinks = mobileMenu.querySelectorAll('a');
        mobileMenuLinks.forEach(link => { link.addEventListener('click', closeMobileMenu); });
      }
    
      // Investment Calculator Functionality
      const investmentAmount = document.getElementById('investment-amount');
      const investmentPlan = document.getElementById('investment-plan');
      const investmentPeriod = document.getElementById('investment-period');
      const calculateBtn = document.getElementById('calculate-btn');
    
      const initialInvestment = document.getElementById('initial-investment');
      const dailyRoi = document.getElementById('daily-roi');
      const dailyProfit = document.getElementById('daily-profit');
      const monthlyProfit = document.getElementById('monthly-profit');
      const totalReturn = document.getElementById('total-return');
      const periodDisplay = document.getElementById('period-display');
      const progressBar = document.getElementById('progress-bar');
      const initialDisplay = document.getElementById('initial-display');
      const returnDisplay = document.getElementById('return-display');
    
      // Format currency
      function formatCurrency(value) {
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(value);
      }
    
      // Calculate returns
      function calculateReturns() {
        const amount = parseFloat(investmentAmount.value);
        const roi = parseFloat(investmentPlan.value);
        const period = parseInt(investmentPeriod.value);
    
        const dailyProfitValue = amount * (roi / 100);
        const monthlyProfitValue = dailyProfitValue * 30;
        const totalReturnValue = amount + (dailyProfitValue * period);
        const percentageGrowth = ((totalReturnValue / amount) - 1) * 100;
    
        if (initialInvestment) initialInvestment.textContent = formatCurrency(amount);
        if (dailyRoi) dailyRoi.textContent = roi.toFixed(2) + '%';
        if (dailyProfit) dailyProfit.textContent = formatCurrency(dailyProfitValue);
        if (monthlyProfit) monthlyProfit.textContent = formatCurrency(monthlyProfitValue);
        if (totalReturn) totalReturn.textContent = formatCurrency(totalReturnValue);
        if (periodDisplay) periodDisplay.textContent = period;
    
        if (progressBar) progressBar.style.width = Math.min(percentageGrowth, 1000) + '%';
    
        if (initialDisplay) initialDisplay.textContent = 'Initial: ' + formatCurrency(amount);
        if (returnDisplay) returnDisplay.textContent = 'Return: ' + formatCurrency(totalReturnValue);
      }
    
      if (calculateBtn && investmentAmount && investmentPlan && investmentPeriod) {
        calculateReturns();
        calculateBtn.addEventListener('click', calculateReturns);
        investmentAmount.addEventListener('input', calculateReturns);
        investmentPlan.addEventListener('change', calculateReturns);
        investmentPeriod.addEventListener('input', calculateReturns);
      }
    });
    
    /* Smartsupp Live Chat Integration */
    var _smartsupp = _smartsupp || {};
    _smartsupp.key = 'd1f5f5a15997e673174e653d52040e12a3651a08';
    window.smartsupp||(function(d) {
      var s,c,o = smartsupp = function() { o._.push(arguments) };
      o._ = [];
      s = d.getElementsByTagName('script')[0];
      c = d.createElement('script');
      c.type = 'text/javascript';
      c.charset = 'utf-8';
      c.async = true;
      c.src = 'https://www.smartsuppchat.com/loader.js?';
      s.parentNode.insertBefore(c, s);
    })(document);
  </script>
  <noscript>Powered by <a href="https://www.smartsupp.com" target="_blank">Smartsupp</a></noscript>


</body>
</html>

