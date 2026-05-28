<div id="testimonials">
	<ul>
		<li>
			<p class="testimonial-body">It was getting really frustrating looking for a family PPO plan. I really thought I wouldn't be able to get enough coverage for my family this year. However, thanks to <?php echo $sitename; ?> I didn't just get a plan ON TIME, I was able to get more than enough coverage at the lowest price!</p>
			<div class="testimonial-profile">
				<svg><circle></svg>
				<img src="/img/people/2.png" alt="">
				<span>Mitchel M.</span>
				<br><small data-social="facebook">Feb 2, 2017</small>
			</div>
		</li><li>
			<p class="testimonial-body">With Open Enrollment approaching fast, I was stressed about adding the right coverage to the plan I already had. Once I found <?php echo $sitename; ?> I had all the help I needed! They provided so many options and helped me every step of the way. Now all my health needs are covered.</p>
			<div class="testimonial-profile">
				<svg><circle></svg>
				<img src="/img/people/21.png" alt="">
				<span>Christie J.</span>
				<br><small data-social="facebook">May 5, 2017</small>
			</div>
		</li><li>
			<p class="testimonial-body"><?php echo $sitename; ?> is second-to-none when it comes to giving you options to choose from. I had no idea there were so many plans available in my area. As soon as I signed up I started receiving thousands of policies I could compare for FREE! They're the best choice, period.</p>
			<div class="testimonial-profile">
				<svg><circle></svg>
				<img src="/img/people/8.png" alt="">
				<span>Frank G.</span>
				<br><small data-social="facebook">Jun 17, 2017</small>
			</div>
		</li><li>
			<p class="testimonial-body">This is hands-down the best way to get the right coverage. The plans didn't only stay in the budget I asked for, but they offered all-inclusive options that I really felt confident in choosing from. Without <?php echo $sitename; ?> I wouldn't have been able to find a plan THIS GOOD!</p>
			<div class="testimonial-profile">
				<svg><circle></svg>
				<img src="/img/people/20.png" alt="">
				<span>Mary S.</span>
				<br><small data-social="facebook">Jan 11, 2018</small>
			</div>
		</li><li>
			<p class="testimonial-body">Comparing <?php echo $sitename; ?> to other plan providers, I can see I was wasting my time and spinning my wheels looking for coverage. <?php echo $sitename; ?> gave me free quotes I could start reviewing for free THE SAME DAY I signed up. The process couldn't have been easier. Thanks!</p>
			<div class="testimonial-profile">
				<svg><circle></svg>
				<img src="/img/people/13.png" alt="">
				<span>Alex D.</span>
				<br><small data-social="facebook">Nov 21, 2017</small>
			</div>
		</li>
	</ul>
