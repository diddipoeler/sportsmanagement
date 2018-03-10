<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_birthday.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_birthday
 */
 
defined('_JEXEC') or die('Restricted access');

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if (!class_exists('sportsmanagementHelper')) {
//add the classes for handling
    $classpath = JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'helpers' . DS . 'sportsmanagement.php';
    JLoader::register('sportsmanagementHelper', $classpath);
    JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}
if (!class_exists('JSMCountries')) {
//add the classes for handling 
    $classpath = JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'countries.php';
    JLoader::register('JSMCountries', $classpath);
}

//require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'countries.php');
//require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');  
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'route.php' );

require_once(dirname(__FILE__) . DS . 'helper.php');

// Reference global application object
$app = JFactory::getApplication();
$document = JFactory::getDocument();
$show_debug_info = JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info', 0);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' module<br><pre>'.print_r($module,true).'</pre>'),'Notice');

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
    DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database'));
}

$mode = $params->def("mode");

switch ($mode) {
    case 'B':
    break;
    default:
    if ( $mode == 'L' && $params->get('show_player_card') )
    {
    $attribs['layout'] = 'default_player_card';    
    $document->addStyleSheet(JUri::base() . 'modules' . DS . $module->module . DS . 'css' . DS . 'player_card.css');
    $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/com_sportsmanagement/libraries/flag-icon/css/flag-icon.css' . '" type="text/css" />' . "\n";
    $document->addCustomTag($stylelink);
    }
    else
    {
//add css file
    $document->addStyleSheet(JUri::base() . 'modules' . DS . $module->module . DS . 'css' . DS . $module->module . '.css');
    $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/com_sportsmanagement/libraries/flag-icon/css/flag-icon.css' . '" type="text/css" />' . "\n";
    $document->addCustomTag($stylelink);
    }
    break;
}

// Prevent that result is null when either $players or $crew is null by casting each to an array.
$persons = array_merge((array) $players, (array) $crew);
if (count($persons) > 1) {
    $persons = jsm_birthday_sort($persons, $params->get('sort_order'), false);
}

if ($show_debug_info) {
    $my_text = 'persons <pre>' . print_r($persons, true) . '</pre>';
    $my_text .= 'params <pre>' . print_r($params, true) . '</pre>';
    sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, $module->module, __LINE__, $my_text);
}

$k = 0;
$counter = 0;

switch ($mode) {
    case 'B':
        $layout = isset($attribs['layout']) ? $attribs['layout'] : 'default';
        break;
    case 'L':
        $layout = isset($attribs['layout']) ? $attribs['layout'] : 'default';
        break;
    case 'J':
        $html_li = '';
        $html_ahref = '';
        $id = 0;
        $container = 'slider' . $module->id . '_container';
        $layout = isset($attribs['layout']) ? $attribs['layout'] : 'jssor';
        $document->addScript(JURI::base() . 'modules/' . $module->module . '/js/jssor.slider.mini.js');

        if (count($persons) > 0) {
            foreach ($persons AS $person) {
                if (($params->get('limit') > 0) && ($counter == intval($params->get('limit')))) {
                    break;
                }
                $class = ($k == 0) ? $params->get('sectiontableentry1') : $params->get('sectiontableentry2');

                $thispic = "";
                $flag = $params->get('show_player_flag') ? JSMCountries::getCountryFlag($person['country']) . "&nbsp;" : "";
                $text = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], $person['nickname'], $person['lastname'], $params->get("name_format")), ENT_QUOTES, 'UTF-8');
                $usedname = $flag . $text;

                $person_link = "";
                $person_type = $person['type'];
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
                $routeparameter['s'] = $params->get('s');
                $routeparameter['p'] = $person['project_slug'];
                if ($person_type == 1) {
                    $routeparameter['tid'] = $person['team_slug'];
                    $routeparameter['pid'] = $person['person_slug'];
                    $person_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
                } else if ($person_type == 2) {
                    $routeparameter['tid'] = $person['team_slug'];
                    $routeparameter['pid'] = $person['person_slug'];
                    $person_link = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);
                } else if ($person_type == 3) {

                    $routeparameter['pid'] = $person['person_slug'];
                    $person_link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee', $routeparameter);
                }
                $showname = JHTML::link($person_link, $usedname);
                ?>

                <?php
                if ($params->get('show_picture')) {
                    if (sportsmanagementHelper::existPicture($person['picture']) && $person['picture'] != '') {
                        $thispic = $person['picture'];
                    } elseif (sportsmanagementHelper::existPicture($person['default_picture']) && $person['default_picture'] != '') {
                        $thispic = $person['default_picture'];
                    }
                }
                switch ($person['days_to_birthday']) {
                    case 0: $whenmessage = $params->get('todaymessage');
                        break;
                    case 1: $whenmessage = $params->get('tomorrowmessage');
                        break;
                    default: $whenmessage = str_replace('%DAYS_TO%', $person['days_to_birthday'], trim($params->get('futuremessage')));
                        break;
                }
                $birthdaytext = htmlentities(trim(JText::_($params->get('birthdaytext'))), ENT_COMPAT, 'UTF-8');
                $dayformat = htmlentities(trim($params->get('dayformat')));
                $birthdayformat = htmlentities(trim($params->get('birthdayformat')));
                $birthdaytext = str_replace('%WHEN%', $whenmessage, $birthdaytext);
                $birthdaytext = str_replace('%AGE%', $person['age'], $birthdaytext);
                $birthdaytext = str_replace('%DATE%', strftime($dayformat, strtotime($person['year'] . '-' . $person['daymonth'])), $birthdaytext);
                $birthdaytext = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($person['date_of_birth'])), $birthdaytext);
                $birthdaytext = str_replace('%BR%', '<br />', $birthdaytext);
                $birthdaytext = str_replace('%BOLD%', '<b>', $birthdaytext);
                $birthdaytext = str_replace('%BOLDEND%', '</b>', $birthdaytext);
                $text .= '<br> ' . $birthdaytext;
                ?>
                <?php
                $showname = '';
                $html_li .= '<div><a href="' . $person_link . '"><img u="image" src="' . $thispic . '" /></a>';
                $html_li .= '<div u="caption" t="' . $params->get('jssor_captiontransitions') . '" style="position:absolute;left:10px;top:80px;width:600px;height:40px;font-size:36px;color:#000;line-height:40px;">' . $showname . '</div>';
                $html_li .= '<div u="caption" t="' . $params->get('jssor_captiontransitions') . '" style="position:absolute;left:10px;top:130px;width:600px;height:40px;font-size:36px;color:#000;line-height:40px;">' . $text . '</div>';
                $html_li .= '</div>';

                $id++;
                $k = 1 - $k;
                $counter++;
            }
        }
        break;
}
?>           
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module, $layout));
?>
</div>
