<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>Benefit Market Trade- AI Trading</title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<meta content="" name="keywords" />
		<meta content="" name="description" />

		<!-- Google Web Fonts -->
		<link rel="preconnect" href="https://fonts.googleapis.com" />
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
		<link
			href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Roboto:wght@400;500;700;900&display=swap"
			rel="stylesheet" />

		<!-- Icon Font Stylesheet -->
		<link
			rel="stylesheet"
			href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css"
			rel="stylesheet" />

		<!-- Libraries Stylesheet -->
		<link rel="stylesheet" href="lib/animate/animate.min.css" />
		<link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet" />
		<link
			href="lib/owlcarousel/assets/owl.carousel.min.css"
			rel="stylesheet" />

		<!-- Favicon -->
		<link
			rel="icon"
			href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/icons/home-line.svg"
			type="image/svg+xml"
			style="
				filter: invert(0.5) sepia(1) saturate(5) hue-rotate(190deg);
			" />
		<!-- Customized Bootstrap Stylesheet -->
		<link href="css/bootstrap.min.css" rel="stylesheet" />
		<!-- Tailwind CSS -->
		<link
			href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
			rel="stylesheet" />
		<!-- Template Stylesheet -->
		<link href="css/style.css" rel="stylesheet" />

		<!-- Remix Icon  -->
		<link
			rel="stylesheet"
			href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" />
	</head>

	<body>
		<!-- Spinner Start -->
		<div
			id="spinner"
			class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
			<div class="lds-graph">
				<div></div>
				<div></div>
				<div></div>
			</div>
		</div>
		<style>
			.lds-graph,
			.lds-graph div {
				box-sizing: border-box;
			}
			.lds-graph {
				display: inline-block;
				position: relative;
				width: 80px;
				height: 80px;
			}
			.lds-graph div {
				display: inline-block;
				position: absolute;
				width: 16px;
				background: currentColor;
				animation: lds-graph 10s cubic-bezier(0, 0.5, 0.5, 1) infinite;
			}
			.lds-graph div:nth-child(1) {
				left: 8px;
				animation-delay: -2.4s;
			}
			.lds-graph div:nth-child(2) {
				left: 32px;
				animation-delay: -1.2s;
			}
			.lds-graph div:nth-child(3) {
				left: 56px;
				animation-delay: 0s;
			}
			@keyframes lds-graph {
				0% {
					top: 8px;
					height: 64px;
				}
				50% {
					top: 24px;
					height: 32px;
				}
				100% {
					top: 8px;
					height: 64px;
				}
			}
		</style>
		<!-- Spinner End -->

		<!-- Navbar & Hero Start -->
		<div class="container-fluid position-relative p-0">
			<nav
				class="navbar navbar-expand-lg navbar-light px-4 px-lg-5 py-3 py-lg-0">
				<a href="index.php" class="flex items-center text-primary">
					<i class="ri-home-line text-2xl"></i>
					<span class="text-2xl ml-2">Benefit Market Trade</span>
				</a>
				<br />
				<button
					class="navbar-toggler"
					type="button"
					data-bs-toggle="collapse"
					data-bs-target="#navbarCollapse">
					<span class="fa fa-bars"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarCollapse">
					<div class="navbar-nav ms-auto py-0">
						<a href="index.php" class="nav-item nav-link"
							>Home</a
						>
						<a href="about.php" class="nav-item nav-link">About</a>
						<a href="service.php" class="nav-item nav-link"
							>Services</a
						>
						<div class="nav-item dropdown">
							<a
								href="#"
								class="nav-link"
								data-bs-toggle="dropdown">
								<span class="dropdown-toggle">Pages</span>
							</a>
							<div class="dropdown-menu m-0">
								<a href="feature.php" class="dropdown-item"
									>Our Features</a
								>

								<a href="testimonial.php" class="dropdown-item"
									>Testimonial</a
								>
								<a href="offer.php" class="dropdown-item active"
									>Our offer</a
								>
								<a href="FAQ.php" class="dropdown-item"
									>FAQs</a
								>
								<!-- <a href="404.php" class="dropdown-item">404 Page</a> -->
							</div>
						</div>
						<a href="contact.php" class="nav-item nav-link"
							>Contact Us</a
						>
					</div>
					<a
						href="/login.php"
						class="btn btn-primary rounded-pill py-2 px-4 my-3 my-lg-0 flex-shrink-0"
						>Get Started</a
					>
				</div>
			</nav>
			<!-- Nav End -->


			<!-- Header Start -->
			<div class="container-fluid bg-breadcrumb">
				<div
					class="container text-center py-5"
					style="max-width: 900px">
					<h4
						class="text-white display-4 mb-4 wow fadeInDown"
						data-wow-delay="0.1s">
						Our Offer
					</h4>
					<ol
						class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown"
						data-wow-delay="0.3s">
						<li class="breadcrumb-item">
							<a href="index.php">Home</a>
						</li>
						<li class="breadcrumb-item"><a href="#">Pages</a></li>
						<li class="breadcrumb-item active text-primary">
							Our offer
						</li>
					</ol>
				</div>
			</div>
			<!-- Header End -->
		</div>
		<!-- Navbar & Hero End -->

		<!-- Offer Start -->
		<section class="bg-gray-900 py-16">
			<div class="max-w-7xl mx-auto px-4 text-center">
				<h2 class="text-4xl font-extrabold text-white mb-4">
					Our Investment Plans
				</h2>
				<p class="text-gray-400 mb-12">
					Choose the perfect plan to grow your portfolio.
				</p>
				<div class="grid gap-8 lg:grid-cols-4 md:grid-cols-2">
					<!-- Basic Plan -->
					<div
						class="bg-gray-800 rounded-2xl shadow-lg transform hover:scale-105 transition p-6 flex flex-col border-2 border-green-400">
						<h3 class="text-xl font-semibold text-green-400 mb-2">
							BASIC
						</h3>
						<p class="text-gray-300 mb-4">20% ROI after 24 hours</p>
						<p class="text-gray-400 mb-6">Investment Range</p>
						<p class="text-white text-2xl font-bold mb-6">
							$250 – $2,500
						</p>
						<ul class="text-gray-300 space-y-2 mb-8 flex-1">
							<li>📈 Daily Performance Dashboard</li>
							<li>✉️ Email Support</li>
							<li>🔒 Secure Wallet Integration</li>
						</ul>
						<a
							href="/login.php"
							class="mt-auto inline-block bg-green-400 text-gray-900 font-semibold py-2 rounded-full hover:bg-green-500 transition">
							Sign Up
						</a>
					</div>

					<!-- Standard Plan -->
					<div
						class="bg-gray-800 rounded-2xl shadow-lg transform hover:scale-105 transition p-6 flex flex-col">
						<h3 class="text-xl font-semibold text-green-400 mb-2">
							STANDARD
						</h3>
						<p class="text-gray-300 mb-4">35% ROI after 48 hours</p>
						<p class="text-gray-400 mb-6">Investment Range</p>
						<p class="text-white text-2xl font-bold mb-6">
							$2,500 – $20,000
						</p>
						<ul class="text-gray-300 space-y-2 mb-8 flex-1">
							<li>✅ Everything in Basic</li>
							<li>📊 Weekly Performance Report</li>
							<li>⚡ Priority Email & Chat Support</li>
						</ul>
						<a
							href="/login.php"
							class="mt-auto inline-block bg-green-400 text-gray-900 font-semibold py-2 rounded-full hover:bg-green-500 transition">
							Sign Up
						</a>
					</div>

					<!-- Gold Plan -->
					<div
						class="bg-gray-800 rounded-2xl shadow-lg transform hover:scale-105 transition p-6 flex flex-col">
						<h3 class="text-xl font-semibold text-green-400 mb-2">
							GOLD
						</h3>
						<p class="text-gray-300 mb-4">50% ROI after 72 hours</p>
						<p class="text-gray-400 mb-6">Investment Range</p>
						<p class="text-white text-2xl font-bold mb-6">
							$20,000 – $35,000
						</p>
						<ul class="text-gray-300 space-y-2 mb-8 flex-1">
							<li>✅ Everything in Standard</li>
							<li>🤝 Dedicated Account Manager</li>
							<li>🎓 Monthly Strategy Webinars</li>
						</ul>
						<a
							href="/login.php"
							class="mt-auto inline-block bg-green-400 text-gray-900 font-semibold py-2 rounded-full hover:bg-green-500 transition">
							Sign Up
						</a>
					</div>

					<!-- Premium Plan -->
					<div
						class="bg-gray-800 rounded-2xl shadow-lg transform hover:scale-105 transition p-6 flex flex-col">
						<h3 class="text-xl font-semibold text-green-400 mb-2">
							PREMIUM
						</h3>
						<p class="text-gray-300 mb-4">
							100% ROI after 96 hours
						</p>
						<p class="text-gray-400 mb-6">Investment Range</p>
						<p class="text-white text-2xl font-bold mb-6">
							$35,000+
						</p>
						<ul class="text-gray-300 space-y-2 mb-8 flex-1">
							<li>✅ Everything in Gold</li>
							<li>📞 24/7 VIP Support</li>
							<li>🎯 Personalized Investment Strategy</li>
						</ul>
						<a
							href="/login.php"
							class="mt-auto inline-block bg-green-400 text-gray-900 font-semibold py-2 rounded-full hover:bg-green-500 transition">
							Sign Up
						</a>
					</div>
				</div>
			</div>
		</section>

		<!-- Offer End -->
		<!-- Testimonial End -->
