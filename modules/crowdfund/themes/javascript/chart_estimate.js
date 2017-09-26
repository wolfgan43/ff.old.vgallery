ff.crowdfund_estimate.chart = (function () {
	var chartData			= {};
	var target				= "";
	var smartUrl			= "";
	var containerTarget		= "";

	var that = 
	{ // publics
		"init" : function(params) 
		{
			smartUrl = (params.smartUrl !== undefined ?params.smartUrl :"");
			target = (params.target !== undefined ?params.target :"");
			containerTarget = (params.containerTarget !== undefined ?params.containerTarget :"");
			
			
			ff.pluginLoad("AmCharts", "http://www.amcharts.com/lib/amcharts.js", function()
			{
				// generate some data
				jQuery.getJSON("/services/chart/" + smartUrl, function(data)  
				{
					console.log(data);
					chartData = data["value"];
					
				});
			});
		},
		"draw" : function (selContainerTarget, selChartData) 
		{
			
			if(selContainerTarget === undefined)
			{
				selContainerTarget = containerTarget;
			}
			if(selChartData === undefined)
			{
				selChartData = chartData;
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
			chart.dataProvider = selChartData;
			chart.categoryField = "date";
			chart.balloon.bulletSize = 5;
			chart.columnWidth = 7000; // we set column width to 20 so that each daily column spans 20 categories (hours)
			chart.numberFormatter = {precision:2, decimalSeparator:',', thousandsSeparator:'.'};
	//		chart.startDuration = 1;

		// AXES
			// category
			var categoryAxis = chart.categoryAxis; 
			categoryAxis.parseDates = true;
			categoryAxis.minPeriod = "hh"; 
			categoryAxis.dashLength = 1;
			categoryAxis.gridAlpha = 0.15;
			categoryAxis.axisColor = "#DADADA";

			// value                
			var valueAxis = new AmCharts.ValueAxis();
			valueAxis.dashLength = 1;
			valueAxis.stackType = "regular";
			valueAxis.gridAlpha = 0.1;
			valueAxis.axisAlpha = 0;
			chart.addValueAxis(valueAxis);

		// graph

			// first graph utile netto
			
				var graph = new AmCharts.AmGraph();
				graph.title = "Utile netto";
				graph.valueField = "net_income";
				graph.stackable = false;
				graph.lineThickness = 2;
				graph.lineColor = "#5fb503";
				graph.balloonText = "[[title]]: [[value]]";
				chart.addGraph(graph);
			

			// second graph ricavi totali
			
				var graph = new AmCharts.AmGraph();
				graph.title = "Ricavi totali";
				graph.valueField = "total_revenue";
				graph.stackable = false;
				graph.lineThickness = 2;
				graph.lineColor = "#cccc00";
				graph.balloonText = "[[title]]: [[value]]";
				chart.addGraph(graph);
			

			// third graph obiettivo
			
				var graph = new AmCharts.AmGraph();
				graph.title = "Goal";
				graph.valueField = "goal";
				graph.stackable = false;
				graph.lineThickness = 2; 
				graph.lineColor = "#FF0000";
				graph.balloonText = "[[title]]: [[value]]"; 
				chart.addGraph(graph); 
			

			// fourth graph costo con opzione
			
				if(0)
				{
					var graph = new AmCharts.AmGraph();
					graph.title = "{_mod_crowdfund_chart_cost_title}";
					graph.valueField = "cost_united";
					graph.stackable = false; 
					if(chartData["params"]["column"])
					{
						graph.type = "column";
						graph.lineAlpha = 0;
						graph.fillAlphas = 1;
					} else
					{
						graph.lineThickness = 2;
					}
					graph.lineColor = "#C72C95"; 
					graph.balloonText = "[[title]]: [[value]]"; 
					chart.addGraph(graph);
				

				var graph = new AmCharts.AmGraph();
				graph.title = "Costo anno 0";
				graph.valueField = "cost0";
				graph.type = "column";
				graph.lineAlpha = 0;
				graph.fillAlphas = 1;
				graph.lineColor = "#FF4D00";
				graph.balloonText = "[[title]]: [[value]]"; 
				chart.addGraph(graph);


				graph = new AmCharts.AmGraph();
				graph.title = "Costo anno 1";
				graph.valueField = "cost1";
				graph.type = "column";
				graph.lineAlpha = 0;
				graph.fillAlphas = 1;
				graph.lineColor = "#03C03C";
				graph.balloonText = "[[title]]: [[value]]"; 
				chart.addGraph(graph);


				graph = new AmCharts.AmGraph();
				graph.title = "Costo anno 2";
				graph.valueField = "cost2";
				graph.type = "column";
				graph.lineAlpha = 0;
				graph.fillAlphas = 1;
				graph.lineColor = "#3CB371";
				graph.balloonText = "[[title]]: [[value]]";
				chart.addGraph(graph);


				graph = new AmCharts.AmGraph();
				graph.title = "Costo anno 3";
				graph.valueField = "cost3";
				graph.type = "column";
				graph.lineAlpha = 0;
				graph.fillAlphas = 1;
				graph.lineColor = "#808000";
				graph.balloonText = "[[title]]: [[value]]"; 
				chart.addGraph(graph);

				graph = new AmCharts.AmGraph();
				graph.title = "Costo anno 4";
				graph.valueField = "cost4";
				graph.type = "column";
				graph.lineAlpha = 0;
				graph.fillAlphas = 1;
				graph.lineColor = "#30D5C8";
				graph.balloonText = "[[title]]: [[value]]"; 
				chart.addGraph(graph);

				graph = new AmCharts.AmGraph(); 
				graph.title = "Costo anno 5";
				graph.valueField = "cost5";
				graph.type = "column";
				graph.lineAlpha = 0;
				graph.fillAlphas = 1;
				graph.lineColor = "#531B00";
				graph.balloonText = "[[title]]: [[value]]"; 
				chart.addGraph(graph);

				graph = new AmCharts.AmGraph();
				graph.title = "Costo anno 6";  
				graph.valueField = "cost6";
				graph.type = "column";
				graph.lineAlpha = 0;
				graph.fillAlphas = 1;
				graph.lineColor = "#FF6088"; 
				graph.balloonText = "[[title]]: [[value]]"; 
				chart.addGraph(graph);

			}

		// CURSOR
			var chartCursor = new AmCharts.ChartCursor();
			chartCursor.cursorPosition = "mouse";
			chartCursor.categoryBalloonDateFormat = "YYYY";
			chart.addChartCursor(chartCursor);
			 
		// LEGEND                  
			var legend = new AmCharts.AmLegend();
			legend.borderAlpha = 0.2;
			legend.valueWidth = 0;
			legend.horizontalGap = 10;
			chart.addLegend(legend);

		// WRITE
			chart.write(target); 
			
		}
	};
	return that;
})();


