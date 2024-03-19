<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_ajax_top_navigation_menu
 * @file       mod_sportsmanagement_ajax_top_navigation_menu.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * https://stackoverflow.com/questions/1145208/how-to-add-li-in-an-existing-ul
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

// Prüft vor Benutzung ob die gewünschte Klasse definiert ist
if (!class_exists('sportsmanagementHelper'))
{
	// Add the classes for handling
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);


// Reference global application object
$app = Factory::getApplication();

// JInput object
$jinput = $app->input;
$ajax    = $jinput->getVar('ajax', 0, 'default', 'POST');
$ajaxmod = $jinput->getVar('ajaxmodid', 0, 'default', 'POST');

$document = Factory::getDocument();
/**
 * sprachdatei aus dem backend laden
 */
$langtag = Factory::getLanguage();

$lang         = Factory::getLanguage();
$extension    = 'com_sportsmanagement';
$base_dir     = JPATH_ADMINISTRATOR;
$language_tag = $langtag->getTag();
$reload       = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
$countryassocselect = array();
$leagueselect = array();
$divisionsselect = array();
$projectselect = array();
/**
 *
 * Include the functions only once
 */
JLoader::register('modSportsmanagementAjaxTopNavigationMenuHelper', __DIR__ . '/helper.php');

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{	
HTMLHelper::_('behavior.tooltip');
}

$helper = new modSportsmanagementAjaxTopNavigationMenuHelper($params);

$points     = $helper->getFederations();
$tab_points = array();

$navpoint       = array();
$navpoint_label = array();

for ($i = 1; $i < 23; $i++)
{
	$navpoint[]       = $params->get('navpoint' . $i);
	$navpoint_label[] = $params->get('navpoint_label' . $i);
}

$document->addScriptOptions('navpoint', $navpoint);
$document->addScriptOptions('navpoint_label', $navpoint_label);

foreach ($points as $row)
{
	$tab_points[] = $row->name;
}


//$tab_points[]                = 'NON';
$user_name                   = '';
$ende_if                     = false;
$league_assoc_id             = 0;
$sub_assoc_parent_id         = 0;
$sub_sub_assoc_parent_id     = 0;
$assoc_id                    = 0;
$subassoc_id                 = 0;
$subsubassoc_id              = 0;
$subsubsubassoc_id           = 0;
$project_id                  = $jinput->get('p', 0, 'INT');
$team_id                     = $jinput->get('tid', 0, 'INT');
$division_id                 = $jinput->get('division', 0, 'INT');
$countrysubassocselect       = array();
$countrysubsubassocselect    = array();
$countrysubsubsubassocselect = array();
$helper->setProject($project_id, $team_id, $division_id);
$league_id               = $helper->getLeagueId();
$country_id              = $helper->getProjectCountry($project_id);
$league_assoc_id         = $helper->getLeagueAssocId();
$sub_assoc_parent_id     = $helper->getAssocParentId($league_assoc_id);
$sub_sub_assoc_parent_id = $helper->getAssocParentId($sub_assoc_parent_id);

if (!empty($sub_sub_assoc_parent_id) && !$ende_if)
{
	$assoc_id       = $sub_sub_assoc_parent_id;
	$subassoc_id    = $sub_assoc_parent_id;
	$subsubassoc_id = $league_assoc_id;
	$ende_if        = true;
}

if (!empty($sub_assoc_parent_id) && !$ende_if)
{
	$assoc_id    = $sub_assoc_parent_id;
	$subassoc_id = $league_assoc_id;
	$ende_if     = true;
}

if (!empty($league_assoc_id) && !$ende_if)
{
	$assoc_id = $league_assoc_id;
	$ende_if  = true;
}

