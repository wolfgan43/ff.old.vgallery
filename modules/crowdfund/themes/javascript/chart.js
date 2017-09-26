if(!ff.crowdfund)
	ff.crowdfund = {};

ff.crowdfund.chart = (function () {
	var chartData			= {};
	var smartUrl			= "";
	var containerTarget		= "";
	var estimateBudget		= "";
	var columnDecision		= "";
	var onlyPositive		= "";

	var that = 
	{ // publics
		"init" : function(params) 
		{  
			smartUrl = (params.smartUrl !== undefined ?params.smartUrl :"");
			containerTarget = (params.containerTarget !== undefined ?params.containerTarget :"");
			estimateBudget = (params.estimateBudget !== undefined ?params.estimateBudget :"");
			columnDecision = (params.decision !== undefined ?params.decision :"");
			onlyPositive = (params.positive !== undefined ?params.positive :"");
			
			ff.pluginLoad("AmCharts", "http://www.amcharts.com/lib/amcharts.js", function()
			{
				// generate data
				jQuery.getJSON("/services/chart/" + smartUrl, function(data)  
				{
					chartData = data["value"];
					ff.crowdfund.chart.draw();
				});
			});
		},
		"draw" : function (selContainerTarget, selChartData, selEstimateBudget, selGraphColumnDecision, selOnlyPositiveDecision)  
		{
			if(selContainerTarget === undefined)
			{
				selContainerTarget = containerTarget;
			}
			if(selChartData === undefined)
			{
				selChartData = chartData;
			}
			if(selEstimateBudget === undefined)
			{
				selEstimateBudget = estimateBudget;
			}
			if(selGraphColumnDecision === undefined)
			{
				selGraphColumnDecision = columnDecision;
			}
			if(selOnlyPositiveDecision === undefined)
			{
				selOnlyPositiveDecision = onlyPositive;
			}
			
		// SERIAL CHART    
			chart = new AmCharts.AmSerialChart();  

			chart.autoMarginOffset = 5;
			chart.marginBottom = 0;
			chart.pathToImages = "http://www.amcharts.com/lib/images/";
			chart.zoomOutButton = 
			{
				backgroundColor: '#000000',
				backgroundAlpha: 0.15
			};
			chart.color = "#ffffff";
			chart.dataProvider = selChartData;
			chart.categoryField = "date";
			chart.balloon.bulletSize = 5;
			chart.columnWidth = 7000; // we set column width to 20 so that each daily column spans 20 categories (hours)
			chart.numberFormatter = {precision:2, decimalSeparator:',', thousandsSeparator:'.'};
//			chart.startDuration = 1; 

		// AXES
			// category 
			var categoryAxis = chart.categoryAxis; 
			categoryAxis.gridPosition = "start";
			categoryAxis.equalSpacing = true;
			categoryAxis.parseDates = false; 
//			categoryAxis.minPeriod = "YYYY";
			categoryAxis.dashLength = 1;
			categoryAxis.gridAlpha = 0.15;
			categoryAxis.axisColor = "#DADADA";

			// value                
			var valueAxis = new AmCharts.ValueAxis();
			valueAxis.dashLength = 1;
			if(selGraphColumnDecision == true)
			{
				valueAxis.stackType = "regular";
			}
			valueAxis.gridAlpha = 0.1;
			valueAxis.axisAlpha = 0;
			valueAxis.axisColor = "#ffffff";
			valueAxis.color = "#ffffff";
			if(selOnlyPositiveDecision == true)    
			{
				valueAxis.minimum = 0;
			}
			chart.addValueAxis(valueAxis);

		// graph
			// first graph utile netto
			var graph = new AmCharts.AmGraph();
			graph.title = "Net income";
			graph.valueField = "net_income";
			if(selGraphColumnDecision == true)
			{
				graph.stackable = false;
			}
			graph.lineThickness = 2;
			graph.lineColor = "#92b135";  
			graph.balloonText = "[[title]]: [[value]]"; 
			chart.addGraph(graph);

			
			// second graph ricavi totali
			var graph = new AmCharts.AmGraph();
			graph.title = "Total revenue";
			graph.valueField = "total_revenue";
			if(selGraphColumnDecision == true)
			{
				graph.stackable = false;
			}
			graph.lineThickness = 2;
			graph.lineColor = "#01B9FF";  
			graph.balloonText = "[[title]]: [[value]]"; 
			chart.addGraph(graph);


			// third graph obiettivo
			var graph = new AmCharts.AmGraph();
			graph.title = "Goal";
			graph.valueField = "goal";
			if(selGraphColumnDecision == true)
			{
				graph.stackable = false;
			}
			graph.lineThickness = 2; 
			graph.lineColor = "#f6853f";
			graph.balloonText = "[[title]]: [[value]]"; 
			chart.addGraph(graph); 
			

	
			// fourth graph costo con opzione
			if(selGraphColumnDecision == true)
			{
				
			//COLONNE COSTI
				if(selEstimateBudget) 
				{
					var graph = new AmCharts.AmGraph();
					graph.title = "Cost"; 
					graph.valueField = "cost_united";
					graph.stackable = false; 
					graph.type = "column";
					graph.lineAlpha = 0;
					graph.fillAlphas = 1;
					graph.lineColor = "#457da5"; 
					graph.balloonText = "[[title]]: [[value]]"; 
					chart.addGraph(graph);
				} else
				{

					var graph = new AmCharts.AmGraph();
					graph.title = "Costo anno 0";
					graph.valueField = "cost0";
					graph.type = "column";
					graph.lineAlpha = 0;
					graph.fillAlphas = 1;
					graph.lineColor = "#0b3b5a";
					graph.balloonText = "[[title]]: [[value]]"; 
					chart.addGraph(graph);


					graph = new AmCharts.AmGraph();
					graph.title = "Costo anno 1";
					graph.valueField = "cost1";
					graph.type = "column";
					graph.lineAlpha = 0;
					graph.fillAlphas = 1;
					graph.lineColor = "#18567e";
					graph.balloonText = "[[title]]: [[value]]"; 
					chart.addGraph(graph);


					graph = new AmCharts.AmGraph();
					graph.title = "Costo anno 2";
					graph.valueField = "cost2";
					graph.type = "column";
					graph.lineAlpha = 0;
					graph.fillAlphas = 1;
					graph.lineColor = "#1c6a9b";
					graph.balloonText = "[[title]]: [[value]]";
					chart.addGraph(graph);


					graph = new AmCharts.AmGraph();
					graph.title = "Costo anno 3";
					graph.valueField = "cost3";
					graph.type = "column";
					graph.lineAlpha = 0;
					graph.fillAlphas = 1;
					graph.lineColor = "#457da5";
					graph.balloonText = "[[title]]: [[value]]"; 
					chart.addGraph(graph);

					graph = new AmCharts.AmGraph();
					graph.title = "Costo anno 4";
					graph.valueField = "cost4";
					graph.type = "column";
					graph.lineAlpha = 0;
					graph.fillAlphas = 1;
					graph.lineColor = "#6095ba";
					graph.balloonText = "[[title]]: [[value]]"; 
					chart.addGraph(graph);

					graph = new AmCharts.AmGraph(); 
					graph.title = "Costo anno 5";
					graph.valueField = "cost5";
					graph.type = "column";
					graph.lineAlpha = 0;
					graph.fillAlphas = 1;
					graph.lineColor = "#9abcd4";
					graph.balloonText = "[[title]]: [[value]]"; 
					chart.addGraph(graph);

					graph = new AmCharts.AmGraph();
					graph.title = "Costo anno 6";  
					graph.valueField = "cost6";
					graph.type = "column";
					graph.lineAlpha = 0;
					graph.fillAlphas = 1;
					graph.lineColor = "#d7e5ef";  
					graph.balloonText = "[[title]]: [[value]]"; 
					chart.addGraph(graph);
				}
			} else
			{
				var graph = new AmCharts.AmGraph();
				graph.title = "Cost"; 
				graph.valueField = "cost";
				graph.lineThickness = 2;
				graph.lineColor = "#457da5"; 
				graph.balloonText = "[[title]]: [[value]]"; 
				chart.addGraph(graph);
			}
//			}

		// CURSOR
			var chartCursor = new AmCharts.ChartCursor();
			chartCursor.cursorPosition = "mouse";
			chartCursor.categoryBalloonDateFormat = "YYYY";
			chart.addChartCursor(chartCursor);
 		 
		// LEGEND                  
			var legend = new AmCharts.AmLegend();
			legend.borderAlpha = 0.2;
			legend.color = "#ffffff";
			if(selEstimateBudget)
			{
				legend.valueWidth = 00;
				legend.valueText=" "
			} else
			{
				legend.valueWidth = 70;  
			}
			legend.horizontalGap = 10;
			chart.addLegend(legend);
			// WRITE 
			chart.write(containerTarget + "-chart"); 
			$("tspan:contains('chart by amcharts.com')").closest("g").remove();
		}
	};
	return that; 
})();


