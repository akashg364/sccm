/* Chart One*/
	var chart = Highcharts.chart('container1', {

    xAxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    },

    series: [{
        type: 'column',
        colorByPoint: true,
        data: [29.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
        showInLegend: false
    }]

	});


	$('#plain').click(function () {
		chart.update({
			chart: {
				inverted: false,
				polar: false
			},
			subtitle: {
				text: 'Plain'
			}
		});
	});

	$('#inverted').click(function () {
		chart.update({
			chart: {
				inverted: true,
				polar: false
			},
			subtitle: {
				text: 'Inverted'
			}
		});
	});

	$('#polar').click(function () {
		chart.update({
			chart: {
				inverted: false,
				polar: true
			},
			subtitle: {
				text: 'Polar'
			}
		});
	});
	
	/* Chart Two */
	
	$(document).ready(function () {

		// Build the chart
		Highcharts.chart('container2', {
			chart: {
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				type: 'pie'
			},
			tooltip: {
				pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
			},
			plotOptions: {
				pie: {
					allowPointSelect: true,
					cursor: 'pointer',
					dataLabels: {
						enabled: false
					},
					showInLegend: true
				}
			},
			series: [{
				name: 'Brands',
				colorByPoint: true,
				data: [{
					name: 'Microsoft Internet Explorer',
					y: 50
				}, {
					name: 'Chrome',
					y: 50,
					sliced: true,
					selected: true
				}]
			}]
		});
	});
	
	/* Chart Three */
	Highcharts.chart('container3', {
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: 0,
			plotShadow: false
		},
		title: {
			text: 'Browser<br>shares<br>2015',
			align: 'center',
			verticalAlign: 'middle',
			y: 40
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		},
		plotOptions: {
			pie: {
				dataLabels: {
					enabled: true,
					distance: -50,
					style: {
						fontWeight: 'bold',
						color: 'white'
					}
				},
				startAngle: -90,
				endAngle: 90,
				center: ['50%', '75%']
			}
		},
		series: [{
			type: 'pie',
			name: 'Browser share',
			innerSize: '50%',
			data: [
				['Firefox',   80],
				['Opera',     20],
				{
					name: 'Proprietary or Undetectable',
					y: 0.2,
					dataLabels: {
						enabled: false
					}
				}
			]
		}]
	});