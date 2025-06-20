<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>contact</title>
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
						<a href="index.php" class="nav-item nav-link active"
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
                    <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Contact Us</h4>
                    <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Pages</a></li>
                        <li class="breadcrumb-item active text-primary">Contact</li>
                    </ol>    
                </div>
            </div>
            <!-- Header End -->
        </div>
        <!-- Navbar & Hero End -->

        <!-- Contact Start -->
        <div class="container-fluid contact py-5">
            <div class="container py-5">
                <div class="row g-5">
                    <div class="col-xl-6">
                        <div class="wow fadeInUp" data-wow-delay="0.2s">
                            <div class="bg-light rounded p-5 mb-5">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                       
                                    </div>
                                    <div class="col-md-6">
                                        <div class="contact-add-item">
                                            <div class="contact-icon text-primary mb-4">
                                                <i class="fas fa-envelope fa-2x"></i>
                                            </div>
                                            <div>
                                                <h4>Mail Us</h4>
                                                <a href="mailto:support@nextrade.online" class="text-dark text-decoration-none">
                                                    <p class="mb-0">support@nextrade.online</p>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="contact-add-item">
                                         
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="contact-add-item">
                                            <div class="contact-icon text-primary mb-4">
                                                <i class="fab fa-whatsapp fa-2x"></i>
                                            </div>
                                            <div>
                                                <a href="https://wa.me/13157195192" target="_blank" class="text-decoration-none text-dark">
                                                    <p class="mb-0">+386 40 824 856</p>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-light p-5 rounded h-100 wow fadeInUp" data-wow-delay="0.2s">
                                <h4 class="text-primary">Send Your Message</h4>
                                <p class="mb-4">Feel free to reach out to us with any questions or inquiries. We are here to help and will get back to you as soon as possible.</p>
                                <form>
                                    <div class="row g-4">
                                        <div class="col-lg-12 col-xl-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control border-0" id="name" placeholder="Your Name">
                                                <label for="name">Your Name</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-xl-6">
                                            <div class="form-floating">
                                                <input type="email" class="form-control border-0" id="email" placeholder="Your Email">
                                                <label for="email">Your Email</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-xl-6">
                                            <div class="form-floating">
                                                <input type="phone" class="form-control border-0" id="phone" placeholder="Phone">
                                                <label for="phone">Your Phone</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-xl-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control border-0" id="project" placeholder="Project">
                                                <label for="project">Your Project</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="text" class="form-control border-0" id="subject" placeholder="Subject">
                                                <label for="subject">Subject</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <textarea class="form-control border-0" placeholder="Leave a message here" id="message" style="height: 160px"></textarea>
                                                <label for="message">Message</label>
                                            </div>

                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary w-100 py-3">Send Message</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 wow fadeInRight" data-wow-delay="0.2s">
                        <div class="rounded h-100">
                            <iframe class="rounded h-100 w-100" 
                            style="height: 400px;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387191.33750346623!2d-73.97968099999999!3d40.6974881!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1694259649153!5m2!1sen!2sbd" 
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contact End -->
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
					<a href="mailto:support@nextrade.online" class="text-white mb-0">support@nextrade.online</a>
					</div>
					<div class="d-flex align-items-center">
						<i class="fab fa-whatsapp text-primary me-3"></i>
						<a href="https://wa.me/13157195192" class="text-white mb-0" target="_blank">+386 40 824 856</a>
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
