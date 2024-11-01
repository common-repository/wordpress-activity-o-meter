<?php
/*
Copyright (C) 2010 Micro Formatica

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
global $wpdb;

?>
<div class="wrap">
<?php
global $wpdb;
$wpdb->show_errors();

$year = 0;
if (isset($_GET['year'])) {
       $year = $_GET['year'];
} else {
       $year = date("Y");
}

$month = 0;
if (isset($_GET['month'])) {
       $month = $_GET['month'];
} else {
       $month = date("n");
}

$lastdayofmonth = date("d",strtotime("-1 second",strtotime("+1 month",strtotime($month."/01/". $year. " 00:00:00")))); 

if (current_user_can('manage_options')) {
add_option($month . $year . "-activitynotes", "", "", "no");
if (isset($_POST['notes'])) {
update_option ($month . $year . "-activitynotes", wordpressactivityometer_replacebr($_POST['notes']));
}
}
?>

<!-- jQuery -->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>

<!-- required plugins -->
<!--[if IE]><script type="text/javascript" src="scripts/jquery.bgiframe.js"></script><![endif]-->
<!-- jquery.datePicker.js -->

<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/jquery.datePicker.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/date.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/jquery.tools.min.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/flot/excanvas.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/flot/jquery.flot.js"></script>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/jquery.cookie.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/date.css" />
<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/flot/customflotcss.css" />
<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/jquery-ui-1.7.2.custom.css" />

<div style="background-image: url('<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/sep2.png'); margin-left: 10px;">
<div style="background-image: url('<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/sep3.png'); height: 127px; width: 100%;">

</div>

<h2 style="margin-left: 500px; margin-top: -5px; color: white;">Wordpress activity-o-meter</h2>

</div>
<a href="http://www.microformatica.com/internet-services/buy-support" style="z-index: 400; position: absolute; margin-top: -170px; margin-left: 10px;"><img src="<?php echo get_bloginfo('siteurl'); ?>/wp-content/plugins//wordpress-activity-o-meter/logo.gif.png"></a>

<br />

<div id="tooltip">&nbsp;</div>
<br />
<div style="margin-left: 10px; margin-top: -25px;" class="">
<br />
<div class="wrap"><h2>General activity of this month (<?php echo date("F", mktime(0, 0, 0, $month, 1, $year)) . " $year" ; ?>)</h2>

<div id="accordion">
<h3 href="" class="rollhead"><a href="#">Notes on this month.</a></h3>
<div style="height: 245px;">

<form action="<?php echo get_bloginfo('siteurl') ?>/wp-admin/admin.php?page=wordpressactivityometer<?php echo "&month=$month&year=$year"; ?>" method="post" name="notes">
<textarea rows="10" cols="100" style="width: 100%" class="theEditor" name="notes"><?php echo wordpressactivityometer_resetencap(get_option ($month . $year . "-activitynotes")); ?></textarea><br />

<?php if (current_user_can('manage_options')):?>
<input type="submit" value="Set notes of <?php echo date("F") . " " . date("Y"); ?>"/> 
<?php endif; ?>
<br /><br /><br /><br /><br />
</form></div>
<h3 href="" class="rollhead"><a href="#">Selected users in graph.</a></h3>
<div class="activityusers">
</div>

</div>
</div>
<br />

<div>
<div id="placeholder" style="width:600px;height:400px;"></div>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/wordpress-activity-o-meter/flot/customflotjs.js"></script>
<script id="source" language="javascript" type="text/javascript">
<?php

$arr_daysinmonth = array();

for ($day = 1; $day <= $lastdayofmonth; $day++) {
$arr_daysinmonth[$day] = 0;
}

$finaltext = "
(function($) { 

$(function () {
    var datasets = {
";

$finaltext .= "\"cumulative\": { label: 'all users', data: [" ;
$second_results = $wpdb->get_results("SELECT DISTINCT DAY(post_date) as dayposted, count(ID) as postssum  FROM " . $wpdb->prefix . "posts WHERE post_type = 'post' AND post_status = 'publish' AND post_date BETWEEN '$year-$month-01 00:00:00' AND '$year-$month-$lastdayofmonth 23:59:59' GROUP BY DAY(post_date)");


foreach ($second_results as $secondresult) {
$arr_daysinmonth[$secondresult->dayposted] = $secondresult->postssum;

}
$i = 1;
foreach ($arr_daysinmonth as $dayinmonth) {
$finaltext .= "[$i, $dayinmonth],";
$i++;
}
unset ($arr_daysinmonth);

$finaltext = substr_replace($finaltext ,"",-1);
$finaltext .= "] },";

$results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "users WHERE user_status = 0");
foreach ($results as $result) {

$arr_daysinmonth = array();

for ($day = 1; $day <= $lastdayofmonth; $day++) {
$arr_daysinmonth[$day] = 0;
}

$finaltext .= "\"" . $result->ID . "\": { label: '$result->display_name', data: [" ;

$i = 1; 
$second_results = $wpdb->get_results("SELECT DISTINCT DAY(post_date) as dayposted, count(ID) as postssum  FROM " . $wpdb->prefix . "posts WHERE post_author = $result->ID AND post_type = 'post' AND post_status = 'publish' AND post_date BETWEEN '$year-$month-01 00:00:00' AND '$year-$month-$lastdayofmonth 23:59:59' GROUP BY DAY(post_date)");

foreach ($second_results as $secondresult) {
$arr_daysinmonth[$secondresult->dayposted] = $secondresult->postssum;
}
foreach ($arr_daysinmonth as $dayinmonth) {
$finaltext .= "[$i, $dayinmonth],";
$i++;
}

unset ($arr_daysinmonth);

$finaltext = substr_replace($finaltext ,"",-1);
$finaltext .= "] },";

}

$finaltext = substr_replace($finaltext ,"",-1);

echo $finaltext;

?>
 };

var i = 0;
    $.each(datasets, function(key, val) {
        val.color = i;
        ++i;
    });
    
    // insert checkboxes 
    var choiceContainer = $("div.activityusers");

    $.each(datasets, function(key, val) {

        choiceContainer.append('<br/><input type="checkbox" name="' + key +
                               '" id="graphid' + key + '">' +
                               '<label for="graphid' + key + '">'
                                + val.label + '</label>');
    });

    choiceContainer.find("input").click(function () {

ProcessGraphCookie (datasets);
plotAccordingToChoices();

});

var placewidth = $("#placeholder").parent().width();
$("#placeholder").css('width', (placewidth - 10) + 'px');


    function plotAccordingToChoices() {
        var data = [];

        choiceContainer.find("input:checked").each(function () {
            var key = $(this).attr("name");
            if (key && datasets[key])
                data.push(datasets[key]);
        });

        if (data.length > 0)
            $.plot($("#placeholder"), data, {
                series: {
                   lines: { show: true },
                   points: { show: true } },
		grid: { hoverable: true },
 		yaxis: { min: 0 },
                xaxis: { tickDecimals: 0, min: 1, max: <?php echo $lastdayofmonth; ?>, ticks: <?php echo $lastdayofmonth; ?> },
  
            });
    }

    GetGraphCookie ();
    plotAccordingToChoices();

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

</script>

<script type="text/javascript">
Date.format = 'yyyy-mm-dd';
$(function()
{
	$('.date-pick').datePicker({startDate:'1990-01-01'});
});

<?php if (get_option('wordpressactivityometer-tooltip') == "on") : ?>
        $("form a").tooltip({tip: '#tooltip', effect: 'bouncy'});
        $("form label").tooltip({tip: '#tooltip', effect: 'bouncy'});
});
<?php endif;?>
</script>

<script type="text/javascript">
/* <![CDATA[ */
var lang = 'en';
tinyMCEPreInit = {
	base : "<?php echo get_bloginfo('siteurl'); ?>/wp-includes/js/tinymce",
	suffix : "",
	query : "ver=327-1235",
	mceInit : {mode:"specific_textareas", editor_selector:"theEditor", width:"100%", theme:"advanced",skin:"wp_theme", theme_advanced_buttons1:"bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,wp_more,|,spellchecker,fullscreen,wp_adv", theme_advanced_buttons2:"formatselect,underline,justifyfull,forecolor,|,pastetext,pasteword,removeformat,|,media,charmap,|,outdent,indent,|,undo,redo,wp_help", theme_advanced_buttons3:"", theme_advanced_buttons4:"", language:"en", spellchecker_languages:"+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv", theme_advanced_toolbar_location:"top", theme_advanced_toolbar_align:"left", theme_advanced_statusbar_location:"bottom", theme_advanced_resizing:"1", theme_advanced_resize_horizontal:"", dialog_type:"modal", relative_urls:"", remove_script_host:"", convert_urls:"", apply_source_formatting:"", remove_linebreaks:"1", gecko_spellcheck:"1", entities:"38,amp,60,lt,62,gt", accessibility_focus:"1", tabfocus_elements:"major-publishing-actions", media_strict:"", paste_remove_styles:"1", paste_remove_spans:"1", paste_strip_class_attributes:"all", wpeditimage_disable_captions:"", plugins:"safari,inlinepopups,spellchecker,paste,wordpress,media,fullscreen,wpeditimage,wpgallery,tabfocus"},
	load_ext : function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
};
/* ]]> */
</script>

