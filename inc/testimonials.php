<div id="testimonials">
	<ul>
		<li>
			<p class="testimonial-body">I was overwhelmed trying to compare family plans on my own. After using <?php echo $sitename; ?>, I could finally review options in my area and choose coverage that worked for my household without all the guesswork.</p>
			<div class="testimonial-profile">
				<svg><circle></svg>
				<img src="/img/people/2.png" alt="">
				<span>Mitchel M.</span>
				<br><small data-social="facebook">Feb 2, 2017</small>
			</div>
		</li><li>
			<p class="testimonial-body">Open Enrollment was coming up quickly, and I did not want to make the wrong choice. <?php echo $sitename; ?> helped me review clear options, ask questions, and feel confident about the coverage I selected.</p>
			<div class="testimonial-profile">
				<svg><circle></svg>
				<img src="/img/people/21.png" alt="">
				<span>Christie J.</span>
				<br><small data-social="facebook">May 5, 2017</small>
			</div>
		</li><li>
			<p class="testimonial-body">I did not expect the process to be this straightforward. I entered my ZIP code, reviewed the plans that matched my needs, and got help understanding the differences before I enrolled.</p>
			<div class="testimonial-profile">
				<svg><circle></svg>
				<img src="/img/people/8.png" alt="">
				<span>Frank G.</span>
				<br><small data-social="facebook">Jun 17, 2017</small>
			</div>
		</li><li>
			<p class="testimonial-body">What stood out to me was how easy it was to compare details side by side. I could focus on doctors, prescriptions, and monthly costs instead of bouncing between different websites.</p>
			<div class="testimonial-profile">
				<svg><circle></svg>
				<img src="/img/people/20.png" alt="">
				<span>Mary S.</span>
				<br><small data-social="facebook">Jan 11, 2018</small>
			</div>
		</li><li>
			<p class="testimonial-body">I had been putting this off for weeks because it felt complicated. With <?php echo $sitename; ?>, the process was simple and organized, and I was able to make a decision much faster than I expected.</p>
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
	-webkit-filter: drop-shadow(0 0 4px rgba(103, 205, 203, 0.5));
	        filter: drop-shadow(0 0 4px rgba(103, 205, 203, 0.5));
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
	stroke: #67cdcb;
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
	content: "";
	width: 14px;
	height: 14px;
	margin-right: 8px;
	vertical-align: -2px;
	background-repeat: no-repeat;
	background-position: center;
	background-size: contain;
}
#testimonials ul li .testimonial-profile small[data-social][data-social="facebook"]:before {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%233b5998' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M7 10v4h3v7h4v-7h3l1-4h-4v-2c0-1 .5-2 2-2h2V2h-3c-3 0-5 2-5 5v3H7'/%3E%3C/svg%3E");
}
#testimonials ul li .testimonial-profile small[data-social][data-social="twitter"]:before {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231da1f2' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M4 4l11.733 16h4.267L8.267 4z'/%3E%3Cpath d='M4 20l6.768-6.768m2.46-2.46L20 4'/%3E%3C/svg%3E");
}
#testimonials ul li .testimonial-profile small[data-social][data-social="google"]:before {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ea4335' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 12a8 8 0 1 0-2.343 5.657'/%3E%3Cpath d='M20 12h-8'/%3E%3C/svg%3E");
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
		-webkit-filter: drop-shadow(0 0 4px rgba(103, 205, 203, 0.5));
		        filter: drop-shadow(0 0 4px rgba(103, 205, 203, 0.5));
  	}
  	49.1803278689% {
		-webkit-filter: drop-shadow(0 0 4px rgba(242, 194, 48, 0.45));
		        filter: drop-shadow(0 0 4px rgba(242, 194, 48, 0.45));
  	}
  	98.3606557377%, 99.1803278689% {
		-webkit-filter: drop-shadow(0 0 4px rgba(19, 103, 107, 0.45));
		        filter: drop-shadow(0 0 4px rgba(19, 103, 107, 0.45));
  	}
  	99.1803278689%, 100% {
		-webkit-filter: drop-shadow(0 0 4px rgba(19, 103, 107, 0.45));
		        filter: drop-shadow(0 0 4px rgba(19, 103, 107, 0.45));
  	}
}
@keyframes testimonial-progress-svg {
  	0% {
		-webkit-filter: drop-shadow(0 0 4px rgba(103, 205, 203, 0.5));
		        filter: drop-shadow(0 0 4px rgba(103, 205, 203, 0.5));
  	}
  	49.1803278689% {
		-webkit-filter: drop-shadow(0 0 4px rgba(242, 194, 48, 0.45));
		        filter: drop-shadow(0 0 4px rgba(242, 194, 48, 0.45));
  	}
  	98.3606557377%, 99.1803278689% {
		-webkit-filter: drop-shadow(0 0 4px rgba(19, 103, 107, 0.45));
		        filter: drop-shadow(0 0 4px rgba(19, 103, 107, 0.45));
  	}
  	99.1803278689%, 100% {
		-webkit-filter: drop-shadow(0 0 4px rgba(19, 103, 107, 0.45));
		        filter: drop-shadow(0 0 4px rgba(19, 103, 107, 0.45));
  	}
}
@-webkit-keyframes testimonial-progress-circle {
  	0% {
		stroke: #67cdcb;
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
		stroke: #13676b;
		stroke-width: 3.5px;
		stroke-dasharray: 150.7964472;
		stroke-dashoffset: 0;
		-webkit-transform: rotate(2340deg);
		        transform: rotate(2340deg);
  	}
  	99.1803278689%, 100% {
		stroke: #67cdcb;
		stroke-width: 0px;
		stroke-dashoffset: 149.7964472;
		-webkit-transform: rotate(0deg);
		        transform: rotate(0deg);
  	}
}
@keyframes testimonial-progress-circle {
  	0% {
		stroke: #67cdcb;
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
		stroke: #13676b;
		stroke-width: 3.5px;
		stroke-dasharray: 150.7964472;
		stroke-dashoffset: 0;
		-webkit-transform: rotate(2340deg);
		        transform: rotate(2340deg);
  	}
  	99.1803278689%, 100% {
		stroke: #67cdcb;
		stroke-width: 0px;
		stroke-dashoffset: 149.7964472;
		-webkit-transform: rotate(0deg);
		        transform: rotate(0deg);
  	}
}
</style>