<!-- Footer Start -->
<div
	class="container-fluid footer py-5 wow fadeIn"
	data-wow-delay="0.2s">
	<div
		class="container py-5 border-start-0 border-end-0"
		style="border: 1px solid; border-color: rgb(255, 255, 255, 0.08);">
		<div class="row g-4">
			<div class="col-6 col-md-4">
				<div class="footer-item">
					<h4 class="text-white mb-4">Quick Links</h4>
					<a href="/about.php"><i class="fas fa-angle-right me-2"></i> About Us</a>
					<a href="feature.php"><i class="fas fa-angle-right me-2"></i> Feature</a>
					<a href="service.php"><i class="fas fa-angle-right me-2"></i> Service</a>
					<a href="blog.php"><i class="fas fa-angle-right me-2"></i> Blog</a>
					<a href="contact.php"><i class="fas fa-angle-right me-2"></i> Contact us</a>
				</div>
			</div>

			<div class="col-6 col-md-4">
				<div class="footer-item">
					<h4 class="text-white mb-4">Support</h4>
					<a href="/privacy.php"><i class="fas fa-angle-right me-2"></i> Privacy Policy</a>
					<a href="/contact.php"><i class="fas fa-angle-right me-2"></i> Support</a>
					<a href="/FAQ.php"><i class="fas fa-angle-right me-2"></i> FAQ</a>
				</div>
			</div>

			<div class="col-6 col-md-4">
				<div class="footer-item">
					<h4 class="text-white mb-4">Contact Info</h4>
					<div class="d-flex align-items-center">
						<i class="fas fa-envelope text-primary me-3"></i>
					<a href="mailto:support@benefitsmart.xyz" class="text-white mb-0">support@benefitsmart.xyz</a>
					</div>
					<div class="d-flex align-items-center">
						<i class="fab fa-whatsapp text-primary me-3"></i>
						<a href="https://wa.me/14795270406"" class="text-white mb-0" target="_blank">+1 (479) 527-0406</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Footer End -->

<!-- Copyright Start -->
<div class="container-fluid copyright py-4">
	<div class="container text-center">
		<div class="row g-4 align-items-center justify-content-center">
			<div class="col-md-12">
				<span class="text-body">
					<a href="#" class="border-bottom text-white">
						Copyright <i class="fas fa-copyright text-light"></i>
						2012 - 2026 Benefit Market Trade
					</a>, All rights reserved.
				</span>
			</div>
		</div>
	</div>
</div>


		<!-- Back to Top -->
		<a
			href="#"
			class="btn btn-primary btn-lg-square rounded-circle back-to-top"
			><i class="fa fa-arrow-up"></i
		></a>

		<!-- JavaScript Libraries -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
		<script src="lib/wow/wow.min.js"></script>
		<script src="lib/easing/easing.min.js"></script>
		<script src="lib/waypoints/waypoints.min.js"></script>
		<script src="lib/counterup/counterup.min.js"></script>
		<script src="lib/lightbox/js/lightbox.min.js"></script>
		<script src="lib/owlcarousel/owl.carousel.min.js"></script>

		<script src="js/main.js"></script>
	</body>
</html>