foreach ($points as $row)
{
$federationselect[$row->name] = $helper->getFederationSelect($row->name, $row->id);
$countryassocselect[$row->name] = array();
$leagueselect[$row->name] = array();
$countrysubassocselect[$row->name] = array();
$countrysubsubassocselect[$row->name] = array();
$countrysubsubsubassocselect[$row->name] = array();
$projectselect[$row->name] = array();
$divisionsselect[$row->name] = array();
	
$countryassocselect[$row->name]['assocs'] = array();	
$countrysubassocselect[$row->name]['assocs'] = array();	
$countrysubsubassocselect[$row->name]['subassocs'] = array();
$countrysubsubsubassocselect[$row->name]['subsubassocs'] = array();
$leagueselect[$row->name]['leagues'] = array();
$projectselect[$row->name]['projects'] = array();
$projectselect[$row->name]['teams'] = array();	
	
?>
<script>
console.log('tabpoints = ' + '<?php echo $row->name;?>');
</script>
<?php
}

/*
$federationselect['NON'] = $helper->getFederationSelect('NON', 0);
$countryassocselect['NON'] = array();
$countryassocselect['NON']['assocs'] = array();
$leagueselect['NON'] = array();
$countrysubassocselect['NON'] = array();
$countrysubassocselect['NON']['assocs'] = array();
$countrysubsubassocselect['NON'] = array();
$countrysubsubsubassocselect['NON'] = array();
$projectselect['NON'] = array();
$divisionsselect['NON'] = array();
$countrysubsubassocselect['NON']['subassocs'] = array();
$countrysubsubsubassocselect['NON']['subsubassocs'] = array();
$leagueselect['NON']['leagues'] = array();
$projectselect['NON']['projects'] = array();
$projectselect['NON']['teams'] = array();	
*/	
$country_federation = $helper->getCountryFederation($country_id);

if (!$country_federation)
{
	$country_federation = 'NONFED';
}

?>
<script>
    console.log('project_id = ' + '<?php echo $project_id;?>');
    console.log('country_id = ' + '<?php echo $country_id;?>');
    console.log('country_federation = ' + '<?php echo $country_federation;?>');

    console.log('league_id = ' + '<?php echo $league_id;?>');
    console.log('league_assoc_id = ' + '<?php echo $league_assoc_id;?>');
    console.log('sub_assoc_parent_id = ' + '<?php echo $sub_assoc_parent_id;?>');
    console.log('sub_sub_assoc_parent_id = ' + '<?php echo $sub_sub_assoc_parent_id;?>');

    console.log('assoc_id = ' + '<?php echo $assoc_id;?>');
    console.log('subassoc_id = ' + '<?php echo $subassoc_id;?>');
    console.log('subsubassoc_id = ' + '<?php echo $subsubassoc_id;?>');

    console.log("jquery version : " + jQuery().jquery);
    //console.log("bootstrap version : " + jQuery.fn.tooltip.Constructor.VERSION);

</script>
<?php
// Build the script.
$script   = array();
$script[] = "\n";
/**
$script[] = "$(document).ready(function(){
// Handling data-toggle manually
    $('.nav-tabs a').click(function(){
        $(this).tab('show');
    });
// The on tab shown event
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
       // alert('Hello from the other siiiiiide!');
        var current_tab = e.target;
        //alert('Hello from the other siiiiiide!' + current_tab );
        console.log('current_tab ' + current_tab);
        var previous_tab = e.relatedTarget;
    });
});";
*/

$script[] = "$(document).ready(function(){

var id = $('.tab-content .active').attr('id');
console.log('current_tab ' + id);
//console.log('aktueller index ' + $($(this).attr('href')).index() );

    
$('.nav-tabs a').click(function (e) {
     e.preventDefault();
     //alert( $($(this).attr('href')).index() );
     
     console.log('klick index ' + $($(this).attr('href')).index() );
     console.log('klick tab ' + $('.tab-content .active').attr('id') );
});   

    

});";

  
$script[] = "jQuery(document).ready(function ($){";

