<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>About </title>
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
				<a href="/" class="flex items-center text-primary">
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
						<a href="/" class="nav-item nav-link "
							>Home</a
						>
						<a href="about.php" class="nav-item nav-link active">About</a>
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
			<!-- Header Start -->
			<div class="container-fluid bg-breadcrumb">
				<div
					class="container text-center py-5"
					style="max-width: 900px">
					<h4
						class="text-white display-4 mb-4 wow fadeInDown"
						data-wow-delay="0.1s">
						About Us
					</h4>
					<ol
						class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown"
						data-wow-delay="0.3s">
						<li class="breadcrumb-item">
							<a href="index.php">Home</a>
						</li>
						<li class="breadcrumb-item"><a href="#">Pages</a></li>
						<li class="breadcrumb-item active text-primary">
							About
						</li>
					</ol>
				</div>
			</div>
			<!-- Header End -->
		</div>
		<!-- Navbar & Hero End -->

		<!-- About Start -->
		<div class="container-fluid about py-5">
			<div class="container py-5">
				<div class="row g-5 align-items-center">
					<div class="col-xl-7 wow fadeInLeft" data-wow-delay="0.2s">
						<div>
							<h4 class="text-primary">About Us</h4>
							<h1 class="display-5 mb-4">
								Meet our company unless miss the opportunity
							</h1>
							<p class="mb-4">
								Our company is a leading crypto investment and
								financial management firm dedicated to
								empowering individuals and businesses to achieve
								their financial goals. With a focus on
								innovation, transparency, and expertise, we
								provide cutting-edge solutions for
								cryptocurrency investments and comprehensive
								financial strategies. Our team of seasoned
								professionals is committed to delivering
								personalized services, ensuring secure and
								profitable investment opportunities. Whether
								you're a seasoned investor or new to the
								financial world, we are here to guide you every
								step of the way, helping you build a prosperous
								and sustainable financial future.
							</p>
							<div class="row g-4">
								<div class="col-md-6 col-lg-6 col-xl-6">
									<div class="d-flex">
										<div>
											<i
												class="fas fa-lightbulb fa-3x text-primary"></i>
										</div>
										<div class="ms-4">
											<h4>Business Consuluting</h4>
											<p>
												Business consulting provides
												expert guidance to enhance
												organizational performance,
												optimize operations, and drive
												sustainable growth through
												strategic planning and market
												insights.
											</p>
										</div>
									</div>
								</div>
								<div class="col-md-6 col-lg-6 col-xl-6">
									<div class="d-flex">
										<div>
											<i
												class="bi bi-bookmark-heart-fill fa-3x text-primary"></i>
										</div>
										<div class="ms-4">
											<h4>Year Of Expertise</h4>
											<p>
												With over a decade of expertise
												in the financial and investment
												industry, we have consistently
												delivered innovative solutions
												and exceptional results. Our
												experience spans across diverse
												markets, enabling us to provide
												tailored strategies and trusted
												guidance to our clients.
											</p>
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<a
										href="/service.php"
										class="btn btn-primary rounded-pill py-3 px-5 flex-shrink-0"
										>Discover Now</a
									>
								</div>
								<div class="col-sm-6">
									<div class="d-flex">
										<i
											class="fas fa-envelope fa-2x text-primary me-4"></i>
										<div>
											<h4>Email Us</h4>
											<p
												class="mb-0 fs-5"
												style="letter-spacing: 1px">
												support@benefitsmart.xyz
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-5 wow fadeInRight" data-wow-delay="0.2s">
						<div
							class="bg-primary rounded position-relative overflow-hidden">
							<img
								src="img/about-2.png"
								class="img-fluid rounded w-100"
								alt="" />

							<div
								class=""
								style="
									position: absolute;
									top: -15px;
									right: -15px;
								">
								<img
									src="img/about-3.png"
									class="img-fluid"
									style="
										width: 150px;
										height: 150px;
										opacity: 0.7;
									"
									alt="" />
							</div>
							<div
								class=""
								style="
									position: absolute;
									top: -20px;
									left: 10px;
									transform: rotate(90deg);
								">
								<img
									src="img/about-4.png"
									class="img-fluid"
									style="
										width: 100px;
										height: 150px;
										opacity: 0.9;
									"
									alt="" />
							</div>
							<div class="rounded-bottom">
								<img
									src="img/about-5.jpg"
									class="img-fluid rounded-bottom w-100"
									alt="" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- About End -->

		<!-- Features Start -->
		<div class="container-fluid feature pb-5">
			<div class="container pb-5">
				<div
					class="text-center mx-auto pb-5 wow fadeInUp"
					data-wow-delay="0.2s"
					style="max-width: 800px">
					<h4 class="text-primary">Our Features</h4>
					<h1 class="display-5 mb-4">
						Empowering Financial Success Through Innovation and
						Expertise.
					</h1>
					<p class="mb-0">
						Our investment platform offers a range of innovative
						features designed to empower users and enhance their
						financial journey. These include advanced analytics
						tools for informed decision-making, secure and seamless
						transactions, personalized investment strategies
						tailored to individual goals, and 24/7 customer support
						to address any queries. Additionally, our platform
						integrates cutting-edge technology to ensure data
						security and provides access to a diverse portfolio of
						investment opportunities, including stocks,
						cryptocurrencies, and forex trading. Join us to
						experience a smarter, more efficient way to grow your
						wealth.
					</p>
				</div>
				<div class="row g-4">
					<div
						class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp"
						data-wow-delay="0.2s">
						<div class="feature-item p-4">
							<div class="feature-icon p-4 mb-4">
								<i
									class="fas fa-chart-line fa-4x text-primary"></i>
							</div>
							<h4>Global Management</h4>
							<p class="mb-4">
								We offer global management services, ensuring
								seamless operations, strategic planning, and
								expert guidance for businesses worldwide to
								thrive.
							</p>
							<a
								class="btn btn-primary rounded-pill py-2 px-4"
								href="#"
								>Learn More</a
							>
						</div>
					</div>
					<div
						class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp"
						data-wow-delay="0.4s">
						<div class="feature-item p-4">
							<div class="feature-icon p-4 mb-4">
								<i
									class="fas fa-university fa-4x text-primary"></i>
							</div>
							<h4>Cost Efficiency</h4>
							<p class="mb-4">
								Our platform ensures cost efficiency by
								optimizing resources, reducing expenses, and
								delivering maximum value for your investments.
							</p>
							<a
								class="btn btn-primary rounded-pill py-2 px-4"
								href="#"
								>Learn More</a
							>
						</div>
					</div>
					<div
						class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp"
						data-wow-delay="0.6s">
						<div class="feature-item p-4">
							<div class="feature-icon p-4 mb-4">
								<i
									class="fas fa-users-cog fa-4x text-primary"></i>
							</div>
							<h4>User-Friendly Platform</h4>
							<p class="mb-4">
								Our platform ensures simplicity, offering an
								intuitive experience for users to achieve
								financial goals effortlessly with a easy to use
								interface.
							</p>
							<a
								class="btn btn-primary rounded-pill py-2 px-4"
								href="#"
								>Learn More</a
							>
						</div>
					</div>
					<div
						class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp"
						data-wow-delay="0.8s">
						<div class="feature-item p-4">
							<div class="feature-icon p-4 mb-4">
								<i
									class="fas fa-headset fa-4x text-primary"></i>
							</div>
							<h4>24/7 Support</h4>
							<p class="mb-4">
								Our dedicated support team is available 24/7 to
								assist you with any queries or issues, ensuring
								a seamless experience at all times.
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
		<!-- Features End -->
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
						<a href="https://wa.me/14795270406" class="text-white mb-0" target="_blank">+1 (479) 527-0406 </a>
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
		<!-- Copyright End -->

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
