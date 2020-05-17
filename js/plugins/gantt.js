/**
 * @author Dimitry Kudrayvtsev
 * @version 2.1
 */

d3.gantt = function() {
    //var FIT_TIME_DOMAIN_MODE = "fit";
    //var FIXED_TIME_DOMAIN_MODE = "fixed";
    
    var margin = {
			top : 20,
			right : 40,
			bottom : 20,
			left : 150
    };
    var selector = 'body';
    //var timeDomainStart = d3.time.day.offset(new Date(),-3);
    //var timeDomainEnd = d3.time.hour.offset(new Date(),+3);
    //var timeDomainMode = FIT_TIME_DOMAIN_MODE;// fixed or fit
    var taskTypes = [];
    var taskStatus = {"RUNNING":"bar-running"};
    var height = document.body.clientHeight - margin.top - margin.bottom-5;
    var width = document.body.clientWidth - margin.right - margin.left-5;

    //var tickFormat = "%H:%M";

    var keyFunction = function(d) {
			return d.id;
    };

    var rectTransform = function(d) {
			return "translate(" + x(d.startDate) + "," + y(d.taskName) + ")";
    };

    var y = d3.scaleBand().domain(taskTypes.sort(naturalSort)).rangeRound([ 0, height - margin.top - margin.bottom], .1);
    var yAxis = d3.axisLeft(yScale).tickSize(0);

    var x = d3.scaleTime().range([0, width]).clamp(true);
    var x2 = d3.scaleTime().range([0, width]);
    var x0 = [d3.timeDay.offset(new Date(), -7), new Date()];

    x.domain(x0);
    x2.domain(x0);

    var xAxis = d3.axisBottom(x);

    

    var initAxis = function() {
			x = d3.scaleTime().range([0, width]).clamp(true);
			y = d3.scaleBand().domain(taskTypes.sort(naturalSort)).rangeRound([ 0, height - margin.top - margin.bottom], .1);

			x.domain(x0);

			xAxis = d3.axisBottom(x);
			yAxis = d3.axisLeft(y).tickSize(0);
    };
    
    function gantt(tasks) {
	
			initAxis();
	
			//Define the div for the tooltip
			var div = d3.select("body").append("div")
				.attr("class", "tooltip")
				.style("opacity", 0);

			var svg = d3.select(selector)
				.append("svg")
				.attr("class", "chart")
				.attr("width", width + margin.left + margin.right)
				.attr("height", height + margin.top + margin.bottom)
				.append("g")
			    .attr("class", "gantt-chart")
				.attr("width", width + margin.left + margin.right)
				.attr("height", height + margin.top + margin.bottom)
				.attr("transform", "translate(" + margin.left + ", " + margin.top + ")");
				
      svg.selectAll(".chart")
				.data(tasks, keyFunction).enter()
				.append("rect")
				.filter(function(d) { return y(d.taskName);})
				//.attr("rx", 5)
			    //.attr("ry", 5)
				.attr("class", function(d){ 
					if(taskStatus[d.status] == null){ return "bar";}
					return taskStatus[d.status];
					}) 
				.attr("y", 0)
				.attr("transform", rectTransform)
				.attr("height", function(d) { return y.bandwidth(); })
				.attr("width", function(d) { 
					return x(d.endDate) - x(d.startDate); 
				})
				.on("mouseover", function(d) {
					div.transition()
						.duration(200)
						.style("opacity", .9);
					div.html('<br />' + d.label + '<br /><br />Start: ' + d.startDate.toLocaleString() + '<br />End: ' + d.endDate.toLocaleString())
						.style("left", (d3.event.pageX - 150) + "px")
						.style("top", (d3.event.pageY - 100) + "px");
				})
				.on("mouseout", function(d){
					div.transition()
						.duration(500)
						.style("opacity", 0);
				});
				 
	 		svg.append("g")
				.attr("class", "x--axis")
				.attr("transform", "translate(0, " + (height - margin.top - margin.bottom) + ")")
				.transition()
				.call(xAxis);
			 
			svg.append("g").attr("class", "y--axis").transition().call(yAxis);

			var zoom = d3.zoom()
		 		.scaleExtent([1, Infinity])
				.translateExtent([[0, 0], [width, height]])
				.extent([[0, 0]], [width, height])
				.on("zoom", zoomed);

			d3.select('svg').append("rect")
				.attr("class", "zoom")
				.attr("style", "{cursor: move; fill:none; pointer-events: all;}")
				.attr("width", width)
				.attr("height", height)
				.attr("transform", "translate(" + margin.left + "," + margin.top + ")")
				.call(zoom);

			return gantt;
    };
    
    function zoomed() {
    	var t = d3.event.transform;
    	xScale.domain(t.rescaleX(x2).domain());
    	redrawChart();
    }

    gantt.redraw = function(tasks) {

			initAxis();
	
      var svg = d3.select(".chart");

      var ganttChartGroup = svg.select(".gantt-chart");
      var rect = ganttChartGroup.selectAll("rect").data(tasks, keyFunction);
        
      rect.enter()
      	.insert("rect",":first-child")
        //.attr("rx", 5)
        //.attr("ry", 5)
	 			.attr("class", function(d){ 
	     		if(taskStatus[d.status] == null){ return "bar";}
	     		return taskStatus[d.status];
	     	}) 
	 			.transition()
	 			.attr("y", 0)
	 			.attr("transform", rectTransform)
	 			.attr("height", function(d) { return y.bandwidth(); })
	 			.attr("width", function(d) { 
	     		return x(d.endDate) - x(d.startDate);  
	     	});

      rect.transition()
      	.filter(function(d){ return y(d.taskName);})
        .attr("transform", rectTransform)
				.attr("height", function(d) { return y.bandwidth(); })
				.attr("width", function(d) { 
				   return x(d.endDate) - x(d.startDate);  
				});
        
			rect.exit().remove();

			svg.select(".x--axis").transition().call(xAxis);
			svg.select(".y--axis").transition().call(yAxis);
			
			return gantt;
    };

    function redrawChart(){
    	var t = svg.transition().duration(750);
    	svg.selectAll(".x--axis").call(xAxis);
    	svg.selectAll("rect")
    		//.transition(t)
    		//.attr("rx", 5)
    		//.attr("ry", 5)
    		.attr("y", 0)
    		.attr("transform", rectTransform)
    		.attr("height", function(d) { return yScale.bandwidth(); })
    		.attr("width", function(d) {
    			return (x(d.endDate) - x(d.startDate));
    		});
    }

		gantt.margin = function(value) {
			if (!arguments.length)
			  return margin;
			margin = value;
			return gantt;
		};

    gantt.timeDomain = function(d1, d2) {
			x0 = [d1, d2];
			x.domain(x0);
			redrawChart();
    };

    
    gantt.taskTypes = function(value) {
			if (!arguments.length)
			    return taskTypes;
			taskTypes = value;
			return gantt;
    };
    
    gantt.taskStatus = function(value) {
			if (!arguments.length)
			    return taskStatus;
			taskStatus = value;
			return gantt;
    };

    gantt.width = function(value) {
			if (!arguments.length)
			    return width;
			width = +value;
			return gantt;
    };

    gantt.height = function(value) {
			if (!arguments.length)
			    return height;
			height = +value;
			return gantt;
    };

    gantt.selector = function(value) {
			if (!arguments.length)
			    return selector;
			selector = value;
			return gantt;
    };

    return gantt;
};