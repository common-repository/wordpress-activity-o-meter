(function($) { 

  function showTooltip(x, y, contents) {
        $('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 12,
            border: '1px solid #fdd',
            padding: '2px',
            'background-color': '#fee',
            'background-image': 'none',
	    'color': 'black',
            opacity: 0.80
        }).appendTo("body").fadeIn(200);
    }

    var previousPoint = null;
    $("#placeholder").bind("plothover", function (event, pos, item) {
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

            if (item) {
                if (previousPoint != item.datapoint) {
                    previousPoint = item.datapoint;
                    
                    $("#tooltip").remove();
                    var x = item.datapoint[0].toFixed(2),
                        y = item.datapoint[1].toFixed(2);
                    
                    showTooltip(item.pageX, item.pageY,
                                Math.round(y) + " posts by " + item.series.label);
                }
            }
            else {
                $("#tooltip").remove();
                previousPoint = null;            
            }
        
    });

function ProcessGraphCookie (datasets) {
var cookievalue = "{";

$.each (datasets, function(key, val) {
cookievalue = cookievalue + "'#graphid" + key + "': '" + $("#graphid" + key).attr("checked") + "',";

});
cookievalue = cookievalue.slice(0, -1);

cookievalue = cookievalue + "}";
$.cookie("plotgraphs", cookievalue);
}


function GetGraphCookie() {
if ($.cookie ("plotgraphs") != null ) {
eval ("var temparr = " + $.cookie ("plotgraphs") + ";");
} else {
var temparr = {"#graphidcumulative":"true"};
}

$.each (temparr ,function (key, val) {
switch (val) {
case "true":
$(key).attr("checked", "checked");
break;

case "false":
$(key).attr("checked", "");

}
});

}


})(jQuery);

