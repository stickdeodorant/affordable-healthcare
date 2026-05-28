<?php 
	if(isset($_GET['st'])) {
		$FAQs = [
			'AZ' => 'Purchasing health insurance plans in Arizona can be complicated. Deciding which plan is best, what insurer can you trust or if the plan is affordabl, can be overwhelming. Let ' . $sitename . ' help in the process. Our expert agents can offer advice on making the best decision for coverage.
			' . $sitename . ' can help you weigh your options in time for Open Enrollment or during any Special Enrollment period. Don\'t be amongh Arizona\'s almost 7.2 million residents caught without adequare or affordable insurance.<br>&nbsp;<br>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'CA' => 'There are many types healthcare coverage in California. Health insurance, Health Plans (HMOs), and public programs like Medicare and Medi-Cal are all different. They each follow their own set of rules. Different government agencies regulate each one.<br>&nbsp;<br>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'CO' => 'Healthcare-Insurance offers a varied selection of Colorado health plans for individuals, families and short term needs from most of the leading Colorado health insurance companies to-date.<br>&nbsp;<br>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'IA' => 'In Iowa, enrollment hit unprecedented highs in 2016, while it did increased in 2018 and in 2020, it\'s never reached the levels it had been at in 2016. With that being said, enrollments for healthcare insurance surpassed its 2016 levels in 2021.<br>&nbsp;<br>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'KS' => '<strong>Short-term, Limited Duration Insurance</strong><br>
			Short-term, limited duration insurance (STLDI) policies in Kansas may include policy terms of either six or twelve months (364 days) with one renewal for a maximum policy duration of twenty-four months pursuant to state law.<br>&nbsp;<br>
			<strong>Individual plan premium rates</strong><br>
			Individual plan premium rates may vary by age, rating area, family composition and tobacco usage. A person living in Frankfort, Kansas (rating area 2) may pay a different rate than someone living in Pittsburg, Kansas (rating area 7) based on the claims data by rating area.<br>&nbsp;<br>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'LA' => 'Despite the Open Enrollment period being over for current coverage plans, Louisiana residents can still enroll or change their health insurance coverage if they have a qualifying life event. Qualifying life events can trigger a special enrollment period. In addition, Louisianan residents can shop for short-term plans to get coverage until the next open enrollment period.<br>&nbsp;<br>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'MI' => 'Get an affordable plan from some of the top name brands in Michigan healthcare covering areas such as Grand Rapids, Detroit, Warren and everywhere else in between. Options include:<br>
			<ul>
				<li>Molina Health Insurance</li>
				<li>Oscar Insurance Company</li>
				<li>Priority Health</li>
				<li>Blue Cross Blue Shield of Michigan</li>
				<li>Health Alliance Plan</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'MO' => 'Get an affordable plan from some of the top name brands in Montana healthcare covering areas such as Great Falls, Billings, Missoula and everywhere else in between. Options include:<br>
			<ul>
				<li>PacificSource Health Plans</li>
				<li>BlueCross BlueShield of Montana</li>
				<li>Golden Rule Insurance Company</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).<br>',
			'NV' => 'Get an affordable plan from some of the top name brands in Nevada healthcare covering areas such as Reno, Las Vegas, Reno and everywhere else in between. Options include:<br>
			<ul>
				<li>Health Plan of Nevada</li>
				<li>Sierra Health and Life</li>
				<li>Friday Health Plans</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'NC' => 'Get an affordable plan from some of the top name brands in North Carolina healthcare covering areas such as Raleigh, Charlotte, Greensboro,Durham and everywhere else in between. Options include:<br>
			<ul>
				<li>Ambetter</li>
				<li>Bright Health</li>
				<li>Cigna Healthcare of North Carolina</li>
				<li>Oscar</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'OH' => 'Get an affordable plan from some of the top name brands in Ohio healthcare covering areas such as Cleveland, Columbus, Cincinnati, and everywhere else in between. Options include:<br>
			<ul>
				<li>Ambetter from Buckeye Health</li>
				<li>Anthem BlueCross BlueShield of Ohio</li>
				<li>Molina Health Care</li>
				<li>SummaCare Inc of Ohio</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'TN' => 'Get an affordable plan from some of the top name brands in Tennessee healthcare covering areas such as Knoxville, Memphis, Nashville and everywhere else in between. Options include:<br>
			<ul>
				<li>Ambetter</li>
				<li>Bright Health Plans</li>
				<li>BlueCross BlueShield of Tennessee</li>
				<li>Cigna</li>
				<li>Oscar</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'TX' => 'Get an affordable plan from some of the top name brands in Texas healthcare covering areas such as Austin, Dallas, Houston, San Antonio and everywhere else in between. Options include:<br>
			<ul>
				<li>Oscar Insurance Corportation</li>
				<li>Molina Health Care</li>
				<li>Ambetter from Superior HealthPlan</li>
				<li>FirstCare</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'UT' => 'Get an affordable plan from some of the top name brands in Utah healthcare covering areas such as Provo, Salt Lake City, West Valley City and everywhere else in between. Options include:<br>
			<ul>
				<li>SelectHealth</li>
				<li>Regence BlueCross BlueShield of Utah</li>
				<li>Molina Health Care</li>
				<li>BridgeSpan</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'WV' => 'Get an affordable plan from some of the top name brands in West Virginia healthcare covering areas such as Chesapeake, Norfolk, Virginia Beach and everywhere else in between. Options include:<br>
			<ul>
				<li>Anthem BlueCross and BlueShield of VA</li>
				<li>Optima Health Plan</li>
				<li>Kaiser Mid-Attlanic</li>
				<li>Oscar</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'WI' => 'Get an affordable plan from some of the top name brands in Wisconsin healthcare covering areas such as Appleton, Milwaukee, Oshkosh, Green Bay, and everywhere else in between. Options include:<br>
			<ul>
				<li>Dean Health Plan Inc.</li>
				<li>Quartz</li>
				<li>Prevea 360 Health Plans</li>
				<li>HealthPartners</li>
				<li>Medica</li>
				<li>Security HealthPlan</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).',
			'WY' => 'Get an affordable plan from some of the top name brands in Wyoming healthcare covering areas such as Bar Nunn, Basin, Bear River and everywhere else in between. Options include:<br>
			<ul>
				<li>BlueCross Blue Shield of Wyoming</li>
				<li>United Healthcare Wyoming</li>
				<li>and more...</li>
			</ul>
			<strong>Terms and cost to consider</strong><br>
			<strong>Premium:</strong> what you pay your insurance company each month for coverage.<br>
			<strong>Deductible:</strong> a set amount you must pay to providers before your insurance company starts paying its share of the bills. Your deductible resets each plan year.<br>
			<strong>Copayments and Coinsurance:</strong> Copays are a flat fee (e.g., $20 to visit a doctor\'s office). Coinsurance is a percentage of costs (for example, 25% of the cost of a prescription drug).'
		];
		?>
	<div class="card">
		<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseState" aria-expanded="true" aria-controls="collapseState">
			<div class="card-header" id="headingState">
				<h5 class="card-header-title my-0">
					<?=$state?> FAQs
				</h5>
			</div>
		</a>
		<div id="collapseState" class="collapse" aria-labelledby="headingState" data-parent="#accordion">
			<div class="card-body">
				<?=$FAQs[strtoupper($_GET['st'])]?>
			</div>
		</div>
	</div>
<?php } ?>