<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       mod_sportsmanagement_liveticker.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_liveticker
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Cookie;

if (! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH') ) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
 * prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
if (!class_exists('JSMModelLegacy')) {
    JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}
if (!class_exists('JSMCountries')) {
    JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
}
if (!class_exists('sportsmanagementHelper') ) {
    /**
 * add the classes for handling
 */
    $classpath = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'sportsmanagement.php';
    JLoader::register('sportsmanagementHelper', $classpath);
    BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

/**
* 
 * Include the functions only once 
*/
JLoader::register('modTurtushoutHelper', __DIR__ . '/helper.php');

$document = Factory::getDocument();
$document->addScript(Uri::base().'modules/'.$module->module.'/js/turtushout.js');

$action = Factory::getApplication()->input->getCmd('action');

$use_local_jquery   = $params->get('use_local_jquery', true);
$allow_unregistered = $params->get('allow_unregistered', false);
$use_secret_salt    = $params->get('use_secret_salt', true);
$secret_salt        = $params->get('secret_salt', 'tGbd8mfTb4p3f1_aAQpn84Qds');
$add_timeout        = $params->get('add_timeout', 240);
$update_timeout     = $params->get('update_timeout', 240);
$display_username   = $params->get('display_username', 1);
$display_title      = $params->get('display_title', 1);
$display_num        = $params->get('display_num', 5);
$display_guests     = $params->get('display_guests', 1);
$display_welcome    = $params->get('display_welcome', 1);
$size                = $params->get('size');
$cols                = $params->get('cols');
$rows                = $params->get('rows');
$use_css            = $params->get('use_css', 'simple');
$class                = $params->get('moduleclass_sfx', '');
$playtime                = $params->get('playtime');
$user             = Factory::getUser();
$userId             = (int) $user->get('id');
$name             = $user->get('name');
$display_add_box = ($userId || $allow_unregistered);

$display_teamname    = $params->get('display_teamname', 1);
$display_teamwappen    = $params->get('display_teamwappen', 0);
$display_anstoss    = $params->get('display_anstoss', 0);
$display_abpfiff    = $params->get('display_abpfiff', 0);
$display_liganame    = $params->get('display_liganame', 0);
$display_ligaflagge    = $params->get('display_ligaflagge', 0);

$table_class    = $params->get('table_class', 'table');

if ($use_local_jquery) {
    $document->addScript(Uri::base().'modules/'.$module->module.'/js/jquery-1.2.3.pack.js');
}

if ($use_css) {
    /**
 * add css file
 */
    $document->addStyleSheet(Uri::base().'modules/'.$module->module.'/css/'.$use_css);
}
    
$is_ajaxed = isset($_SERVER["HTTP_X_REQUESTED_WITH"])?($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") : false;
$cookie = Factory::getApplication()->input->cookie;
$cookieValue = $cookie->get('tstoken');
switch ($action) {
case "turtushout_shout":

    if (!$display_add_box) {
        $ajax_return = "Login First!";
        break;
    }
    if ($use_secret_salt && !(        Factory::getApplication()->input->getInt('ts') && !empty($cookieValue) && $cookieValue == md5($secret_salt . Factory::getApplication()->input->getInt('ts'))        )
    ) {
        $ajax_return = "Access Error!";
        break;
    }
    if($use_secret_salt && ((Factory::getApplication()->input->getInt('ts') + 120) < mktime())) {
        $ajax_return = "Access Error!";
        break;
    }

    $errors = modTurtushoutHelper::shout($display_username, $display_title, $add_timeout);
    if ($errors) {
        $ajax_return = $errors;
    } else {
        $ajax_return = "Shouted!";
    }
    break;
case "turtushout_del":
    if ($user->gid == 25) {
        $errors = modTurtushoutHelper::delete();
        if ($errors) {
            $ajax_return = $errors;
        } else {
            $ajax_return = "Deleted!";
        }
    } else {
        $ajax_return = "Access denied!";
    }
    break;
case "turtushout_token":
    $ct = mktime();
    setcookie('tstoken', md5($secret_salt . $ct), 0, '/');
    $ajax_return = $ct;
    break;
default:

    break;

}

if (!$is_ajaxed || ($action == "turtushout_shouts")) {
    $list = modTurtushoutHelper::getList($params, $display_num);
    $listcomment = modTurtushoutHelper::getListCommentary($list);
    $list_html = "";
    
    $list_html .= "<div class='turtushout-entry'>";
    $list_html .= "<div class='turtushout-name'>";
    $list_html .= "<table class=\"".$table_class."\">";
    $list_html .= "<thead>" ;
    $list_html .= "<tr>" ;
    $list_html .= "<td colspan=\"\" align=\"middle\" >" . "aktuelle Zeit" . "</td>";
    $date = new DateTime();
    $config = Factory::getConfig();
    $date->setTimezone(new DateTimeZone($config->get('offset')));    
    $list_html .= "<td colspan=\"8\" align=\"left\" >" . $date->format('H:i:s'). "</td>";    
    $list_html .= "</tr>" ;
    $list_html .= "<tr>" ;

    if ($display_liganame && $display_ligaflagge ) {
        $list_html .= "<td colspan=\"2\">" . "Liga" . "</td>";
    }

    if (( !$display_liganame && $display_ligaflagge ) || ( $display_liganame && !$display_ligaflagge ) ) {
        $list_html .= "<td colspan=\"1\">" . "Liga" . "</td>";
    }

    if ($display_anstoss ) {
        $list_html .= "<td>" . "Anpfiff" . "</td>";
    }

    if ($display_abpfiff ) {
        $list_html .= "<td>" . "Abpfiff" . "</td>";
    }

    /**
 * wappen und teamname
 */
    if ($display_teamwappen && $display_teamname < 3 ) {
        $list_html .= "<td colspan=\"4\" align=\"middle\" >" . "Paarung" . "</td>";
    }
    /**
 * kein wappen und teamname
 */
    if (!$display_teamwappen && $display_teamname < 3 ) {
        $list_html .= "<td colspan=\"2\" align=\"middle\" >" . "Paarung" . "</td>";
    }
    /**
 * wappen und kein teamname
 */
    if ($display_teamwappen && $display_teamname == 3 ) {
        $list_html .= "<td colspan=\"2\" align=\"middle\" >" . "Paarung" . "</td>";
    }
    /**
 * kein wappen und kein teamname
 */
    if (!$display_teamwappen && $display_teamname == 3 ) {
    }

    $list_html .= "<td colspan=\"2\">" . "Ergebnis" . "</td>";
    $list_html .= "</tr>" ;
    $list_html .=  "</thead>" ;

    for ($i = 0, $ic = count($list); $i<$ic; $i++)
    {

        $anstossdatum = explode(" ", $list[$i]->match_date);

        $anstoss = $anstossdatum[1];
        $abpfiff = $anstoss + ( ( $list[$i]->game_regular_time + $list[$i]->halftime ) * 60 );

        $abpfiff = date('H:i:s', strtotime($anstoss) + ($list[$i]->game_regular_time + $list[$i]->halftime)*60);

        $matchpart1_pic = (trim($list[$i]->wappenheim) != "")? sprintf('<img src="%s" alt="%s" width="20"/>', $list[$i]->wappenheim, $list[$i]->heim) : "";
        $matchpart2_pic = (trim($list[$i]->wappengast) != "")? sprintf('<img src="%s" alt="%s" width="20"/>', $list[$i]->wappengast, $list[$i]->gast) : "";

        $list_html .=  "<tr>" ;

        /**
 * ligaflagge
 */
        if ($display_ligaflagge ) {
             $list_html .= "<td>" . "<img src=\"".$list[$i]->country_picture."\" alt=\"".$list[$i]->countries_iso_code_3."\" title=\"".$list[$i]->countries_iso_code_3."\" hspace=\"2\" /> " . "</td>";
        }

        /**
 * liganame
 */
        if ($display_liganame ) {
             $list_html .= "<td>" . $list[$i]->name . "</td>";
        }

        if ($display_anstoss ) {
             $list_html .= "<td>" . $anstoss . "</td>";
        }

        if ($display_abpfiff ) {
             $list_html .= "<td>" . $abpfiff . "</td>";
        }

        if ($display_teamwappen ) {
             $list_html .= "<td>" . $matchpart1_pic . "</td>";
        }

        if ($display_teamname == 0 ) {
            $list_html .= "<td>" . $list[$i]->heim . "</td>";
        }
        if ($display_teamname == 1 ) {
             $list_html .= "<td>" . $list[$i]->heim_middle_name . "</td>";
        }
        if ($display_teamname == 2 ) {
             $list_html .= "<td>" . $list[$i]->heim_short_name . "</td>";
        }
        if ($display_teamname == 3 ) {
        }
        if ($display_teamwappen ) {
             $list_html .= "<td>" . $matchpart2_pic . "</td>";
        }
        if ($display_teamname == 0 ) {
             $list_html .= "<td>" . $list[$i]->gast . "</td>";
        }
        if ($display_teamname == 1 ) {
             $list_html .= "<td>" . $list[$i]->gast_middle_name . "</td>";
        }
        if ($display_teamname == 2 ) {
             $list_html .= "<td>" . $list[$i]->gast_short_name . "</td>";
        }
        if ($display_teamname == 3 ) {
        }

        $list_html .= "<td>" . $list[$i]->team1_result . "</td>";
        $list_html .= "<td>" . $list[$i]->team2_result . "</td>";
        $list_html .= "</tr>" ;

        if (isset($listcomment[$list[$i]->match_id]) ) {
             $list_html .= "<tr>" ;  
             $list_html .= "<td colspan=\"9\">" ; 
             $list_html .= "<div style=\"height:80px; overflow:auto;\">";
             $list_html .= "<table width=\"100%\">";

            foreach ( $listcomment[$list[$i]->match_id] as $key => $value )
             {
                $list_html .= "<tr>" ;
                $list_html .= "<td width=\"10%\">" . $value->event_time  . "</td>";
                $list_html .= "<td width=\"10%\">" . HTMLHelper::image(Uri::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', 'Kommentar', array(' title' => 'Kommentar'))  . "</td>";
                $list_html .= "<td width=\"80%\">" . $value->notes  . "</td>";    
                $list_html .= "</tr>" ;
            }
    
             $list_html .= "</table>";
             $list_html .= "</div>";
             $list_html .= "</td>" ; 
             $list_html .= "</tr>" ;
        }

    }
        $list_html .= "</table>";
    $list_html .= "</div>";
    $list_html .= "</div>";
    $ajax_return = $list_html;
}

if ($is_ajaxed) {
    echo $ajax_return;
    exit;
}

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require ModuleHelper::getLayoutPath($module->module);
?>
</div>