ff.crowdfund.highChart = (function () {
	var smartUrl			= "";
	var containerTarget		= "";
	var years				= "";

	var that = 
	{ // publics
		"init" : function(params)    
		{  
			smartUrl = (params.smartUrl !== undefined ?params.smartUrl :"");
			containerTarget = (params.containerTarget !== undefined ?params.containerTarget :"");
			
			jQuery.getJSON("/services/chart/" + smartUrl, function(data)  
			{
				years = data["value"]["year"];
				
				$('#' + containerTarget + '-chart').highcharts({     
					chart: {
						type: 'line',  
						spacingBottom: 10,
						backgroundColor: '#244962',
						animation:'linear'
					},
					title: {
						text: ''
					},
					subtitle: {
						text: '',
						floating: true,
						align: 'right',
						verticalAlign: 'bottom',  
						y: 15
					},
					legend: {
						layout: 'vertical',
						backgroundColor: 'rgba(255,255,255,0.8)',   
						align: 'left',
						verticalAlign: 'top',
						x: 20,
						y: 0,
						floating: true,
				//		backgroundColor: '#FFFFFF',
						borderWidth: 1
					},
					xAxis: {
						categories: years,
						lineColor: '#FFFFFF',
						labels: {
							style: {
								color: '#FFFFFF'
							}
						}
					},
					yAxis: {   
						min: data["min_net_income"],
						lineColor: '#FFFFFF',
						title: {
							text: ''
						},
						labels: {
							style: {
								color: '#FFFFFF'
							},
							formatter: function() {
								return this.value;
							}
						}
					},
					tooltip: {
						formatter: function() {
							return '<b>'+ this.series.name +'</b><br/>'+
							this.x +': '+ this.y;
						}
					},
					plotOptions: { 
						area: {
							fillOpacity: 0.5
						}
					},
					credits: {
						enabled: false
					},
					 series: [{
						name: 'Goal',
						color: '#f6853f',
						data: JSON.parse( "[" + data["value"]["goal"] + "]" )
					}, {
						name: 'Cost',
						color: '#457da5',
						data: JSON.parse( "[" + data["value"]["cost"] + "]" )  
					}, {
						name: 'Total Revenue',
						color: '#01B9FF',
						data: JSON.parse( "[" + data["value"]["total_revenue"] + "]" )
					}, {
						name: 'Net income',
						color: '#92b135',
						data: JSON.parse( "[" + data["value"]["net_income"] + "]" )  
					}]
				});
				
			});
		}
	};
	return that; 
})();