</div>
<style>
#testimonials {
	width: 100%;
	background: #eceff1;
	border-radius: 15px;
	overflow: hidden;
}
#testimonials ul {
  	display: -webkit-box;
  	display: -ms-flexbox;
  	display: flex;
  	margin: 0;
  	padding: 0;
  	width: calc(100% * 5);
  	height: 100%;
  	padding-left: 0;
  	list-style: none;
  	-webkit-animation: testimonial-slide 76.25s infinite;
	        animation: testimonial-slide 76.25s infinite;
}
#testimonials ul:hover, #testimonials ul:hover svg, #testimonials ul:hover circle {
  	-webkit-animation-play-state: paused !important;
	        animation-play-state: paused !important;
}
#testimonials ul li {
  	display: -webkit-box;
  	display: -ms-flexbox;
  	display: flex;
  	-webkit-box-orient: vertical;
  	-webkit-box-direction: normal;
	    -ms-flex-direction: column;
	        flex-direction: column;
  	padding: 24px;
  	width: calc(100% / 5);
  	-ms-flex-negative: 0;
	    flex-shrink: 0;
  	-webkit-user-select: none;
	   -moz-user-select: none;
	    -ms-user-select: none;
	        user-select: none;
}
#testimonials ul li .testimonial-body {
  	-webkit-box-flex: 1;
	    -ms-flex-positive: 1;
	        flex-grow: 1;
  	margin: 0;
  	quotes: "“" "”" "‘" "’";
  	font-size: 20px;
}
#testimonials ul li .testimonial-body:before, #testimonials ul li .testimonial-body:after {
  	display: inline-block;
  	position: relative;
  	-webkit-transform: scale(1.25);
	        transform: scale(1.25);
}
#testimonials ul li .testimonial-body:before {
  	content: open-quote;
  	left: -1px;
}
#testimonials ul li .testimonial-body:after {
  	content: close-quote;
  	right: -1px;
}
#testimonials ul li .testimonial-profile {
  	position: relative;
  	white-space: nowrap;
  	margin-top: .75rem;
}
#testimonials ul li .testimonial-profile svg {
  	position: absolute;
  	width: 48px;
  	height: 48px;
  	top: 0;
  	left: 0;
  	-webkit-filter: drop-shadow(0 0 4px rgba(51, 165, 255, 0.5));
	        filter: drop-shadow(0 0 4px rgba(51, 165, 255, 0.5));
  	-webkit-transform: rotate(90deg);
	        transform: rotate(90deg);
  	overflow: visible;
  	z-index: 1;
  	-webkit-animation: testimonial-progress-svg 15.25s infinite;
	        animation: testimonial-progress-svg 15.25s infinite;
}
#testimonials ul li .testimonial-profile svg circle {
  	-webkit-transform-origin: center;
	        transform-origin: center;
  	cx: 24;
  	cy: 24;
  	r: 24;
  	stroke-linecap: round;
  	fill: none;
  	stroke: #33a5ff;
  	stroke-width: 4px;
  	stroke-dasharray: 150.7964472;
  	stroke-dashoffset: 150.7964472;
  	-webkit-animation: testimonial-progress-circle 15.25s infinite;
	        animation: testimonial-progress-circle 15.25s infinite;
}
#testimonials ul li .testimonial-profile img, #testimonials ul li .testimonial-profile span, #testimonials ul li .testimonial-profile small {
  	float: left;
  	white-space: inherit;
}
#testimonials ul li .testimonial-profile img {
  	margin-right: .75rem;
  	width: 48px;
  	height: 48px;
  	border-radius: 1000px;
  	-webkit-box-shadow: 0 7px 10px -2px rgba(94, 114, 128, 0.55);
	        box-shadow: 0 7px 10px -2px rgba(94, 114, 128, 0.55);
  	cursor: pointer;
  	-webkit-transition: all 0.1s ease-in-out;
  	transition: all 0.1s ease-in-out;
}
@media (min-width: 768px) {
  	#testimonials ul li .testimonial-profile img:hover {
	  -webkit-transform: scale(1.1);
	          transform: scale(1.1);
  	}
}
#testimonials ul li .testimonial-profile span {
  	vertical-align: top;
  	font-weight: 500;
  	color: #5e7280;
}
#testimonials ul li .testimonial-profile small {
  	font-weight: 300;
  	color: #94a5b0;
}
#testimonials ul li .testimonial-profile small[data-social]:before {
  	display: inline-block;
  	margin-right: 10px;
  	font-family: "Font Awesome 5 Brands";
  	font-size: inherit;
  	-webkit-transform: scale(1.1);
	        transform: scale(1.1);
}
#testimonials ul li .testimonial-profile small[data-social][data-social="facebook"]:before {
  	content: '\f082';
  	color: #3b5998;
}
#testimonials ul li .testimonial-profile small[data-social][data-social="twitter"]:before {
  	content: '\f099';
  	color: #1da1f2;
}
#testimonials ul li .testimonial-profile small[data-social][data-social="google"]:before {
  	content: '\f0d5';
  	color: #ea4335;
}

@-webkit-keyframes testimonial-slide {
  	0%, 19.6721311475% {
		-webkit-transform: translateX(calc(0 * (100% / 5)));
		        transform: translateX(calc(0 * (100% / 5)));
  	}
  	20%, 39.6721311475% {
		-webkit-transform: translateX(calc(-1 * (100% / 5)));
		        transform: translateX(calc(-1 * (100% / 5)));
  	}
  	40%, 59.6721311475% {
		-webkit-transform: translateX(calc(-2 * (100% / 5)));
		        transform: translateX(calc(-2 * (100% / 5)));
  	}
  	60%, 79.6721311475% {
		-webkit-transform: translateX(calc(-3 * (100% / 5)));
		        transform: translateX(calc(-3 * (100% / 5)));
  	}
  	80%, 99.6721311475% {
		-webkit-transform: translateX(calc(-4 * (100% / 5)));
		        transform: translateX(calc(-4 * (100% / 5)));
  	}
  	100% {
		-webkit-transform: translateX(0);
		        transform: translateX(0);
  	}
}