<script type="text/javascript" src="<?php echo get_bloginfo('siteurl'); ?>/wp-includes/js/tinymce/wp-tinymce.js"></script>
<script type="text/javascript">
<?php 

global $language;
$language = "en";
include (ABSPATH . WPINC . "/js/tinymce/langs/wp-langs.php"); 

echo $lang;

?>
</script>

<script type="text/javascript">
/* <![CDATA[ */
tinyMCEPreInit.go();
tinyMCE.init(tinyMCEPreInit.mceInit);
/* ]]> */

jQuery(document).ready(function(){
	$("#accordion h3 a").css("color", $("#adminmenu a").css("color"));
	$("#accordion h3").css("font-family", $("#adminmenu a.menu-top").css("font-family"));
	$("#accordion h3").css("background-color", $("#adminmenu a.menu-top").css("background-color"));
	$("#accordion h3").css("background-image","none");

	$("#accordion").accordion({
			collapsible: true,
			autoHeight: false,
			navigation: true,
			
		});
	$("#accordion").accordion("activate" , false);

	$('#accordion .head').click(function() {
		$(this).next().toggle('fast');
		$("#accordion").accordion("resize");
	}).next().hide();
});
</script>

<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
<div style="text-align: right;"><br /><br /><br /><br /><br /><br /><span style=""><i>Graphs by <a href="http://code.google.com/p/flot/">Flot</a> 0.6 </i></span><br/> </div>

</div>


