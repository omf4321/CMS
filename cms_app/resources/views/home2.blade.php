<!DOCTYPE html>
<html class="no-js" lang="zxx">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>SOHOPATH | Read to understand</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<!-- Favicons -->
	<link rel="shortcut icon" href="images/favicon.ico">
	<link rel="apple-touch-icon" href="images/icon.png">
	<!-- Google font (font-family: 'Dosis', Roboto;) -->
	<link href="https://fonts.googleapis.com/css?family=Dosis:400,500,600,700" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet">
	 

	<!-- Stylesheets -->
	<link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('css/plugins.css') }}">
	<link rel="stylesheet" href="{{ asset('css/style_home.css?'.time()) }}">

	<!-- Cusom css -->
   <link rel="stylesheet" href="{{ asset('css/util.css?'.time()) }}">

	<!-- Modernizer js -->
	<script src="{{ asset('js/modernizr-3.5.0.min.js') }}"></script>
</head>
<body>
	<!--[if lte IE 9]>
		<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
	<![endif]-->

	<!-- Add your site or application content here -->
	
	<!-- <div class="fakeloader"></div> -->

	<!-- Main wrapper -->
	<div class="wrapper" id="wrapper">
		<!-- Header -->
		<header id="header" class="jnr__header header--one clearfix">
			<!-- Start Header Top Area -->
			<div class="junior__header__top">
				<div class="container">
					<div class="row">
						<div class="col-md-7 col-lg-6 col-6 p-r-5">
							<div class="jun__header__top__left">
								<ul class="top__address d-flex justify-content-start align-items-center flex-wrap flex-lg-nowrap flex-md-nowrap">
									<li class="d-none d-sm-block m-r-10"><i class="fa fa-envelope"></i><a>sohopath.co@gmail.com</a></li>
									<li class="m-l-0"><i class="fa fa-phone"></i><span class="fc-white m-r-5"></span><a>+8801778021126</a></li>
								</ul>
							</div>
						</div>
						<div class="col-md-5 col-lg-6 col-6 p-l-5 p-r-5">
							<div class="jun__header__top__right" style="position: relative; top: 5px;">
								<ul class="accounting d-flex justify-content-lg-end justify-content-md-end justify-content-end align-items-center">
									@if (Auth::guard('web')->check())
										<li class="m-r-0"><a class="fc-white" href="/user/home"><i class="fa fa-home fs-20"></i></a></li>
										<li class="m-r-10">
											<a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
	                                            <i class="fa fa-sign-out fs-20 fc-white"></i>
	                                        </a>
	                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
	                                            {{ csrf_field() }}
	                                        </form>
										</li>
									@else
										<li class="m-r-5"><a class="fc-white" href="{{ route('client.login') }}">Student</a></li>
										<li class="m-r-10"><a class="fc-white" href="/login">Teacher</a></li>
										{{-- class of <a> login-trigger for js --}}
									@endif
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Header Top Area -->
			<!-- Start Mainmenu Area -->
			<div class="mainmenu__wrapper bg__cat--1 poss-relative header_top_line sticky__header">
				<div class="container">
					<div class="row d-none d-lg-flex">
						<div class="col-sm-4 col-md-6 col-lg-2 order-1 order-lg-1">
							<div class="logo">
								<a href="/">
									<img src="{{ asset('image/logo.png') }}" alt="logo images">
								</a>
							</div>
						</div>
						<div class="col-sm-4 col-md-2 col-lg-9 order-3 order-lg-2">
							<div class="mainmenu__wrap">
								<nav class="mainmenu__nav">
                                    <ul class="mainmenu">
                                        <li class="drop"><a href="#about_us">About Us</a></li>
                                        <li class="drop"><a href="#services">Services</a></li>
                                        <li class="drop"><a href="#courses">Courses</a></li>
                                        <li class="drop"><a href="#gallery">Gallery</a></li>
                                        <li><a href="#contact">Contact</a></li>
                                    </ul>
                                </nav>
							</div>
						</div>
					</div>
					<!-- Mobile Menu -->
                    <div class="mobile-menu d-block d-lg-none">
                    	<div class="logo">
                    		<a href="/"><img src="{{ asset('image/logo.png') }}" alt="logo" style="width: 120px;"></a>
                    	</div>
                    </div>
                    <!-- Mobile Menu -->
				</div>
			</div>
			<!-- End Mainmenu Area -->
		</header>
		<!-- //Header -->
		<!-- Strat Slider Area -->
		<div class="slide__carosel owl-carousel owl-theme">
			<div class="slider__area bg-slide-pngimage--1  d-flex fullscreen justify-content-start align-items-center">
				<div class="container">
					<div class="row">
						<div class="col-md-12 col-lg-12 col-sm-12">
							<div class="slider__activation">
								<!-- Start Single Slide -->
								<div class="slide">
									<div class="slide__inner">
										<h1 class="hind">বুঝে পড়, মনের আকাশে</h1>
										<div class="slider__text">
											<h2 class="hind">ইচ্ছে মতন উড়ো</h2>
										</div>
									</div>
								</div>
								<!-- End Single Slide -->
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="slider__area bg-slide-pngimage--2  d-flex fullscreen justify-content-start align-items-center">
				<div class="container">
					<div class="row">
						<div class="col-md-12 col-lg-12 col-sm-12">
							<div class="slider__activation">
								<!-- Start Single Slide -->
								<div class="slide">
									<div class="slide__inner">
										<h1 class="hind">বুঝে পড়, মনের আকাশে</h1>
										<div class="slider__text">
											<h2 class="hind">ইচ্ছে মতন উড়ো</h2>
										</div>
									</div>
								</div>
								<!-- End Single Slide -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Slider Area -->

		<!-- Start Welcame Area -->
		<section class="junior__welcome__area section-padding--md bg-pngimage--2" id="about_us">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section__title text-center solaiman">
							<h2 class="title__line">About Us</h2>
							<p class="fs-20">পরীক্ষায় পাশ করার এরূপ হাস্যেদ্দীপক উন্মত্ততা পৃথিবীর আর কোথাও নেই। পাশ করে বিদ্যার নিকট চিরবিদায় গ্রহণ করার শিক্ষিতের এরূপ জঘন্য প্রকৃতিও আর কোন দেশে নেই। যে নিয়মটা চলে আসছে সেটা প্রথা। মানুষের জীবন এ প্রথার চতুর্সীমার মধ্যে আবদ্ধ। একে এড়িয়ে চলা আমাদের পক্ষে সাধ্যাতীত। সহপাঠ জড়ো হয়েছে শিক্ষার্থীদের নিয়ে নতুন কিছু করার স্বপ্ন নিয়ে। তাই সহপাঠের স্লোগান হলো-</p>
							<p class="fs-20 p-b-10 p-t-10 fw-600">“বুঝে পড় মনের আকাশে ইচ্ছে মত উড়ো”।</p>
							<p class="fs-20">আমরা বিশ্বাস করি কোন কিছু বুঝে পড়ার ক্ষমতা আমাদের দেয় নির্মল আনন্দ। যে কোন কিছু বুঝে পড়ার প্রবণতা যখন একজন শিক্ষার্থীর মধ্যে তৈরি হবে তখন থেকে তার মন থেকে মুখস্ত নামক ‘বিষ’ দূর হবে। ছোটবেলা থেকে আমরা যখন অ, আ, ক, খ,……….A, B, C…….. পড়তে শিখি, তখন থেকে আমাদের পিতামাতারা আমাদের মুখস্ত করার প্রবণতা মাথায় ঢুকিয়ে দেয়। যে কারণে পরবর্তীতে মুখস্ত করা ছাড়া কোন কিছু পড়া হয় না।</p>
							<p class="fs-20">সময় এসেছে প্রথা দূর করার, সময় এসেছে মুখস্তকে চিরবিদায় জানানোর।</p>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- End Welcame Area -->

		<!-- Start Our Service Area -->
		<section class="junior__service bg-image--1 section-padding--bottom section--padding--xlg--top" id="services">
			<div class="container">
				<div class="row">
					<!-- Start Single Service -->
					<div class="col-lg-3 col-md-6 col-sm-6 col-12">
						<div class="service bg--white border__color wow fadeInUp">
							<div class="service__icon">
								<img src="{{ asset('image/attendence.gif') }}" height="70px" alt="icon images">
							</div>
							<div class="service__details">
								<h6><a href="service.html">Attendence Sytem</a></h6>
								<p>We have a smooth attendence system that maintain attendence record of every student.</p>
								
							</div>
						</div>
					</div>
					<!-- End Single Service -->
					<!-- Start Single Service -->
					<div class="col-lg-3 col-md-6 col-sm-6 col-12 xs-mt-60">
						<div class="service bg--white border__color border__color--2 wow fadeInUp" data-wow-delay="0.2s">
							<div class="service__icon">
								<img src="{{ asset('image/att_sms.png') }}" height="70px;" alt="icon images">
							</div>
							<div class="service__details">
								<h6><a href="service.html">Attendence SMS</a></h6>
								<p>Guardian can check student's attendence. We also send attendence sms to confirm them.</p>
								
							</div>
						</div>
					</div>
					<!-- End Single Service -->
					<!-- Start Single Service -->
					<div class="col-lg-3 col-md-6 col-sm-6 col-12 md-mt-60 sm-mt-60">
						<div class="service bg--white border__color border__color--3 wow fadeInUp" data-wow-delay="0.45s">
							<div class="service__icon">
								<img src="{{ asset('image/exam.png') }}" height="60px;" alt="icon images">
							</div>
							<div class="service__details">
								<h6><a href="service.html">Exam</a></h6>
								<p>We take exams regualarly. Retrun exam paper within a week.</p>
								
							</div>
						</div>
					</div>
					<!-- End Single Service -->
					<!-- Start Single Service -->
					<div class="col-lg-3 col-md-6 col-sm-6 col-12 md-mt-60 sm-mt-60">
						<div class="service bg--white border__color border__color--4 wow fadeInUp" data-wow-delay="0.65s">
							<div class="service__icon">
								<img src="{{ asset('image/exam_sms.png') }}" height="60px;" alt="icon images">
							</div>
							<div class="service__details">
								<h6><a href="service.html">Exam SMS</a></h6>
								<p>Guardian can check exam marks. We also send exam marks via sms.</p>
								
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12 md-mt-60 sm-mt-60 m-t-60">
						<div class="service bg--white border__color border__color--1 wow fadeInUp" data-wow-delay="0.65s">
							<div class="service__icon">
								<img src="{{ asset('image/teacher.gif') }}" height="45px;" alt="icon images">
							</div>
							<div class="service__details">
								<h6><a href="service.html">Quality Teacher</a></h6>
								<p>We provide the professional, punctual and quality teacher in every class </p>
								
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12 md-mt-60 sm-mt-60 m-t-60">
						<div class="service bg--white border__color border__color--2 wow fadeInUp" data-wow-delay="0.65s">
							<div class="service__icon">
								<img src="{{ asset('image/prize.png') }}" height="60px;" alt="icon images">
							</div>
							<div class="service__details">
								<h6><a href="service.html">Prizing</a></h6>
								<p>We beleive in inspiring student. We prize for every exam we take.</p>
								
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12 md-mt-60 sm-mt-60 m-t-60">
						<div class="service bg--white border__color border__color--3 wow fadeInUp" data-wow-delay="0.65s">
							<div class="service__icon">
								<img src="{{ asset('image/take_care.png') }}" height="60px;" alt="icon images">
							</div>
							<div class="service__details">
								<h6><a href="service.html">Take Care</a></h6>
								<p>We take care of each student, communicate with guardians, provide extra care for week student and many more.</p>
								
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6 col-12 md-mt-60 sm-mt-60 m-t-60">
						<div class="service bg--white border__color border__color--4 wow fadeInUp" data-wow-delay="0.65s">
							<div class="service__icon">
								<img src="{{ asset('image/software.png') }}" height="60px;" alt="icon images">
							</div>
							<div class="service__details">
								<h6><a href="service.html">Software</a></h6>
								<p>We maintain software for keeping every record of a student. We can provide any information at any time.</p>
								
							</div>
						</div>
					</div>
					<!-- End Single Service -->
				</div>
			</div>
		</section>
		<!-- End Our Service Area -->

		<!-- Start our Class Area -->
		<section class="junior__classes__area section-lg-padding--top section-padding--md--bottom bg--white" id="courses">
			<div class="container">
				<div class="row">
					<div class="col-md-12 col-lg-12 col-sm-12">
						<div class="section__title text-center">
							<h2 class="title__line">Our Courses</h2>
							<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunte magna aliquaet, consectetempora incidunt</p>
						</div>
					</div>
				</div>
				<div class="row classes__wrap activation__one owl-carousel owl-theme clearfix mt--40">
					<!-- Start Single Classes -->
					<div class="col-lg-4 col-sm-6">
						<div class="junior__classes">
							<div class="classes__thumb p-t-20">
								<a href="class-details.html">
									<img src="{{ asset('image/regular.png') }}" alt="class images" style="width: 80px!important">
								</a>
							</div>
							<div class="classes__inner">
								<div class="class__details p-t-20">
									<h4><a href="class-details.html">Regular Course</a></h4>
									<ul class="class__time">
										<li>Class Five to Ten</li>
										<li>January to July, August to December</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<!-- End Single Classes -->
					<!-- Start Single Classes -->
					<div class="col-lg-4 col-sm-6">
						<div class="junior__classes">
							<div class="classes__thumb p-t-20">
								<a href="class-details.html">
									<img src="{{ asset('image/special.png') }}" alt="class images" style="width: 80px!important">
								</a>
							</div>
							<div class="classes__inner">
								<div class="class__details p-t-20">
									<h4><a href="class-details.html">SSC Special Course</a></h4>
									<ul class="class__time">
										<li>Class Ten</li>
										<li>Novermber to January</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<!-- End Single Classes -->
					<!-- Start Single Classes -->
					<div class="col-lg-4 col-sm-6">
						<div class="junior__classes">
							<div class="classes__thumb p-t-20">
								<a href="class-details.html">
									<img src="{{ asset('image/test_care.png') }}" alt="class images" style="width: 80px!important">
								</a>
							</div>
							<div class="classes__inner">
								<div class="class__details p-t-20">
									<h4><a href="class-details.html">SSC Test Care</a></h4>
									<ul class="class__time">
										<li>Class Ten</li>
										<li>July to October</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<!-- End Single Classes -->
					<!-- Start Single Classes -->
					<div class="col-lg-4 col-sm-6">
						<div class="junior__classes">
							<div class="classes__thumb p-t-20">
								<a href="class-details.html">
									<img src="{{ asset('image/special_1.png') }}" alt="class images" style="width: 80px!important">
								</a>
							</div>
							<div class="classes__inner">
								<div class="class__details p-t-20">
									<h4><a href="class-details.html">JSC Special Course</a></h4>
									<ul class="class__time">
										<li>Class Eight</li>
										<li>July to October</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<!-- End Single Classes -->
					<!-- Start Single Classes -->
					<div class="col-lg-4 col-sm-6">
						<div class="junior__classes">
							<div class="classes__thumb p-t-20">
								<a href="class-details.html">
									<img src="{{ asset('image/special_1.png') }}" alt="class images" style="width: 80px!important">
								</a>
							</div>
							<div class="classes__inner">
								<div class="class__details p-t-20">
									<h4><a href="class-details.html">PECE Special Course</a></h4>
									<ul class="class__time">
										<li>Class Five</li>
										<li>July to November</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<!-- End Single Classes -->
				</div>
			</div>
		</section>
		<!-- End our Class Area -->
		<!-- Start Our Gallery Area -->
		<section class="junior__gallery__area bg--white section-padding--lg" id="gallery">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 col-sm-12 col-md-12">
						<div class="section__title text-center">
							<h2 class="title__line">Our Gallery</h2>
							<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunte magna aliquaet, consectetempora incidunt</p>
						</div>
					</div>
				</div>
				<div class="row galler__wrap mt--40">
					<!-- Start Single Gallery -->
					<div class="col-lg-4 col-md-6 col-sm-6 col-12">
						<div class="gallery wow fadeInUp">
							<div class="gallery__thumb">
								<a>
									<img src="{{ asset('image/gallery/1.jpg') }}" alt="gallery images">
								</a>
							</div>
							<div class="gallery__hover__inner">
								<div class="gallery__hover__action">
									<ul class="gallery__zoom">
										<li><a href="{{ asset('image/gallery/1.jpg') }}" data-lightbox="grportimg" data-title="Exam"><i class="fa fa-search"></i></a></li>
									</ul>
									<h4 class="gallery__title"><a href="#">Attending in Exam</a></h4>
								</div>
							</div>
						</div>	
					</div>	
					<!-- End Single Gallery -->
					<!-- Start Single Gallery -->
					<div class="col-lg-4 col-md-6 col-sm-6 col-12">
						<div class="gallery wow fadeInUp">
							<div class="gallery__thumb">
								<a>
									<img src="{{ asset('image/gallery/2.jpg') }}" alt="gallery images">
								</a>
							</div>
							<div class="gallery__hover__inner">
								<div class="gallery__hover__action">
									<ul class="gallery__zoom">
										<li><a href="{{ asset('image/gallery/2.jpg') }}" data-lightbox="grportimg" data-title="Exam"><i class="fa fa-search"></i></a></li>
									</ul>
									<h4 class="gallery__title"><a href="#">Attending in Exam</a></h4>
								</div>
							</div>
						</div>	
					</div>	
					<!-- End Single Gallery -->
					<!-- Start Single Gallery -->
					<div class="col-lg-4 col-md-6 col-sm-6 col-12">
						<div class="gallery wow fadeInUp">
							<div class="gallery__thumb">
								<a>
									<img src="{{ asset('image/gallery/3.jpg') }}" alt="gallery images">
								</a>
							</div>
							<div class="gallery__hover__inner">
								<div class="gallery__hover__action">
									<ul class="gallery__zoom">
										<li><a href="{{ asset('image/gallery/3.jpg') }}" data-lightbox="grportimg" data-title="Prize Giving"><i class="fa fa-search"></i></a></li>
									</ul>
									<h4 class="gallery__title"><a href="#">Prize Giving Ceremony</a></h4>
								</div>
							</div>
						</div>	
					</div>	
					<!-- End Single Gallery -->
					<!-- Start Single Gallery -->
					<div class="col-lg-4 col-md-6 col-sm-6 col-12">
						<div class="gallery wow fadeInUp">
							<div class="gallery__thumb">
								<a>
									<img src="{{ asset('image/gallery/4.jpg') }}" alt="gallery images">
								</a>
							</div>
							<div class="gallery__hover__inner">
								<div class="gallery__hover__action">
									<ul class="gallery__zoom">
										<li><a href="{{ asset('image/gallery/4.jpg') }}" data-lightbox="grportimg" data-title="Prize Giving"><i class="fa fa-search"></i></a></li>
									</ul>
									<h4 class="gallery__title"><a href="#">Prize Giving Ceremony</a></h4>
								</div>
							</div>
						</div>	
					</div>	
					<!-- End Single Gallery -->
					<!-- Start Single Gallery -->
					<div class="col-lg-4 col-md-6 col-sm-6 col-12">
						<div class="gallery wow fadeInUp">
							<div class="gallery__thumb">
								<a>
									<img src="{{ asset('image/gallery/5.jpg') }}" alt="gallery images">
								</a>
							</div>
							<div class="gallery__hover__inner">
								<div class="gallery__hover__action">
									<ul class="gallery__zoom">
										<li><a href="{{ asset('image/gallery/5.jpg') }}" data-lightbox="grportimg" data-title="Prize Giving"><i class="fa fa-search"></i></a></li>
									</ul>
									<h4 class="gallery__title"><a href="#">Prize Giving Ceremony</a></h4>
								</div>
							</div>
						</div>	
					</div>	
					<!-- End Single Gallery -->
					<!-- Start Single Gallery -->
					<div class="col-lg-4 col-md-6 col-sm-6 col-12">
						<div class="gallery wow fadeInUp">
							<div class="gallery__thumb">
								<a>
									<img src="{{ asset('image/gallery/6.jpg') }}" alt="gallery images">
								</a>
							</div>
							<div class="gallery__hover__inner">
								<div class="gallery__hover__action">
									<ul class="gallery__zoom">
										<li><a href="{{ asset('image/gallery/6.jpg') }}" data-lightbox="grportimg" data-title="Prize Giving"><i class="fa fa-search"></i></a></li>
									</ul>
									<h4 class="gallery__title"><a href="#">Prize Giving</a></h4>
								</div>
							</div>
						</div>	
					</div>		
					<!-- End Single Gallery -->
					<!-- Start Single Gallery -->
					<div class="col-lg-4 col-md-6 col-sm-6 col-12">
						<div class="gallery wow fadeInUp">
							<div class="gallery__thumb">
								<a>
									<img src="{{ asset('image/gallery/7.jpg') }}" alt="gallery images">
								</a>
							</div>
							<div class="gallery__hover__inner">
								<div class="gallery__hover__action">
									<ul class="gallery__zoom">
										<li><a href="{{ asset('image/gallery/7.jpg') }}" data-lightbox="grportimg" data-title="Prize Giving"><i class="fa fa-search"></i></a></li>
									</ul>
									<h4 class="gallery__title"><a href="#">Prize Giving Ceremony</a></h4>
								</div>
							</div>
						</div>	
					</div>	
					<!-- End Single Gallery -->
					<!-- Start Single Gallery -->
					<div class="col-lg-4 col-md-6 col-sm-6 col-12">
						<div class="gallery wow fadeInUp">
							<div class="gallery__thumb">
								<a>
									<img src="{{ asset('image/gallery/8.jpg') }}" alt="gallery images">
								</a>
							</div>
							<div class="gallery__hover__inner">
								<div class="gallery__hover__action">
									<ul class="gallery__zoom">
										<li><a href="{{ asset('image/gallery/8.jpg') }}" data-lightbox="grportimg" data-title="Prize Giving"><i class="fa fa-search"></i></a></li>
									</ul>
									<h4 class="gallery__title"><a href="#">Prize Giving Ceremony</a></h4>
								</div>
							</div>
						</div>	
					</div>	
					<!-- End Single Gallery -->
					<!-- Start Single Gallery -->
					<div class="col-lg-4 col-md-6 col-sm-6 col-12">
						<div class="gallery wow fadeInUp">
							<div class="gallery__thumb">
								<a>
									<img src="{{ asset('image/gallery/9.jpg') }}" alt="gallery images">
								</a>
							</div>
						</div>	
					</div>		
					<!-- End Single Gallery -->
				</div>	
			</div>
		</section>
		<!-- Footer Area -->
			<!-- .Start Footer Contact Area -->
			<div class="footer__contact__area bg__cat--2" id="contact">
				<div class="container">
					<div class="row">
						<div class="col-lg-12">
							<div class="footer__contact__wrapper d-flex flex-wrap justify-content-between">
								<div class="single__footer__address">
									<div class="ft__contact__icon">
										<i class="fa fa-home"></i>
									</div>
									<div class="ft__contact__details">
										<p>Sekandar Center, 3rd Floor</p>
										<p>Oxygen Moor</p>
									</div>
								</div>
								<div class="single__footer__address">
									<div class="ft__contact__icon">
										<i class="fa fa-phone"></i>
									</div>
									<div class="ft__contact__details">
										<p><a>+088 01778021126</a></p>
										<p><a>+088 01688554488</a></p>
									</div>
								</div>
								<div class="single__footer__address">
									<div class="ft__contact__icon">
										<i class="fa fa-envelope"></i>
									</div>
									<div class="ft__contact__details">
										<p><a>sohopath.co@gmail.com</a></p>
										<p><a>support@sohopath.com</a></p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- .End Footer Contact Area -->
			<div class="copyright  bg--theme">
				<div class="container">
					<div class="row align-items-center copyright__wrapper justify-content-center">
						<div class="col-lg-12 col-sm-12 col-md-12">
							<div class="coppy__right__inner text-center">
								<p><i class="fa fa-copyright"></i>All Right Reserved.<a href="#"> Shunno-ek Tecknology</a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</footer>
		<!-- //Footer Area -->

        <!-- Register Form -->
        <div class="accountbox-wrapper">
            <div class="accountbox">
                <div class="accountbox__inner">
                	<h4>continue to register</h4>
                    <div class="accountbox__login">
                        <form action="#">
                            <div class="single-input">
                                <input  type="text" placeholder="User name">
                            </div>
                            <div class="single-input">
                                <input type="email" placeholder="E-mail">
                            </div>
                            <div class="single-input">
                                <input type="text" placeholder="Phone">
                            </div>
                            <div class="single-input">
                                <input type="password" placeholder="Password">
                            </div>
                            <div class="single-input">
                                <input type="password" placeholder="Confirm password">
                            </div>
                            <div class="single-input text-center">
                                <button type="submit" class="sign__btn">Sign Up Now</button>
                            </div>
                            <div class="accountbox-login__others text-center">
                                <h6>or register with</h6>
                                <ul class="dacre__social__link d-flex justify-content-center">
                                    <li class="facebook"><a target="_blank" href="https://www.facebook.com/"><span class="ti-facebook"></span></a></li>
                                    <li class="twitter"><a target="_blank" href="https://twitter.com/"><span class="ti-twitter"></span></a></li>
                                    <li class="pinterest"><a target="_blank" href="#"><span class="ti-google"></span></a></li>
                                </ul>
                            </div>
                        </form>
                    </div>
                    <span class="accountbox-close-button"><i class="zmdi zmdi-close"></i></span>
                </div>
                <h3>Have an account ? Login Fast</h3>
            </div>
        </div><!-- //Register Form -->

        <!-- Login Form -->
        <div class="login-wrapper">
            <div class="accountbox">
                <div class="accountbox__inner">
                	<h4>Login to Continue</h4>
                	<div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
					  <p></p>
					  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
					    <span aria-hidden="true">&times;</span>
					  </button>
					</div>
                    <div class="accountbox__login">
                        <form action="{{ route('login') }}" method="post" id="login_form">
                        	{{ csrf_field() }}
                            <div class="single-input">
                                <input type="email" placeholder="E-mail" name="email">
                            </div>
                            <div class="single-input">
                                <input type="password" placeholder="Password" name="password">
                            </div>
                            <div class="single-input text-center">
                                <button type="submit" class="sign__btn"><span style="display: none;"><i class="fa fa-spinner fa-spin m-r-5"></i></span><strong>SUBMIT</strong></button>
                                
                            </div>
                        </form>
                    </div>
                    <span class="accountbox-close-button"><i class="zmdi zmdi-close"></i></span>
                </div>
                <h3><a href="{{ route('password.request') }}">I have forgot my passowrd!</a></h3>
            </div>
        </div><!-- //Login Form -->

	</div><!-- //Main wrapper -->

	<!-- JS Files -->
	<script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
	<script src="{{ asset('js/popper.min.js') }}"></script>
	<script src="{{ asset('js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('js/plugins.js') }}"></script>
	<script src="{{ asset('js/active.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$('.mainmenu__nav .mainmenu li a').click(function(e){
				e.preventDefault()
				var id = $(this).attr('href')
				$('html, body').animate({
		            scrollTop: $(id).offset().top - 120
		        }, 500);
			})
			$('#login_form').submit(function(e){
				e.preventDefault()
				var form_data = new FormData
				form_data.append('email', $('input[name=email]').val())
				form_data.append('password', $('input[name=password]').val())
				// loader
				$('.sign__btn span').show()
				$('.sign__btn strong').text('Loading...')
				$('.sign__btn').prop('disabled', true)
				$.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "post",
                    url:  '/login',
                    cache: false,
                    data: form_data,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                    	$('.alert.alert-danger').hide()
                    	$('.sign__btn span').hide()
						$('.sign__btn strong').text('SUBMIT')
                        location.href = '/user/home'
                    },
                    error: function(data){
                        console.log(data.responseJSON.message)
                        $('.alert.alert-danger p').text(data.responseJSON.message)
                        $('.alert.alert-danger').show()
                        $('.sign__btn span').hide()
						$('.sign__btn strong').text('SUBMIT')
						$('.sign__btn').prop('disabled', false)
                    }
                });
			})
		})
	</script>
</body>
</html>
