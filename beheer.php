<?php
/*
Plugin Name: Wordpress activity-o-meter
Plugin URI: http://www.microformatica.com/internet-services/wordpress-addin-wordpress-activity-o-meter
Description: Wordpress activity o meter measures the activity of your users
Author: Micro Formatica
Version: 1
Author URI: http://www.microformatica.com
*/

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

global $wordpressactivityometer_dir;
$wordpressactivityometer_dir = WP_PLUGIN_DIR . '/wordpress-activity-o-meter';

add_action('admin_init', 'wordpressactivityometer_admin_init');
add_action('admin_menu', 'wordpressactivityometer_admin_actions');
add_action('wp_dashboard_setup', 'wordpressactivityometer_add_dashboard_widgets' );


function wordpressactivityometer_admin_init () {
wp_register_script ('excanvasflot', WP_PLUGIN_URL . '/wordpress-activity-o-meter/flot/excanvas.min.js');
wp_register_script ('flot', WP_PLUGIN_URL . '/wordpress-activity-o-meter/flot/jquery.flot.min.js');
wp_register_script ('jquerycookie', WP_PLUGIN_URL . '/wordpress-activity-o-meter/jquery.cookie.js');

wp_enqueue_script ('jquery');
wp_enqueue_script ('excanvasflot');
wp_enqueue_script ('flot');
wp_enqueue_script ('jquerycookie');
}

function wordpressactivityometer_admin_actions()
{
wp_enqueue_script('common');
wp_enqueue_script('wp-lists');
wp_enqueue_script('postbox');
wp_enqueue_script ('jquery');
wp_enqueue_script ('excanvasflot');
wp_enqueue_script ('flot');
wp_enqueue_script ('jquerycookie');

$page = add_menu_page("Wordpress activity o meter | This month", "Activity-o-meter", 1,"wordpressactivityometer", "wordpressactivityometer_menu");

if ( current_user_can('manage_options') ) {
add_submenu_page ("wordpressactivityometer","Wordpress activity-o-meter | Activity, by month", "Activity, by month", 1, "wordpressactivityometerbymonth", "wordpressactivityometerbymonth_menu");
add_submenu_page ("wordpressactivityometer","Wordpress activity-o-meter | Activity, custom selection", "Activity, custom selection", 1, "wordpressactivityometercustomsel", "wordpressactivityometercustomsel_menu");
add_submenu_page ("wordpressactivityometer","Wordpress activity-o-meter | Activity, comments", "Activity, comments", 1, "wordpressactivityometercomments", "wordpressactivityometercomments_menu");
add_submenu_page ("wordpressactivityometer","Wordpress activity-o-meter | Activity, impact", "Activity, impact", 1, "wordpressactivityometerimpact", "wordpressactivityometerimpact_menu");

}

}

function wordpressactivityometer_menu() {
add_action( 'admin_init', 'register_wordpressactivityometer_settings' );
include "wordpressactivityometer-admin.php";
}

function wordpressactivityometerbymonth_menu () {
include "wordpressactivityometer-bymonth.php";
}

function wordpressactivityometercustomsel_menu () {
include "wordpressactivityometer-customsel.php";
}

function wordpressactivityometercomments_menu () {
include "wordpressactivityometer-comments.php";
}

function wordpressactivityometerimpact_menu () {
include "wordpressactivityometer-impact.php";
}

function wordpressactivityometer_dashboard_widget_function() {
global $wpdb;

$month = date("n");
$year = date("Y");
$lastdayofmonth = date("d",strtotime("-1 second",strtotime("+1 month",strtotime(date("m")."/01/". date("Y"). " 00:00:00"))));
echo "<h3>" . date ("F", mktime(0,0,0, $month, 1, $year)) . "</h3>";

echo "
";
echo "<div id=\"placeholder\" style=\"width:425px;height:200px;\"></div>";
echo "<script id=\"source\" language=\"javascript\" type=\"text/javascript\">
";

$arr_daysinmonth = array();

for ($day = 1; $day <= $lastdayofmonth; $day++) {
$arr_daysinmonth[$day] = 0;
}

$finaltext .= "
(function($) {

var data = [];

$(function () {
    var datasets = {";

$finaltext .= "\"1\": { label: 'all users', data: [" ;
$second_results = $wpdb->get_results("SELECT DISTINCT DAY(post_date) as dayposted, count(ID) as postssum  FROM " . $wpdb->prefix . "posts WHERE post_type = 'post' AND post_status = 'publish' AND post_date BETWEEN '$year-$month-01 00:00:00' AND '$year-$month-$lastdayofmonth 23:59:59' GROUP BY DAY(post_date)");

$i = 1;

foreach ($second_results as $secondresult) {
$arr_daysinmonth[$secondresult->dayposted] = $secondresult->postssum;
}

foreach ($arr_daysinmonth as $dayinmonth) {
$finaltext .= "[$i, $dayinmonth],";
$i++;
}

unset ($arr_daysinmonth);

$finaltext = substr_replace($finaltext ,"",-1);
$finaltext .= "] } }; \n";

echo $finaltext;
echo "

var dashwidth = $(\"#wordpressactivityometer_dashboard_widget\").width() - 15;
$(\"#placeholder\").width (dashwidth);

data.push(datasets['1']);

plotme(data); 

});

function plotme(data) {
if (data.length > 0 && $(\"#placeholder\").height() > 0){
            $.plot($(\"#placeholder\"), data, {
                series: {
                   lines: { show: true },
                   points: { show: true } },
                grid: { hoverable: true },
                yaxis: { min: 0 },
                xaxis: { tickDecimals: 0, min: 1, max: $lastdayofmonth },

            }); 
}
}


$(\"#wordpressactivityometer_dashboard_widget\").find(\".handlediv\").click( function () { 
if ($(\"#placeholder\").height() == 0) { 

$(\"#placeholder\").css(\"height\", \"200px\"); 
plotme(data);

}
});

})(jQuery);

";


echo "</script>";
echo "<script type=\"text/javascript\" src=\"" . WP_PLUGIN_URL . "/wordpress-activity-o-meter/flot/customflotjs.js\"></script>
";
echo "\n<a href=\"" . get_bloginfo('siteurl') . "/wp-admin/admin.php?page=wordpressactivityometer\">See more activity ...</a>";



} 

function wordpressactivityometer_add_dashboard_widgets() {
	wp_add_dashboard_widget('wordpressactivityometer_dashboard_widget', 'Wordpress Activity-o-meter', 'wordpressactivityometer_dashboard_widget_function');	

	global $wp_meta_boxes;
	
	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
	$wordpressactivityometer_widget_backup = array('wordpressactivityometer_dashboard_widget' => $normal_dashboard['wordpressactivityometer_dashboard_widget']);
	unset($normal_dashboard['wordpressactivityometer_dashboard_widget']);
	$sorted_dashboard = array_merge($wordpressactivityometer_widget_backup, $normal_dashboard);
	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;

} 




function wordpressactivityometer_replacebr ( $string ) {
$return = "";

$return = $string;
//$return = str_replace("\r", "\n", $return);

$return = nl2br ($return);

$return = str_replace("</li><br />", "</li>", $return);
$return = str_replace("</ul><br />", "</ul>", $return);
$return = str_replace("<ul><br />", "<ul>", $return);
return $return;
}

function wordpressactivityometer_resetencap ( $string ) {
$return = $string;

$return = str_replace("\\\"", "\"", $return);
$return = str_replace("\\'", "'", $return);
return $return;
}