foreach ($points as $row)
{
	// Regionalverband auswählen
	$script[] = "$('#jlamtopfederation" . $row->name . $module->id . "').change(function(){";
	$script[] = "var value = $('#jlamtopfederation" . $row->name . $module->id . "').val();";
	$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getcountryassoc&country=' + value;";
	$script[] = "console.log('".__LINE__." country value = ' + value );";
	$script[] = "console.log('".__LINE__." country url = ' + url );";
    
    /** sollte man überlegen */
    /*
    $script[] = "
  ajaxRequest = $.ajax({
    method: 'POST',
    crossDomain: true,
    dataType: 'json',
    crossOrigin: true,
    async: true,
    contentType: 'application/json',
    //data: data,
    headers: {
        'Access-Control-Allow-Methods': '*',
        'Access-Control-Allow-Credentials': true,
        'Access-Control-Allow-Headers' : 'Access-Control-Allow-Headers, Origin, X-Requested-With, Content-Type, Accept, Authorization',
        'Access-Control-Allow-Origin': '*',
        'Control-Allow-Origin': '*',
        'cache-control': 'no-cache',
        'Content-Type': 'application/json'
    },
    url: url,
    success: function(response){
        console.log('Respond was: ', response);
    },
    error: function (request, status, error) {
        console.log('There was an error: ', request.responseText);
    }
  })

  ";
  */
    
    
    
    
    
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url,";
	$script[] = "dataType: 'json',
    cache: false,
    headers: {
     'Content-Type': 'application/json'
    },";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data1) {";
	$script[] = "$('#jlamtopassoc" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopassoc" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(' data1 = ' + data1);";
	$script[] = "});";
	$script[] = "";
	$script[] = "						$.each(data1, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopassoc" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopassoc" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					})
    ajaxRequest.fail(function(jqXHR, textStatus, errorThrown, data1) {
    console.log('".__LINE__." fehler ');
    console.error(
            '".__LINE__." The following error occurred: '+
            textStatus, errorThrown
        );
        console.log('Result: ' + textStatus + ' : ' + errorThrown + ':' + jqXHR.status + ' :' + jqXHR.statusText);
        
        //console.log(' data1 = ' + data1);
  })
  ajaxRequest.always(function(data1) {
    console.log('".__LINE__." finished ');
    //console.log(' data1 = ' + data1);
    jQuery('select#jlamtopassoc" . $row->name . $module->id . " option').remove();
    $.each(data1, function (i, val) {
    var option = $('<option>');
				option.text(val.text).val(val.value);
								jQuery('#jlamtopassoc" . $row->name . $module->id . "').append(option);
						});
    $('#jlamtopassoc" . $row->name . $module->id . "').trigger('liszt:updated');
    
  })
    
    
    ;";

	$script[] = "var valcountry = $('#jlamtopfederation" . $row->name . $module->id . "').val();";
	$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getAssocLeagueSelect&country=' + valcountry;";
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url,";
	$script[] = "dataType: 'json',";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data2) {";
	$script[] = "$('#jlamtopleagues" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopleagues" . $row->name . $module->id . " option').remove();";
	$script[] = "jQuery('select#jlamtopprojects" . $row->name . $module->id . " option').remove();";
  	$script[] = "jQuery('select#jlamtopteams" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(data2);";
	$script[] = "});";
	$script[] = "";
	$script[] = "						$.each(data2, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopleagues" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopleagues" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					});";

	$script[] = "});";

	// Landesverband auswählen
	$script[] = "$('#jlamtopassoc" . $row->name . $module->id . "').change(function(){";
	$script[] = "var value = $('#jlamtopassoc" . $row->name . $module->id . "').val();";
	$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCountrySubAssocSelect&assoc_id=' + value;";
	$script[] = "console.log('assoc_id value = ' + value );";
	$script[] = "console.log('assoc_id url = ' + url );";
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url,";
	$script[] = "dataType: 'json',";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data3) {";
	$script[] = "$('#jlamtopsubassoc" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopsubassoc" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(data3);";
	$script[] = "});";
	$script[] = "";
	$script[] = "						$.each(data3, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopsubassoc" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopsubassoc" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					});";

	$script[] = "var valcountry = $('#jlamtopfederation" . $row->name . $module->id . "').val();";
	$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getAssocLeagueSelect&country=' + valcountry + '&assoc_id=' + value;";
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url,";
	$script[] = "dataType: 'json',";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data4) {";
	$script[] = "$('#jlamtopleagues" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopleagues" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(data4);";
	$script[] = "});";
	$script[] = "";
	$script[] = "						$.each(data4, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopleagues" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopleagues" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					});";

	$script[] = "});";

	// Kreisverband auswählen
	$script[] = "$('#jlamtopsubassoc" . $row->name . $module->id . "').change(function(){";
	$script[] = "var value5 = $('#jlamtopsubassoc" . $row->name . $module->id . "').val();";
	$script[] = "var url5 = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCountrySubSubAssocSelect&subassoc_id=' + value5;";
	$script[] = "console.log('subassoc_id value5 = ' + value5 );";
	$script[] = "console.log('subassoc_id url5 = ' + url5 );";
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url5,";
	$script[] = "dataType: 'json',";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data5) {";
	$script[] = "$('#jlamtopsubsubassoc" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopsubsubassoc" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(data5);";
	$script[] = "});";
	$script[] = "";
	$script[] = "						$.each(data5, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopsubsubassoc" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopsubsubassoc" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					});";

	$script[] = "var valcountry6 = $('#jlamtopfederation" . $row->name . $module->id . "').val();";
	$script[] = "var url6 = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getAssocLeagueSelect&country=' + valcountry6 + '&assoc_id=' + value5;";
	$script[] = "console.log('subassoc_id value5 = ' + value5 );";
	$script[] = "console.log('subassoc_id url6 = ' + url6 );";
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url6,";
	$script[] = "dataType: 'json',";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data6) {";
	$script[] = "$('#jlamtopleagues" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopleagues" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(data6);";
	$script[] = "});";
	$script[] = "";
	$script[] = "						$.each(data6, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopleagues" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopleagues" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					});";

	$script[] = "});";

	// Letzte stufe auswählen
	$script[] = "$('#jlamtopsubsubassoc" . $row->name . $module->id . "').change(function(){";
	$script[] = "var value7 = $('#jlamtopsubsubassoc" . $row->name . $module->id . "').val();";
	$script[] = "var url7 = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCountrySubSubAssocSelect&subassoc_id=' + value7;";
	$script[] = "console.log('subassoc_id value7 = ' + value7 );";
	$script[] = "console.log('subassoc_id url7 = ' + url7 );";
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url7,";
	$script[] = "dataType: 'json',";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data7) {";
	$script[] = "$('#jlamtopsubsubsubassoc" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopsubsubsubassoc" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(data7);";
	$script[] = "});";
	$script[] = "";
	$script[] = "						$.each(data7, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopsubsubsubassoc" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopsubsubsubassoc" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					});";

	$script[] = "var valcountry8 = $('#jlamtopfederation" . $row->name . $module->id . "').val();";
	$script[] = "var url8 = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getAssocLeagueSelect&country=' + valcountry8 + '&assoc_id=' + value7;";
	$script[] = "console.log('assoc_id value7 = ' + value7 );";
	$script[] = "console.log('assoc_id url8 = ' + url8 );";
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url8,";
	$script[] = "dataType: 'json',";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data8) {";
	$script[] = "$('#jlamtopleagues" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopleagues" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(data8);";
	$script[] = "});";
	$script[] = "";
	$script[] = "						$.each(data8, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopleagues" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopleagues" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					});";

	$script[] = "});";

	// Liga ändern projekte wählen
	$script[] = "$('#jlamtopleagues" . $row->name . $module->id . "').change(function(){";
	$script[] = "var value9 = $('#jlamtopleagues" . $row->name . $module->id . "').val();";
	$script[] = "var url9 = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getProjectSelect&league_id=' + value9;";
	$script[] = "console.log('league_id value9 = ' + value9 );";
	$script[] = "console.log('league_id url9 = ' + url9 );";
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url9,";
	$script[] = "dataType: 'json',";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data9) {";
	$script[] = "$('#jlamtopprojects" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopprojects" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(data9);";
	$script[] = "});";
	$script[] = "";
	$script[] = "						$.each(data9, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopprojects" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopprojects" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					});";
	$script[] = "});";


	// Project ändern teams wählen
	$script[] = "$('#jlamtopprojects" . $row->name . $module->id . "').change(function(){";
	$script[] = "$('ul.jsmpage').empty();";
	$script[] = "$('ul.pagination').empty();";
	$script[] = "
loadHtml = \"<p id='loadingDiv-\"
			+ \"' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>\";
	loadHtml += \"<img src='\" + ajaxmenu_baseurl +
				\"modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>\";
	loadHtml += \"</p>\";
	document.getElementById('pagination').innerHTML += loadHtml;
 document.getElementById('ajax-nav-list').innerHTML = '';
  ";

	$script[] = "var value10 = $('#jlamtopprojects" . $row->name . $module->id . "').val();";
	$script[] = "var url10 = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getProjectTeams&project_id=' + value10;";
	$script[] = "console.log('project_id value10 = ' + value10 );";
	$script[] = "console.log('project_id url10 = ' + url10 );";
	$script[] = "ajaxRequest = $.ajax({";
	$script[] = "url: url10,";
	$script[] = "dataType: 'json',";
	$script[] = "type : 'POST'";
	$script[] = "})
    ajaxRequest.done(function(data10) {";
	$script[] = "$('#jlamtopteams" . $row->name . $module->id . " option').each(function() {";
	$script[] = "jQuery('select#jlamtopteams" . $row->name . $module->id . " option').remove();";
	$script[] = "console.log(data10);";
	$script[] = "});";
	$script[] = "";

	$script[] = "const navpoint = Joomla.getOptions('navpoint');";
	$script[] = "console.log(navpoint);";
	$script[] = "const navpoint_label = Joomla.getOptions('navpoint_label');";
	$script[] = "console.log(navpoint_label);";

	$script[] = "$('ul.jsmpage').empty();";
	$script[] = "$('ul.pagination').empty();";

	$script[] = "
var linktext = '';
//loop from 0 index to max index
for(var i = 0; i < navpoint.length; i++) {
  
if (navpoint[i] != null)  
{  
console.log('navpoint -> ' + navpoint[i]);
linktext = navpoint_label[i];
console.log('linktext -> ' + linktext);
var j = i;
console.log('var j -> ' + j);
var url11 = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getLink&view=' + navpoint[i] + '&project_id=' + value10 + '&linktext=' + linktext;
console.log('navpoint url11 = ' + url11 );
$.ajax({
url: url11,
dataType: 'json',
async: false,
type : 'POST'
}).done(function(data11) {
console.log('data11 link -> ' + data11.link);  
console.log('data11 linktext -> ' + data11.linktext );  

if (data11.link != '')
{
//console.log('navpoint_label -> ' + navpoint_label[j]);
//var linktext = navpoint_label[j];
//console.log('var j -> ' + j);  
//console.log('linktext -> ' + linktext);  
//linktext.replace(/\"/g, '');

//const linktext = Joomla.getOptions('linktext');
//console.log('linktext ajax-> ' + linktext);
//$('ul.jsmpage').append('<li class=\'nav-item\' ><a href=\"' + data11.link + '\">' + data11.linktext + '</a></li>'); 
$('#ajax-nav-list').append('<li class=\'nav-item\' ><a href=\"' + data11.link + '\">' + data11.linktext + '</a></li>');  
}

});

}

}
";


	// $script[] = "$('ul.jsmpage').append('<li class=\'nav-item\' >An element' + value10 + '</li>');";


	$script[] = "						$.each(data10, function (i, val) {";
	$script[] = "							var option = $('<option>');";
	$script[] = "							option.text(val.text).val(val.value);";
	$script[] = "							jQuery('#jlamtopteams" . $row->name . $module->id . "').append(option);";
	$script[] = "						});";
	$script[] = "						$('#jlamtopteams" . $row->name . $module->id . "').trigger('liszt:updated');";
	$script[] = "					});";
	$script[] = "});";
}

$script[] = "});";

Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

/** php fehler unterbinden */
if ( !array_key_exists($country_federation, $countryassocselect) ) {
$countryassocselect[$country_federation] = array();
}


if ( !array_key_exists($country_federation, $countrysubsubassocselect) ) {
$countrysubsubassocselect[$country_federation] = array();
}


if ( !array_key_exists($country_federation, $countrysubsubsubassocselect) ) {
$countrysubsubsubassocselect[$country_federation] = array();
}

if ( !array_key_exists($country_federation, $leagueselect) ) {
$leagueselect[$country_federation] = array();
}
if ( !array_key_exists($country_federation, $divisionsselect) ) {
$divisionsselect[$country_federation] = array();
}
if ( !array_key_exists($country_federation, $projectselect) ) {
$projectselect[$country_federation] = array();
}


if ( !array_key_exists('assocs', $countryassocselect[$country_federation]) ) {
$countryassocselect[$country_federation]['assocs'] = array();
}

//echo __LINE__.'<pre>'.print_r($country_federation,true).'</pre>';
//echo __LINE__.'<pre>'.print_r($countrysubsubassocselect,true).'</pre>';
//echo __LINE__.'<pre>'.print_r($countrysubsubassocselect[$country_federation],true).'</pre>';

if ( !array_key_exists('subassocs', $countrysubsubassocselect[$country_federation]) ) {
$countrysubsubassocselect[$country_federation]['subassocs'] = array();
}
if ( !array_key_exists('leagues', $leagueselect[$country_federation]) ) {
$leagueselect[$country_federation]['leagues'] = array();
}

if ( !array_key_exists('subsubassocs', $countrysubsubsubassocselect[$country_federation]) ) {
$countrysubsubsubassocselect[$country_federation]['subsubassocs'] = array();
}

if ( !array_key_exists('divisions', $divisionsselect[$country_federation]) ) {
$divisionsselect[$country_federation]['divisions'] = array();
}

if ( !array_key_exists('teams', $projectselect[$country_federation]) ) {
$projectselect[$country_federation]['teams'] = array();
}


/** Regionalverband */
if ($country_id)
{
	$countryassocselect[$country_federation]['assocs'] = $helper->getCountryAssocSelect($country_id);
	$leagueselect[$country_federation]['leagues']      = $helper->getAssocLeagueSelect($country_id, $assoc_id);
}
else
{
$countryassocselect[$country_federation]['assocs'] = array(HTMLHelper::_('select.option', 0, Text::_('-- Regionalverbände -- ')));
$leagueselect[$country_federation]['leagues']      = array(HTMLHelper::_('select.option', 0, Text::_('--')));    
}
/** Landesverband */
if ($assoc_id)
{
	$countrysubassocselect[$country_federation]['assocs'] = $helper->getCountrySubAssocSelect($assoc_id);
	$leagueselect[$country_federation]['leagues']         = $helper->getAssocLeagueSelect($country_id, $assoc_id);
}
else
{
//$countrysubassocselect[$country_federation]['assocs'] = array(HTMLHelper::_('select.option', 0, Text::_('-- Kreisverbände -- ')));
//$leagueselect[$country_federation]['leagues']      = array(HTMLHelper::_('select.option', 0, Text::_('--')));    
}

/** Kreisverband */
if ($subassoc_id)
{
	$countrysubsubassocselect[$country_federation]['subassocs'] = $helper->getCountrySubSubAssocSelect($subassoc_id);
	$leagueselect[$country_federation]['leagues']               = $helper->getAssocLeagueSelect($country_id, $subassoc_id);
}
else
{
//$countrysubsubassocselect[$country_federation]['subassocs'] = array(HTMLHelper::_('select.option', 0, Text::_('-- Kreisverbände -- ')));
//$leagueselect[$country_federation]['leagues']      = array(HTMLHelper::_('select.option', 0, Text::_('--')));    
}

if ($subsubassoc_id)
{
	$countrysubsubsubassocselect[$country_federation]['subsubassocs'] = $helper->getCountrySubSubAssocSelect($subsubassoc_id);
	$leagueselect[$country_federation]['leagues']                     = $helper->getAssocLeagueSelect($country_id, $subsubassoc_id);
}
else
{
//$countrysubsubsubassocselect[$country_federation]['subsubassocs'] = array(HTMLHelper::_('select.option', 0, Text::_('-- Kreisverbände -- ')));
//$leagueselect[$country_federation]['leagues']      = array(HTMLHelper::_('select.option', 0, Text::_('--')));    
}

if ($subsubsubassoc_id)
{
	//$countrysubsubsubassocselect[$country_federation]['subsubassocs'] = $helper->getCountrySubSubAssocSelect($subsubassoc_id);
	//$leagueselect[$country_federation]['leagues']                     = $helper->getAssocLeagueSelect($country_id, $subsubassoc_id);
}
else
{
//$countrysubsubsubassocselect[$country_federation]['subsubassocs'] = array(HTMLHelper::_('select.option', 0, Text::_('--  -- ')));
//$leagueselect[$country_federation]['leagues']      = array(HTMLHelper::_('select.option', 0, Text::_('--')));    
}

/** liga */
if ($league_id)
{
	$projectselect[$country_federation]['projects'] = $helper->getProjectSelect($league_id);
}
else
{
    $projectselect[$country_federation]['projects'] = array(HTMLHelper::_('select.option', 0, Text::_($params->get('text_project_dropdown'))));
}

/** projekt */ 
if ($project_id)
{
	$helper->setProject($project_id, $team_id, $division_id);
	$divisionsselect[$country_federation]['divisions'] = $helper->getDivisionSelect($project_id);
	$projectselect[$country_federation]['teams']       = $helper->getTeamSelect($project_id);
}
else
{
    
    $projectselect[$country_federation]['teams']       = array(HTMLHelper::_('select.option', 0, Text::_($params->get('text_teams_dropdown'))));
}

if (!defined('JLTOPAM_MODULESCRIPTLOADED'))
{
	/*
    if(version_compare(JVERSION,'3.0.0','ge'))
    {
    $document->addScript( Uri::base().'modules/'.$module->module.'/js/'.$module->module.'.js' );
    }
    else
    {
    $document->addScript( Uri::base().'modules/'.$module->module.'/js/'.$module->module.'_2.js' );
    }
    */
	//	$document->addScriptDeclaration(';
	//    var ajaxmenu_baseurl=\''. Uri::base() . '\';
	//      ');
	$document->addStyleSheet(Uri::base() . 'modules/' . $module->module . '/css/' . $module->module . '.css');
	$document->addStyleSheet(Uri::base() . 'modules/' . $module->module . '/css/mod_sportsmanagement_ajax_top_navigation_tabs_sliders.css');
	define('JLTOPAM_MODULESCRIPTLOADED', 1);
}

if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	$layout = 'default';
}
else
{
	$layout = 'default_2';
}

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP
	require ModuleHelper::getLayoutPath($module->module, $layout);
	?>
</div>
