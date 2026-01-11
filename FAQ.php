<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>FAQ</title>
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
            <!-- nav end -->
            <!-- Header Start -->
            <div class="container-fluid bg-breadcrumb">
                <div class="container text-center py-5" style="max-width: 900px;">
                    <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">FAQs</h4>
                    <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Pages</a></li>
                        <li class="breadcrumb-item active text-primary">FAQ</li>
                    </ol>    
                </div>
            </div>
            <!-- Header End -->
        </div>
        <!-- Navbar & Hero End -->


		<!-- FAQs Start -->
		<div class="container-fluid faq-section pb-5 mt-6">
			<div class="container pb-5 overflow-hidden">
				<div
					class="text-center mx-auto pb-5 wow fadeInUp"
					data-wow-delay="0.2s"
					style="max-width: 800px">
					<h4 class="text-primary">FAQs</h4>
					<h1 class="display-5 mb-4">Frequently Asked Questions</h1>
					<p class="mb-0">
						Everything you need to know about our trading platform,
						investment plans, and advisory services.
					</p>
				</div>
				<div class="row g-5 align-items-center">
					<div class="col-lg-6 wow fadeInLeft" data-wow-delay="0.2s">
						<div
							class="accordion accordion-flush bg-light rounded p-5"
							id="accordionFlushSection">
							<!-- 1 -->
							<div class="accordion-item rounded-top">
								<h2
									class="accordion-header"
									id="flush-headingOne">
									<button
										class="accordion-button collapsed rounded-top"
										type="button"
										data-bs-toggle="collapse"
										data-bs-target="#flush-collapseOne"
										aria-expanded="false"
										aria-controls="flush-collapseOne">
										What services does Benefit Market Trade offer?
									</button>
								</h2>
								<div
									id="flush-collapseOne"
									class="accordion-collapse collapse"
									aria-labelledby="flush-headingOne"
									data-bs-parent="#accordionFlushSection">
									<div class="accordion-body">
										We provide end‑to‑end investment
										solutions in Forex &amp; Crypto trading,
										plus bespoke Financial Advisory. Enjoy
										real‑time analytics, automated
										strategies, and dedicated account
										managers to help you reach your goals.
									</div>
								</div>
							</div>

							<!-- 2 -->
							<div class="accordion-item">
								<h2
									class="accordion-header"
									id="flush-headingTwo">
									<button
										class="accordion-button collapsed"
										type="button"
										data-bs-toggle="collapse"
										data-bs-target="#flush-collapseTwo"
										aria-expanded="false"
										aria-controls="flush-collapseTwo">
										What are the risks of online trading?
									</button>
								</h2>
								<div
									id="flush-collapseTwo"
									class="accordion-collapse collapse"
									aria-labelledby="flush-headingTwo"
									data-bs-parent="#accordionFlushSection">
									<div class="accordion-body">
										Online markets can be volatile.
										Potential risks include rapid price
										swings, liquidity shortages, and
										emotional decision‑making. We mitigate
										these with tested trading bots
										algorithms, portfolio alerts, and expert
										guidance.
									</div>
								</div>
							</div>

							<!-- 3 -->
							<div class="accordion-item">
								<h2
									class="accordion-header"
									id="flush-headingThree">
									<button
										class="accordion-button collapsed"
										type="button"
										data-bs-toggle="collapse"
										data-bs-target="#flush-collapseThree"
										aria-expanded="false"
										aria-controls="flush-collapseThree">
										How secure is my account and data?
									</button>
								</h2>
								<div
									id="flush-collapseThree"
									class="accordion-collapse collapse"
									aria-labelledby="flush-headingThree"
									data-bs-parent="#accordionFlushSection">
									<div class="accordion-body">
										We use bank‑grade encryption, two‑factor
										authentication (2FA), and regular
										security audits to keep your funds and
										personal information fully protected.
									</div>
								</div>
							</div>

							<!-- 4 -->
							<div class="accordion-item">
								<h2
									class="accordion-header"
									id="flush-headingFour">
									<button
										class="accordion-button collapsed"
										type="button"
										data-bs-toggle="collapse"
										data-bs-target="#flush-collapseFour"
										aria-expanded="false"
										aria-controls="flush-collapseFour">
										How do I open a trading account?
									</button>
								</h2>
								<div
									id="flush-collapseFour"
									class="accordion-collapse collapse"
									aria-labelledby="flush-headingFour"
									data-bs-parent="#accordionFlushSection">
									<div class="accordion-body">
										Simply click “Sign Up,” fill in your
										details, complete verification, deposit
										via bank transfer or crypto, and you’re
										live. Most accounts are approved within
										24 hours.
									</div>
								</div>
							</div>

							<!-- 5 -->
							<div class="accordion-item">
								<h2
									class="accordion-header"
									id="flush-headingFive">
									<button
										class="accordion-button collapsed"
										type="button"
										data-bs-toggle="collapse"
										data-bs-target="#flush-collapseFive"
										aria-expanded="false"
										aria-controls="flush-collapseFive">
										What fees and commissions apply?
									</button>
								</h2>
								<div
									id="flush-collapseFive"
									class="accordion-collapse collapse"
									aria-labelledby="flush-headingFive"
									data-bs-parent="#accordionFlushSection">
									<div class="accordion-body">
										We offer tight spreads on all Forex
										&amp; Crypto pairs, plus a transparent
										performance fee on profitable trades.
										All charges are clearly displayed before
										you execute any order.
									</div>
								</div>
							</div>

							<!-- 6 -->
							<div class="accordion-item">
								<h2
									class="accordion-header"
									id="flush-headingSix">
									<button
										class="accordion-button collapsed"
										type="button"
										data-bs-toggle="collapse"
										data-bs-target="#flush-collapseSix"
										aria-expanded="false"
										aria-controls="flush-collapseSix">
										How can I withdraw my funds?
									</button>
								</h2>
								<div
									id="flush-collapseSix"
									class="accordion-collapse collapse"
									aria-labelledby="flush-headingSix"
									data-bs-parent="#accordionFlushSection">
									<div class="accordion-body">
										Withdrawals can be requested anytime via
										your dashboard. Funds are processed
										within 1–3 business days to your bank
										account or crypto wallet which will be
										processed within 30 minutes.
									</div>
								</div>
							</div>

							<!-- 7 -->
							<div class="accordion-item">
								<h2
									class="accordion-header"
									id="flush-headingSeven">
									<button
										class="accordion-button collapsed"
										type="button"
										data-bs-toggle="collapse"
										data-bs-target="#flush-collapseSeven"
										aria-expanded="false"
										aria-controls="flush-collapseSeven">
										I'm having problem with the
										registration/Deposit, What should i do?
									</button>
								</h2>
								<div
									id="flush-collapseSeven"
									class="accordion-collapse collapse"
									aria-labelledby="flush-headingSeven"
									data-bs-parent="#accordionFlushSection">
									<div class="accordion-body">
										If you have problems logging in, or if
										you receive an error message during
										registration or making deposit or withdrawing, please contact
										support@benefitsmart.xyz or use the
										Live Chat feature on this website.
									</div>
								</div>
							</div>

							<!-- 7 -->
							<div class="accordion-item">
								<h2
									class="accordion-header"
									id="flush-headingSeven">
									<button
										class="accordion-button collapsed"
										type="button"
										data-bs-toggle="collapse"
										data-bs-target="#flush-collapseSeven"
										aria-expanded="false"
										aria-controls="flush-collapseSeven">
										Can I upgrade or change my plan later?
									</button>
								</h2>
								<div
									id="flush-collapseSeven"
									class="accordion-collapse collapse"
									aria-labelledby="flush-headingSeven"
									data-bs-parent="#accordionFlushSection">
									<div class="accordion-body">
										Yes—upgrade or switch plans at any time.
										Our system prorates your remaining term
										so you only pay the difference.
									</div>
								</div>
							</div>

							<!-- 8 -->
							<div class="accordion-item rounded-bottom">
								<h2
									class="accordion-header"
									id="flush-headingEight">
									<button
										class="accordion-button collapsed rounded-bottom"
										type="button"
										data-bs-toggle="collapse"
										data-bs-target="#flush-collapseEight"
										aria-expanded="false"
										aria-controls="flush-collapseEight">
										Which devices are supported?
									</button>
								</h2>
								<div
									id="flush-collapseEight"
									class="accordion-collapse collapse"
									aria-labelledby="flush-headingEight"
									data-bs-parent="#accordionFlushSection">
									<div class="accordion-body">
										Our platform is fully responsive on
										desktop web, iOS and Android apps. All
										your data syncs in real time across
										devices.
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6 wow fadeInRight" data-wow-delay="0.2s">
						<div class="bg-primary rounded">
							<img
								src="img/faq.jpg"
								class="img-fluid w-100 rounded-2xl"
								alt="FAQ Illustration" />
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- FAQs End -->
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
						2012 - 2025 Benefit Market Trade
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
