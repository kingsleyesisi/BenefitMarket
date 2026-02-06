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
							<a href="index.php" class="nav-item nav-link "
								>Home</a
							>
							<a href="about.php" class="nav-item nav-link">About</a>
							<a href="service.php" class="nav-item nav-link active"
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
									<a href="offer.php" class="dropdown-item"
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
							class="text-white display-4 mb-6 wow fadeInDown"
							data-wow-delay="0.1s">
							Our Services
						</h4>
						<ol
							class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown"
							data-wow-delay="0.3s">
							<li class="breadcrumb-item">
								<a href="index.php">Home</a>
							</li>
							<li class="breadcrumb-item"><a href="#">Pages</a></li>
							<li class="breadcrumb-item active text-primary">
								Service
							</li>
						</ol>
					</div>
				</div>
				<!-- Header End -->
			</div>
			<!-- Navbar & Hero End -->

			<!-- Services Start -->
			<div class="container-fluid service pb-5">
				<div class="container pb-5">
					<div
						class="text-center mx-auto pb-5 wow fadeInUp"
						data-wow-delay="0.2s"
						style="max-width: 800px">
						<h4 class="text-primary">Our Services</h4>
						<h1 class="display-5 mb-4">
							Our Services Provide the Best Offers
						</h1>
						<p class="mb-0">
							We offer a comprehensive range of services including
							investment strategies, financial management,
							cryptocurrency trading, forex trading, and expert
							consulting. Our goal is to empower clients with tailored
							solutions, cutting-edge tools, and professional guidance
							to achieve financial success and sustainable growth.
						</p>
					</div>
					<div class="row g-4">
						<div
							class="col-md-6 col-lg-4 wow fadeInUp"
							data-wow-delay="0.2s">
							<div class="service-item">
								<div class="service-img">
									<img
										src="img/investment.jpg"
										class="img-fluid rounded-top w-100"
										style="height: 270px"
										alt="Image" />
								</div>
								<div class="rounded-bottom p-4">
									<a href="#" class="h4 d-inline-block mb-4"
										>Investment Strategies</a
									>
									<p class="mb-4">
										With a team of experts, advanced tools, and
										comprehensive market analysis, we aim to
										optimize your returns while minimizing
										risks. Whether you're an experienced
										investor or a beginner, we provide guidance
										at every stage of your journey.
									</p>
									<a
										class="btn btn-primary rounded-pill py-2 px-4"
										href="#"
										>Learn More</a
									>
								</div>
							</div>
						</div>
						<div
							class="col-md-6 col-lg-4 wow fadeInUp"
							data-wow-delay="0.4s">
							<div class="service-item">
								<div class="service-img">
									<img
										src="img/crypto.jpg"
										class="img-fluid rounded-top w-100"
										style="height: 270px"
										alt="Crypto Services Image" />
								</div>
								<div class="rounded-bottom p-4">
									<a href="#" class="h4 d-inline-block mb-4"
										>Crypto Services</a
									>
									<p class="mb-4">
										We provide secure and reliable
										cryptocurrency services, including trading,
										investment, and portfolio management. Our
										platform ensures seamless transactions and
										expert guidance to help you navigate the
										world of digital assets with confidence.
									</p>
									<a
										class="btn btn-primary rounded-pill py-2 px-4"
										href="#"
										>Learn More</a
									>
								</div>
							</div>
						</div>
						<div
							class="col-md-6 col-lg-4 wow fadeInUp"
							data-wow-delay="0.6s">
							<div class="service-item">
								<div class="service-img">
									<img
										src="img/forex.jpg"
										class="img-fluid rounded-top w-100"
										style="height: 270px"
										alt="Image" />
								</div>
								<div class="rounded-bottom p-4">
									<a href="#" class="h4 d-inline-block mb-4"
										>Forex Services</a
									>
									<p class="mb-4">
										We offer automated trading solutions that
										leverage advanced algorithms and AI to
										trades on your behalf. It analyze market
										trends, optimize strategies, ensuring
										efficient trading, giving you with a
										hands-free approach to maximize returns.
									</p>
									<a
										class="btn btn-primary rounded-pill py-2 px-4"
										href="#"
										>Learn Moe</a
									>
								</div>
							</div>
						</div>
						<div
							class="col-md-6 col-lg-4 wow fadeInUp"
							data-wow-delay="0.2s">
							<div class="service-item">
								<div class="service-img">
									<img
										src="img/secure.jpg"
										class="img-fluid rounded-top w-100"
										style="height: 270px"
										alt="Image" />
								</div>
								<div class="rounded-bottom p-4">
									<a href="#" class="h4 d-inline-block mb-4"
										>Financial Security</a
									>
									<p class="mb-4">
										Our platform is built with state-of-the-art
										security measures ensuring the safety of
										your investments and personal data. We
										utilize advanced encryption and continuous
										monitoring to protect against unauthorized
										access and cyber threats. Your trust is our
										priority.
									</p>
									<a
										class="btn btn-primary rounded-pill py-2 px-4"
										href="#"
										>Learn More</a
									>
								</div>
							</div>
						</div>
						<div
							class="col-md-6 col-lg-4 wow fadeInUp"
							data-wow-delay="0.4s">
							<div class="service-item">
								<div class="service-img">
									<img
										src="img/service-5.jpg"
										class="img-fluid rounded-top w-100"
										alt="Image" />
								</div>
								<div class="rounded-bottom p-4">
									<a href="#" class="h4 d-inline-block mb-4"
										>Financial Advisory</a
									>
									<p class="mb-4">
										Our financial advisory services offer
										professional insights and tailored guidance
										to help you reach your financial
										aspirations. From strategic investment
										planning and risk mitigation to
										comprehensive financial advice, our
										dedicated team ensures customized solutions
										for sustainable success.
									</p>
									<a
										class="btn btn-primary rounded-pill py-2 px-4"
										href="#"
										>Learn More</a
									>
								</div>
							</div>
						</div>
						<div
							class="col-md-6 col-lg-4 wow fadeInUp"
							data-wow-delay="0.6s">
							<div class="service-item">
								<div class="service-img">
									<img
										src="img/service-6.jpg"
										class="img-fluid rounded-top w-100"
										alt="Image" />
								</div>
								<div class="rounded-bottom p-4">
									<a href="#" class="h4 d-inline-block mb-4"
										>Marketing Consulting</a
									>
									<p class="mb-4">
										We provide businesses with expert guidance
										to enhance their marketing strategies. By
										leveraging innovative approaches, and
										data-driven insights, marketing consulting
										helps organizations achieve sustainable
										growth and stay competitive in an
										ever-evolving market landscape.
									</p>
									<a
										class="btn btn-primary rounded-pill py-2 px-4"
										href="#"
										>Learn More</a
									>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Services End -->

			<!-- Testimonial Start -->
			<div class="container-fluid testimonial pb-5">
				<div class="container pb-5">
					<div
						class="text-center mx-auto pb-5 wow fadeInUp"
						data-wow-delay="0.2s"
						style="max-width: 800px">
						<h4 class="text-primary">Testimonial</h4>
						<h1 class="display-5 mb-4">Our Clients Reviews</h1>
						<p class="mb-0">
							See how our platform has transformed portfolios across
							the globe—through Forex, Crypto trading and bespoke
							financial advisory.
						</p>
					</div>
					<div
						class="owl-carousel testimonial-carousel wow fadeInUp"
						data-wow-delay="0.2s">
						<!-- Forex Review -->
						<div class="testimonial-item">
							<div class="testimonial-quote-left">
								<i class="fas fa-quote-left fa-2x"></i>
							</div>
							<div class="testimonial-img">
								<img
									src="img/romania woman.jpg"
									class="img-fluid"
									alt="John Doe" />
							</div>
							<div class="testimonial-text">
								<p class="mb-0">
									"Since joining Benefit Market Trade, my Forex portfolio
									has soared by 30% in just two weeks. The
									advanced charting tools and risk‑management
									alerts are game‑changers!"
								</p>
							</div>
							<div class="testimonial-title">
								<div>
									<h4 class="mb-0">Liliana Dobling</h4>
									<p class="mb-0">Forex Trader, Romania</p>
								</div>
								<div class="d-flex text-primary">
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="far fa-star"></i>
								</div>
							</div>
							<div class="testimonial-quote-right">
								<i class="fas fa-quote-right fa-2x"></i>
							</div>
						</div>

						<!-- Crypto Review -->
						<div class="testimonial-item">
							<div class="testimonial-quote-left">
								<i class="fas fa-quote-left fa-2x"></i>
							</div>
							<div class="testimonial-img">
								<img
									src="img/testimonial-2.jpg"
									class="img-fluid"
									alt="Maria González" />
							</div>
							<div class="testimonial-text">
								<p class="mb-0">
									"Crypto investments were daunting—until I tried
									Benefit Market Trade. Their market signals and 24/7 chat
									support helped me grow my holdings by 45% in
									under a month."
								</p>
							</div>
							<div class="testimonial-title">
								<div>
									<h4 class="mb-0">Sofia Michealson</h4>
									<p class="mb-0">Entrepreneur, USA</p>
								</div>
								<div class="d-flex text-primary">
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="far fa-star"></i>
									<i class="far fa-star"></i>
								</div>
							</div>
							<div class="testimonial-quote-right">
								<i class="fas fa-quote-right fa-2x"></i>
							</div>
						</div>

						<!-- Combined Forex & Crypto Review -->
						<div class="testimonial-item">
							<div class="testimonial-quote-left">
								<i class="fas fa-quote-left fa-2x"></i>
							</div>
							<div class="testimonial-img">
								<img
									src="img/Untitled.jpeg"
									class="img-fluid"
									alt="roberto temonale" />
							</div>
							<div class="testimonial-text">
								<p class="mb-0">
									"I diversified across Forex and Crypto using
									this Platform Trades—total return hit 60% in two
									months. Their auto‑rebalancing feature is
									brilliant for busy investors!"
								</p>
							</div>
							<div class="testimonial-title">
								<div>
									<h4 class="mb-0">Roberto temonale</h4>
									<p class="mb-0">Content Creator, Italy</p>
								</div>
								<div class="d-flex text-primary">
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="far fa-star"></i>
								</div>
							</div>
							<div class="testimonial-quote-right">
								<i class="fas fa-quote-right fa-2x"></i>
							</div>
						</div>

						<!-- Financial Advisory Review -->
						<div class="testimonial-item">
							<div class="testimonial-quote-left">
								<i class="fas fa-quote-left fa-2x"></i>
							</div>
							<div class="testimonial-img">
								<img
									src="img/kira.png"
									class="img-fluid"
									alt="Kira Forex " />
							</div>
							<div class="testimonial-text">
								<p class="mb-0">
									"Their Financial Advisory team crafted a
									personalized strategy that doubled my annual
									returns. I couldn’t ask for better guidance or
									transparency."
								</p>
							</div>
							<div class="testimonial-title">
								<div>
									<h4 class="mb-0">Lamidi Sikira</h4>
									<p class="mb-0">Business Owner, UAE</p>
								</div>
								<div class="d-flex text-primary">
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
								</div>
							</div>
							<div class="testimonial-quote-right">
								<i class="fas fa-quote-right fa-2x"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
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
