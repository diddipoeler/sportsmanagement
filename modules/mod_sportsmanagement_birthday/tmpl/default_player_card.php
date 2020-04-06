<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_play_card.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_birthday
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

?>

<!--<link href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" rel="stylesheet">-->

<?php
foreach ($persons AS $person) {
    $text = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], $person['nickname'], $person['lastname'], $params->get("name_format")), ENT_QUOTES, 'UTF-8');
    switch ($person['days_to_birthday']) {
    case 0: $whenmessage = $params->get('todaymessage');
        break;
    case 1: $whenmessage = $params->get('tomorrowmessage');
        break;
    default: $whenmessage = str_replace('%DAYS_TO%', $person['days_to_birthday'], trim($params->get('futuremessage')));
        break;
    }
    $birthdaytext = htmlentities(trim(Text::_($params->get('birthdaytext'))), ENT_COMPAT, 'UTF-8');
    $dayformat = htmlentities(trim($params->get('dayformat')));
    $birthdayformat = htmlentities(trim($params->get('birthdayformat')));
    $birthdaytext = str_replace('%WHEN%', $whenmessage, $birthdaytext);
    $birthdaytext = str_replace('%AGE%', $person['age'], $birthdaytext);
    $birthdaytext = str_replace('%DATE%', strftime($dayformat, strtotime($person['year'] . '-' . $person['daymonth'])), $birthdaytext);
    $birthdaytext = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($person['date_of_birth'])), $birthdaytext);
    $birthdaytext = str_replace('%BR%', '<br />', $birthdaytext);
    $birthdaytext = str_replace('%BOLD%', '<b>', $birthdaytext);
    $birthdaytext = str_replace('%BOLDEND%', '</b>', $birthdaytext);
    $person_link = "";
    $person_type = $person['type'];
    if ($person_type == 1) {
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $params->get("cfg_which_database");
        $routeparameter['s'] = $person['season_slug'];
        $routeparameter['p'] = $person['project_slug'];
        $routeparameter['tid'] = $person['team_slug'];
        $routeparameter['pid'] = $person['person_slug'];
        $person_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
    } else if ($person_type == 2) {
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $params->get("cfg_which_database");
        $routeparameter['s'] = $person['season_slug'];
        $routeparameter['p'] = $person['project_slug'];
        $routeparameter['tid'] = $person['team_slug'];
        $routeparameter['pid'] = $person['person_slug'];
        $person_link = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);
    } else if ($person_type == 3) {
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $params->get("cfg_which_database");
        $routeparameter['s'] = $person['season_slug'];
        $routeparameter['p'] = $person['project_slug'];
        $routeparameter['pid'] = $person['person_slug'];
        $person_link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee', $routeparameter);
    }
  
    $flag = $params->get('show_player_flag') ? JSMCountries::getCountryFlag($person['country']) . "&nbsp;" : "";
    $text = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], $person['nickname'], $person['lastname'], $params->get("name_format")), ENT_QUOTES, 'UTF-8');
    $usedname = $flag . $text;
    $params_com = ComponentHelper::getParams('com_sportsmanagement');
    $usefontawesome = $params_com->get('use_fontawesome');
  
    $showname = HTMLHelper::link($person_link, $usedname);
    ?>
    <div class="card">
        <?php
        if ($params->get('show_picture') == 1) {
            if (file_exists(JPATH_BASE . '/' . $person['picture']) && $person['picture'] != '') {
                $thispic = $person['picture'];
            } elseif (file_exists(JPATH_BASE . '/' . $person['default_picture']) && $person['default_picture'] != '') {
                $thispic = $person['default_picture'];
            }
            echo '<img class="photo" src="' . Uri::base() . '/' . $thispic . '" alt="' . $text . '" title="' . $text . '"';
            if ($params->get('picture_width') != '') {
                echo ' width="' . $params->get('picture_width') . '"';
            }
            echo ' /><br />';
        }
        ?>                          

        <div class="name">
            <?php
            if ($params->get('show_player_flag') == 1) {
                echo JSMCountries::getCountryFlag($person['country']). " " . $text;                  
            } else {
                echo $text;              
            }         
            ?>
        </div>

        <div class="position">
            <?php echo Text::_($person['position_name']); ?>
            <br />
            <?php echo $person['team_name']; ?></div>
        <div class="birthday-text">
            <?php echo $birthdaytext; ?>
        </div>

        <div class="player-info">
            <a href="<?php echo $person_link; ?>" >
                <?php if($usefontawesome) {
                    echo '<i aria-hidden class="fa fa-info-circle" title="'.Text::_('MOD_SPORTSMANAGEMENT_BIRTHDAY_PLAYER_CARD_INFO_BTN').'"></i>';              
}?>
                <?php echo Text::_('MOD_SPORTSMANAGEMENT_BIRTHDAY_PLAYER_CARD_INFO_BTN');?>
            </a>
        </div>
    </div>

    <?php
}
?>