@keyframes testimonial-slide {
  	0%, 19.6721311475% {
		-webkit-transform: translateX(calc(0 * (100% / 5)));
		        transform: translateX(calc(0 * (100% / 5)));
  	}
  	20%, 39.6721311475% {
		-webkit-transform: translateX(calc(-1 * (100% / 5)));
		        transform: translateX(calc(-1 * (100% / 5)));
  	}
  	40%, 59.6721311475% {
		-webkit-transform: translateX(calc(-2 * (100% / 5)));
		        transform: translateX(calc(-2 * (100% / 5)));
  	}
  	60%, 79.6721311475% {
		-webkit-transform: translateX(calc(-3 * (100% / 5)));
		        transform: translateX(calc(-3 * (100% / 5)));
  	}
  	80%, 99.6721311475% {
		-webkit-transform: translateX(calc(-4 * (100% / 5)));
		        transform: translateX(calc(-4 * (100% / 5)));
  	}
  	100% {
		-webkit-transform: translateX(0);
		        transform: translateX(0);
  	}
}
@-webkit-keyframes testimonial-progress-svg {
  	0% {
		-webkit-filter: drop-shadow(0 0 4px rgba(51, 165, 255, 0.5));
		        filter: drop-shadow(0 0 4px rgba(51, 165, 255, 0.5));
  	}
  	49.1803278689% {
		-webkit-filter: drop-shadow(0 0 4px rgba(64, 184, 169, 0.5));
		        filter: drop-shadow(0 0 4px rgba(64, 184, 169, 0.5));
  	}
  	98.3606557377%, 99.1803278689% {
		-webkit-filter: drop-shadow(0 0 4px rgba(77, 203, 82, 0.5));
		        filter: drop-shadow(0 0 4px rgba(77, 203, 82, 0.5));
  	}
  	99.1803278689%, 100% {
		-webkit-filter: drop-shadow(0 0 4px rgba(77, 203, 82, 0.5));
		        filter: drop-shadow(0 0 4px rgba(77, 203, 82, 0.5));
  	}
}
@keyframes testimonial-progress-svg {
  	0% {
		-webkit-filter: drop-shadow(0 0 4px rgba(51, 165, 255, 0.5));
		        filter: drop-shadow(0 0 4px rgba(51, 165, 255, 0.5));
  	}
  	49.1803278689% {
		-webkit-filter: drop-shadow(0 0 4px rgba(64, 184, 169, 0.5));
		        filter: drop-shadow(0 0 4px rgba(64, 184, 169, 0.5));
  	}
  	98.3606557377%, 99.1803278689% {
		-webkit-filter: drop-shadow(0 0 4px rgba(77, 203, 82, 0.5));
		        filter: drop-shadow(0 0 4px rgba(77, 203, 82, 0.5));
  	}
  	99.1803278689%, 100% {
		-webkit-filter: drop-shadow(0 0 4px rgba(77, 203, 82, 0.5));
		        filter: drop-shadow(0 0 4px rgba(77, 203, 82, 0.5));
  	}
}
@-webkit-keyframes testimonial-progress-circle {
  	0% {
		stroke: #33a5ff;
		stroke-width: 3px;
		stroke-dasharray: 150.7964472;
		stroke-dashoffset: 149.7964472;
		-webkit-transform: rotate(0deg);
		        transform: rotate(0deg);
  	}
  	49.1803278689% {
		stroke-dasharray: 41.887902;
		stroke-dashoffset: 37.4491118;
  	}
  	98.3606557377%, 99.1803278689% {
		stroke: #4dcb52;
		stroke-width: 3.5px;
		stroke-dasharray: 150.7964472;
		stroke-dashoffset: 0;
		-webkit-transform: rotate(2340deg);
		        transform: rotate(2340deg);
  	}
  	99.1803278689%, 100% {
		stroke: #33a5ff;
		stroke-width: 0px;
		stroke-dashoffset: 149.7964472;
		-webkit-transform: rotate(0deg);
		        transform: rotate(0deg);
  	}
}
@keyframes testimonial-progress-circle {
  	0% {
		stroke: #33a5ff;
		stroke-width: 3px;
		stroke-dasharray: 150.7964472;
		stroke-dashoffset: 149.7964472;
		-webkit-transform: rotate(0deg);
		        transform: rotate(0deg);
  	}
  	49.1803278689% {
		stroke-dasharray: 41.887902;
		stroke-dashoffset: 37.4491118;
  	}
  	98.3606557377%, 99.1803278689% {
		stroke: #4dcb52;
		stroke-width: 3.5px;
		stroke-dasharray: 150.7964472;
		stroke-dashoffset: 0;
		-webkit-transform: rotate(2340deg);
		        transform: rotate(2340deg);
  	}
  	99.1803278689%, 100% {
		stroke: #33a5ff;
		stroke-width: 0px;
		stroke-dashoffset: 149.7964472;
		-webkit-transform: rotate(0deg);
		        transform: rotate(0deg);
  	}
}
</style>