<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage helpers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementHelperHtml
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementHelperHtml {

    static $roundid = 0;
    static $project = array();
    static $teams = array();

    /**
     * sportsmanagementHelperHtml::getBootstrapModalImage()
     * 
     * @param string $target
     * @param string $picture
     * @param string $text
     * @param string $picturewidth
     * @param string $url
     * @param string $width
     * @param string $height
     * @return
     */
    public static function getBootstrapModalImage($target = '', $picture = '', $text = '', $picturewidth = '20', $url = '', $width = '100', $height = '200', $use_jquery_modal = 0) {
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;

	    switch ($use_jquery_modal)
	    {	 
	  case 2:		    
if ($url) {
$modaltext = '<a class="jcepopup jcemediabox-image" title="'.$text.'" href="'.$url.'" data-mediabox="1" data-mediabox-title="'.$text.'"><img src="'.$picture.'" alt="'.$text.'" width="'.$picturewidth.'" />';	
}	
if (!$url) {
$modaltext = '<a class="jcepopup jcemediabox-image" title="'.$text.'" href="'.$picture.'" data-mediabox="1" data-mediabox-title="'.$text.'"><img src="'.$picture.'" alt="'.$text.'" width="'.$picturewidth.'" />';		
}	
$modaltext .= '</a>';			    
break;			    
        case 1:
if ($url) {
$modaltext = '<a id="'.$target.'" href="'.$url.'" class=""';
$modaltext .= ' target="SingleSecondaryWindowName"';
$modaltext .= ' onclick="openRequestedSinglePopup(this.href,'.$width.','.$height.'); return false;"';
$modaltext .= ' title="'.$text.'"';
$modaltext .= '>';
$modaltext .= '<img src="'.$picture.'" alt="'.$text.'" width="'.$picturewidth.'" />';	
$modaltext .= '</a>';
}	
if (!$url) {
$modaltext = '<a id="'.$target.'" href="'.$picture.'" class=""';
$modaltext .= ' target="SingleSecondaryWindowName"';
$modaltext .= ' onclick="openRequestedSinglePopup(this.href,'.$width.','.$height.'); return false;"';
$modaltext .= ' title="'.$text.'"';
$modaltext .= '>';
$modaltext .= '<img src="'.$picture.'" alt="'.$text.'" width="'.$picturewidth.'" />';	
$modaltext .= '</a>';
}		
	break;	
        case 0:
//            if ($url) {
//                $modaltext = '<a title="' . $text . '" class="modal" href="' . $url . '">';
//            } else {
//                $modaltext = '<a title="' . $text . '" class="modal" href="' . $picture . '">';
//            }
//            $modaltext .= '<img width="' . $picturewidth . '" alt="' . $text . '" src="' . $picture . '"></a>';
            
            $modaltext = '<a href="#' . $target . '" title="' . $text . '" data-toggle="modal" >';
        $modaltext .= '<img src="' . $picture . '" alt="' . $text . '" width="' . $picturewidth . '" />';
        $modaltext .= '</a>';

        if (!$url) {
            $url = $picture;
        }
            $modaltext .= HTMLHelper::_('bootstrap.renderModal', $target, array(
                    'title' => $text,
                    'url' => $url,
                    'height' => $height,
                    'width' => $width,
                    'footer' => '<button type="button" class="btn btn-default" data-dismiss="modal">'.Text::_('JCANCEL').'</button>'
                        )
        );

        break;
	    
    }    
        return $modaltext;
    }

    /**
     * Return formated match time
     *
     * @param object $game
     * @param array $config
     * @param array $overallconfig
     * @param object $project
     * @return string html
     */
    public static function showMatchTime(&$game, &$config, &$overallconfig, &$project) {
        // overallconfig could be deleted here and replaced below by config as both array were merged in view.html.php
        $output = '';

        if (!isset($overallconfig['time_format'])) {
            $overallconfig['time_format'] = 'H:i';
        }
        $timeSuffix = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CLOCK');
        if ($timeSuffix == 'COM_SPORTSMANAGEMENT_GLOBAL_CLOCK') {
            $timeSuffix = '%1$s&nbsp;h';
        }

        if (strtotime($game->match_date)) {
            $matchTime = HTMLHelper::date($game->match_date, $overallconfig['time_format'], 'UTC');

            if ($config['show_time_suffix'] == 1) {
                $output .= sprintf($timeSuffix, $matchTime);
            } else {
                $output .= $matchTime;
            }

            $config['mark_now_playing'] = (isset($config['mark_now_playing'])) ? $config['mark_now_playing'] : 0;

            if ($config['mark_now_playing']) {
                $thistime = time();
                $time_to_ellapse = ( $project->halftime * ($project->game_parts - 1) ) + $project->game_regular_time;
                if ($project->allow_add_time == 1 && ($game->team1_result == $game->team2_result)) {
                    $time_to_ellapse += $project->add_time;
                }
                $time_to_ellapse = $time_to_ellapse * 60;
                $mydate = preg_split("/-| |:/", $game->match_date);
                $match_stamp = mktime($mydate[3], $mydate[4], $mydate[5], $mydate[1], $mydate[2], $mydate[0]);
                if ($thistime >= $match_stamp && $match_stamp + $time_to_ellapse >= $thistime) {
                    $match_begin = $output . ' ';
                    $title = str_replace('%STARTTIME%', $match_begin, trim(htmlspecialchars($config['mark_now_playing_alt_text'])));
                    $title = str_replace('%ACTUALTIME%', self::mark_now_playing($thistime, $match_stamp, $config, $project), $title);
                    $styletext = '';
                    if (isset($config['mark_now_playing_blink']) && $config['mark_now_playing_blink']) {
                        $styletext = ' style="text-decoration:blink"';
                    }
                    $output = '<b><i><acronym title="' . $title . '"' . $styletext . '>';
                    $output .= Text::_($config['mark_now_playing_text']);
                    $output .= '</acronym></i></b>';
                }
            }
        } else {
            $matchTime = '--&nbsp;:&nbsp;--';
            if ($config['show_time_suffix']) {
                $output .= sprintf($timeSuffix, $matchTime);
            } else {
                $output .= $matchTime;
            }
        }

        return $output;
    }

    /**
     * sportsmanagementHelperHtml::showDivisonRemark()
     * 
     * @param mixed $hometeam
     * @param mixed $guestteam
     * @param mixed $config
     * @param integer $division_id
     * @return
     */
    public static function showDivisonRemark(&$hometeam, &$guestteam, &$config, $division_id = '') {
        $app = Factory::getApplication();
    
        $output = '';
        if ($config['switch_home_guest']) {
            $tmpteam = & $hometeam;
            $hometeam = & $guestteam;
            $guestteam = & $tmpteam;
        }

        /**
         * die gruppen aus der spielpaarung setzen
         */
$hometeam->division_id = $division_id;
$division = Table::getInstance('division', 'sportsmanagementTable');
            $division->load((int) $division_id);
            $hometeam->division_slug = $division->id . ':' . $division->alias;
            $hometeam->division_name = $division->name;
            $hometeam->division_shortname = $division->shortname;      
$guestteam->division_id = $division_id;     
      $guestteam->division_slug = $division->id . ':' . $division->alias;
            $guestteam->division_name = $division->name;
            $guestteam->division_shortname = $division->shortname;
     
        if ((isset($hometeam) && $hometeam->division_id > 0) && (isset($guestteam) && $guestteam->division_id > 0)) {
            if (!isset($config['spacer'])) {
                $config['spacer'] = '/';
            }

            $nametype = 'division_' . $config['show_division_name'];

            if ($config['show_division_link']) {
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = self::$project->slug;
                $routeparameter['type'] = 0;
                $routeparameter['r'] = self::$project->round_slug;
                $routeparameter['from'] = 0;
                $routeparameter['to'] = 0;
                $routeparameter['division'] = $hometeam->division_slug;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);

                $output .= HTMLHelper::link($link, $hometeam->$nametype);
            } else {
                $output .= $hometeam->$nametype;
            }

            if ($hometeam->division_id != $guestteam->division_id) {
                $output .= $config['spacer'];

                if ($config['show_division_link']) {
                    $routeparameter = array();
                    $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                    $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                    $routeparameter['p'] = self::$project->slug;
                    $routeparameter['type'] = 0;
                    $routeparameter['r'] = self::$project->round_slug;
                    $routeparameter['from'] = 0;
                    $routeparameter['to'] = 0;
                    $routeparameter['division'] = $guestteam->division_slug;
                    $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);


                    $output .= HTMLHelper::link($link, $guestteam->$nametype);
                } else {
                    $output .= $guestteam->$nametype;
                }
            }
        } else {
            $output .= '&nbsp;';
        }

        
        return $output;
    }

    /**
     * Shows matchday title
     *
     * @param string $title
     * @param int $current_round
     * @param array $config
     * @param int $mode
     * @return string html
     */
    public static function showMatchdaysTitle($title, $current_round, &$config, $mode = 0) {
        $app = Factory::getApplication();
        $cfg_which_database = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database);
        $query = $db->getQuery(true);

        $projectid = Factory::getApplication()->input->getInt('p', 0);
        //$thisproject = Table::getInstance('Project','sportsmanagementTable');
        //$thisproject->load($projectid);

        $query->clear();
        // select some fields
        $query->select('*');
        $query->select('CONCAT_WS( \':\', id, alias ) AS project_slug');
        // from table
        $query->from('#__sportsmanagement_project');
        // where
        $query->where('id = ' . $projectid);
        $db->setQuery($query);
        $thisproject = $db->loadObject();

        echo ($title != '') ? $title . ' - ' : $title;
        if ((int) $current_round > 0) {
            //$thisround = Table::getInstance('Round','sportsmanagementTable');
            //$thisround->load($current_round);

            $query->clear();
            // select some fields
            $query->select('*');
            $query->select('CONCAT_WS( \':\', id, alias ) AS round_slug');
            // from table
            $query->from('#__sportsmanagement_round');
            // where
            $query->where('id = ' . (int) $current_round);
            $db->setQuery($query);
            $thisround = $db->loadObject();

            if ($config['type_section_heading'] == 1 && $thisround->name != '') {
                if ($mode == 1) {
                    $routeparameter = array();
                    $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                    $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                    $routeparameter['p'] = $thisproject->project_slug;
                    $routeparameter['type'] = 0;
                    $routeparameter['r'] = $thisround->round_slug;
                    $routeparameter['from'] = 0;
                    $routeparameter['to'] = 0;
                    $routeparameter['division'] = 0;
                    $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);


                    echo HTMLHelper::link($link, $thisround->name);
                } else {
                    echo $thisround->name;
                }
            } elseif ($thisround->id > 0) {
                echo ' - ' . $thisround->id . '. ' . Text::_('COM_SPORTSMANAGEMENT_RESULTS_MATCHDAY') . '&nbsp;';
            }

            if ($config['show_rounds_dates'] == 1) {
                echo " (";
                if (!strstr($thisround->round_date_first, "0000-00-00")) {
                    echo HTMLHelper::date($thisround->round_date_first, 'COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE');
                }
                if (($thisround->round_date_last != $thisround->round_date_first) &&
                        (!strstr($thisround->round_date_last, "0000-00-00"))) {
                    echo " - " . HTMLHelper::date($thisround->round_date_last, 'COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE');
                }
                echo ")";
            }
        }

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
        }
    }

    /**
     * sportsmanagementHelperHtml::getRoundSelectNavigation()
     * 
     * @param mixed $form
     * @return
     */
    public static function getRoundSelectNavigation($form, $cfg_which_database = 0, $s = 0) {
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $rounds = sportsmanagementModelProject::getRoundOptions('ASC', $cfg_which_database);
        $division = $jinput->get('division', 0, '');
        $roundid = $jinput->get('r', 0, '');

        $routeparameter = array();
        $routeparameter['cfg_which_database'] = sportsmanagementModelProject::$cfg_which_database;
        $routeparameter['s'] = sportsmanagementModelProject::$seasonid;
        $routeparameter['p'] = sportsmanagementModelProject::$projectslug;
        $routeparameter['r'] = sportsmanagementModelProject::$roundslug;
        $routeparameter['division'] = sportsmanagementModelResults::$divisionid;
        $routeparameter['mode'] = sportsmanagementModelResults::$mode;
        $routeparameter['order'] = sportsmanagementModelResults::$order;
        $routeparameter['layout'] = sportsmanagementModelProject::$layout;

//                $routeparameter['cfg_which_database'] = $cfg_which_database;
//                $routeparameter['s'] = Factory::getApplication()->input->getInt('s',0); 
//        $routeparameter["p"] = sportsmanagementModelProject::$_project->slug;
//        $routeparameter['r'] = 0;
//        $routeparameter['division'] = $division;
//        $routeparameter['mode'] = '';
//        $routeparameter['order'] = '';
//        $routeparameter['layout'] = '';

        if ($form) {
            $routeparameter['r'] = $roundid;
            $currenturl = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
            $options = array();
            foreach ($rounds as $r) {
                $routeparameter['r'] = $r->slug;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                $options[] = HTMLHelper::_('select.option', $link, $r->text);
            }
        } else {
            $routeparameter['r'] = $roundid;
            $currenturl = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
            $options = array();
            foreach ($rounds as $r) {
                $routeparameter['r'] = $r->slug;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                $options[] = HTMLHelper::_('select.option', $link, $r->text);
            }
        }

        return HTMLHelper::_('select.genericlist', $options, 'select-round', 'onchange="top.location.href=this.options[this.selectedIndex].value;"', 'value', 'text', $currenturl);
    }

    /**
     * sportsmanagementHelperHtml::showMatchPlayground()
     * 
     * @param mixed $game
     * @param mixed $config
     * @return
     */
    public static function showMatchPlayground(&$game, $config = array()) {
        $cfg_which_database = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database);
        $query = $db->getQuery(true);
if ( !isset(self::$teams[$game->projectteam1_id]) )
{
self::$teams[$game->projectteam1_id] = new stdClass();
self::$teams[$game->projectteam1_id]->standard_playground = 0;
}
	    
        if (($config['show_playground'] || $config['show_playground_alert']) && isset($game->playground_id)) {
            if (empty($game->playground_id)) {
                $game->playground_id = self::$teams[$game->projectteam1_id]->standard_playground;
            }
            if (empty($game->playground_id)) {
                //$cinfo =& Table::getInstance('Club','Table');
                //$cinfo->load($this->teams[$game->projectteam1_id]->club_id);

                $query->clear();
                // select some fields
                $query->select('*');
                // from table
                $query->from('#__sportsmanagement_club');
                // where
                $query->where('id = ' . self::$teams[$game->projectteam1_id]->club_id);
                try{
		    $db->setQuery($query);
                $cinfo = $db->loadObject();

                $game->playground_id = $cinfo->standard_playground;
                self::$teams[$game->projectteam1_id]->standard_playground = $cinfo->standard_playground;
		}
catch (Exception $e)
{
    // keine fehlermeldung ausgeben
	//Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}
		    
            }

            if (!$config['show_playground'] && $config['show_playground_alert']) {
                if (self::$teams[$game->projectteam1_id]->standard_playground == $game->playground_id) {
                    echo '-';
                    return '';
                }
            }

            $boldStart = '';
            $boldEnd = '';
            $toolTipTitle = Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_MATCH');
            $toolTipText = '';
            $playgroundID = self::$teams[$game->projectteam1_id]->standard_playground;

            if (($config['show_playground_alert']) && (self::$teams[$game->projectteam1_id]->standard_playground != $game->playground_id)) {
                $boldStart = '<b style="color:red; ">';
                $boldEnd = '</b>';
                $toolTipTitle = Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEW');
                $playgroundID = self::$teams[$game->projectteam1_id]->standard_playground;
            }

            //$pginfo =& Table::getInstance('Playground','sportsmanagementTable');
            //$pginfo->load($game->playground_id);

            $query->clear();
            // select some fields
            $query->select('*');
            // from table
            $query->from('#__sportsmanagement_playground');
            // where
            $query->where('id = ' . $game->playground_id);
		try{
            $db->setQuery($query);
            $pginfo = $db->loadObject();
}
catch (Exception $e)
{
	// keine fehlermeldung ausgeben
    //Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}
            if ($pginfo) {
                $toolTipText .= $pginfo->name . '&lt;br /&gt;';
                $toolTipText .= $pginfo->address . '&lt;br /&gt;';
                $toolTipText .= $pginfo->zipcode . ' ' . $pginfo->city . '&lt;br /&gt;';
            } else {
                // Create an object for the record we are going to update.
                $pginfo = new stdClass();
                // Must be a valid primary key value.
                $pginfo->name = '';
                $pginfo->short_name = '';
            }
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
            $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
            $routeparameter['p'] = $game->project_slug;
            $routeparameter['pgid'] = $game->playground_slug;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('playground', $routeparameter);


            $playgroundName = ($config['show_playground_name'] == 'name') ? $pginfo->name : $pginfo->short_name;
            ?>
            <span class='hasTip'
                  title='<?php echo $toolTipTitle; ?> :: <?php echo $toolTipText; ?>'> 
                <?php echo HTMLHelper::link($link, $boldStart . $playgroundName . $boldEnd); ?> </span>

            <?php
        }
    }

    /**
     * mark currently playing game
     *
     * @param int $thistime
     * @param int $match_stamp
     * @param array $config
     * @param object $project
     * @return string
     */
    function mark_now_playing($thistime, $match_stamp, &$config, &$project) {
        $whichpart = 1;
        $gone_since_begin = intval(($thistime - $match_stamp) / 60);
        $parts_time = intval($project->game_regular_time / $project->game_parts);
        if ($project->allow_add_time) {
            $overtime = 1;
        } else {
            $overtime = 0;
        }
        $temptext = Text::_('COM_SPORTSMANAGEMENT_RESULTS_LIVE_WRONG');
        for ($temp_count = 1; $temp_count <= $project->game_parts + $overtime; $temp_count++) {
            $this_part_start = (($temp_count - 1) * ($project->halftime + $parts_time));
            $this_part_end = $this_part_start + $parts_time;
            $next_part_start = $this_part_end + $project->halftime;
            if ($gone_since_begin >= $this_part_start && $gone_since_begin <= $this_part_end) {
                $temptext = str_replace('%PART%', $temp_count, trim(htmlspecialchars($config['mark_now_playing_alt_actual_time'])));
                $temptext = str_replace('%MINUTE%', ($gone_since_begin + 1 - ($temp_count - 1) * $project->halftime), $temptext);
                break;
            } elseif ($gone_since_begin > $this_part_end && $gone_since_begin < $next_part_start) {
                $temptext = str_replace('%PART%', $temp_count, trim(htmlspecialchars($config['mark_now_playing_alt_actual_break'])));
                break;
            }
        }
        return $temptext;
    }

    /**
     * return thumb up/down image url if team won/loss
     *
     * @param object $game
     * @param int $projectteam_id
     * @param array attributes
     * @return string image html code
     */
    public static function getThumbUpDownImg($game, $projectteam_id, $attributes = null) {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $usefontawesome = $params->get('use_fontawesome');
        $res = sportsmanagementHelper::getTeamMatchResult($game, $projectteam_id);
        if ($res === false) {
            return false;
        }      

        if ($res == 0) {
            if ($usefontawesome) {
                $icon = 'fa-handshake-o';
                $alt = Text::_('COM_SPORTSMANAGEMENT_DRAW');
                $title = $alt;
                $icon_color = 'draw';

            } else {
                $img = 'media/com_sportsmanagement/jl_images/draw.png';
                $alt = Text::_('COM_SPORTSMANAGEMENT_DRAW');
                $title = $alt;
            }
        } else if ($res < 0) {
            if ($usefontawesome) {
                 $icon = 'fa-thumbs-down';
                 $alt = Text::_('COM_SPORTSMANAGEMENT_LOST');
                $title = $alt;
                $icon_color = 'lost';
                 
            } else {
                $img = 'media/com_sportsmanagement/jl_images/thumbs_down.png';
                $alt = Text::_('COM_SPORTSMANAGEMENT_LOST');
                $title = $alt;
            }
        } else {
            if ($usefontawesome) {
                 $icon = 'fa-thumbs-up';
                 $alt = Text::_('COM_SPORTSMANAGEMENT_WON');
                $title = $alt;
                $icon_color = 'won';
                 
            } else {
                $img = 'media/com_sportsmanagement/jl_images/thumbs_up.png';
                $alt = Text::_('COM_SPORTSMANAGEMENT_WON');
                $title = $alt;
            }
        }
        
            // default title attribute, if not specified in passed attributes
            $def_attribs = array('title' => $title);
            if ($attributes) {
                $attributes = array_merge($def_attribs, $attributes);
            } else {
                $attributes = $def_attribs;
            }
            if ($usefontawesome) {
            return '<span class="fa-stack fa-xs '.$icon_color.'">
                    <i class="fa fa-square fa-stack-2x"></i>
                    <i class="fa '. $icon . ' fa-stack-1x fa-inverse" title="'.implode("|",$attributes).'"></i>
                    </span>';
            }else{
            return HTMLHelper::image($img, $alt, $attributes);
        }
    }

    /**
     * return thumb up/down image as link with score as title
     *
     * @param object $game
     * @param int $projectteam_id
     * @param array attributes
     * @return string linked image html code
     */
    public function getThumbScore($game, $projectteam_id, $attributes = null) {
        if (!$img = self::getThumbUpDownImg($game, $projectteam_id, $attributes = null)) {
            return false;
        }
        $txt = $teams[$game->projectteam1_id]->name . ' - ' . $teams[$game->projectteam2_id]->name . ' ' . $game->team1_result . ' - ' . $game->team2_result;

        $attribs = array('title' => $txt);
        if (is_array($attributes)) {
            $attribs = array_merge($attributes, $attribs);
        }
        $url = Route::_(sportsmanagementHelperRoute::getMatchReportRoute($game->project_slug, $game->slug));
        return HTMLHelper::link($url, $img);
    }

    /**
     * return up/down image for ranking
     *
     * @param object $team (rank)
     * @param object $previous (rank)
     * @param int $ptid
     * @return string image html code
     */
    public static function getLastRankImg($team, $previous, $ptid, $attributes = null) {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $usefontawesome = $params->get('use_fontawesome');
        if (isset($previous[$ptid]->rank)) {
            $imgsrc = Uri::root() . 'media/com_sportsmanagement/jl_images/';
            if (( $team->rank == $previous[$ptid]->rank ) || ( $previous[$ptid]->rank == "" )) {
                if ($usefontawesome) {
                    echo '<i class="fa fa-circle draw" aria-hidden="true" title="'.Text::_('COM_SPORTSMANAGEMENT_RANKING_SAME').'"></i>';
                } else {
                    $imgsrc .= "same.png";
                    $alt = Text::_('COM_SPORTSMANAGEMENT_RANKING_SAME');
                    $title = $alt;
                }
            } elseif ($team->rank < $previous[$ptid]->rank) {
                if ($usefontawesome) {
                    echo '<i class="fa fa-lg fa-angle-double-up won" aria-hidden="true" title="'.Text::_('COM_SPORTSMANAGEMENT_RANKING_UP').'"></i>';
                } else {
                    $imgsrc .= "up.png";
                    $alt = Text::_('COM_SPORTSMANAGEMENT_RANKING_UP');
                    $title = $alt;
                }
            } elseif ($team->rank > $previous[$ptid]->rank) {
                if ($usefontawesome) {
                    echo '<i class="fa fa-lg fa-angle-double-down lost" aria-hidden="true" title="'.Text::_('COM_SPORTSMANAGEMENT_RANKING_DOWN').'"></i>';
                } else {
                    $imgsrc .= "down.png";
                    $alt = Text::_('COM_SPORTSMANAGEMENT_RANKING_DOWN');
                    $title = $alt;
                }
            }

            if (!$usefontawesome) {
                $def_attribs = array('title' => $title);
                if ($attributes) {
                    $attributes = array_merge($def_attribs, $attributes);
                } else {
                    $attributes = $def_attribs;
                }
                return HTMLHelper::image($imgsrc, $alt, $attributes);
            }
        }
    }

    /**
     * sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking()
     * 
     * @param mixed $columnTitle
     * @param mixed $paramName
     * @param mixed $config
     * @param string $default
     * @return void
     */
    public static function printColumnHeadingSortAllTimeRanking($columnTitle, $paramName, $config = null, $default = "DESC") {
        // Reference global application object
        $app = Factory::getApplication();
        $jinput = $app->input;

        $output = "";
        $img = '';
        if ($config['column_sorting'] || $config == null) {
            $params = array("option" => "com_sportsmanagement",
                "view" => "rankingalltime");
            $params["cfg_which_database"] = $jinput->request->get('cfg_which_database', 0, 'INT');
            $params["l"] = $jinput->request->get('l', 0, 'INT');
            $params["points"] = $jinput->request->get('points', '3,1,0', 'STR');
            $params["type"] = Factory::getApplication()->input->getInt("type", 0);
            //$params["order"] = $jinput->request->get('order', '', 'STR');
            //$params["dir"] = $jinput->request->get('dir', 'DESC', 'STR');
            if ($jinput->request->get('order', '', 'STR') == $paramName) {
                $params["order"] = $paramName;
                $params["dir"] = ( Factory::getApplication()->input->getVar('dir', '') == 'ASC' ) ? 'DESC' : 'ASC';
                $imgname = 'sort' . (Factory::getApplication()->input->getVar('dir', '') == 'ASC' ? "02" : "01" ) . '.gif';
                $img = HTMLHelper::image('media/com_sportsmanagement/jl_images/' . $imgname, $params["dir"]);
            } else {
                $params["order"] = $paramName;
                $params["dir"] = $default;
            }
            $params["s"] = $jinput->request->get('s');
            $params["p"] = $jinput->request->get('p');


            $query = Uri::buildQuery($params);
            echo HTMLHelper::link(
                    Route::_("index.php?" . $query), Text::_($columnTitle), array("class" => "jl_rankingheader")) . $img;
        } else {
            echo Text::_($columnTitle);
        }
    }

    /**
     * sportsmanagementHelperHtml::printColumnHeadingSort()
     * 
     * @param mixed $columnTitle
     * @param mixed $paramName
     * @param mixed $config
     * @param string $default
     * @return void
     */
    public static function printColumnHeadingSort($columnTitle, $paramName, $config = null, $default = "DESC", $paramconfig = null) {
        // Reference global application object
        $app = Factory::getApplication();
        $jinput = $app->input;

        $output = "";
        $img = '';
        if ($config['column_sorting'] || $config == null) {
            $params = array("option" => "com_sportsmanagement",
                "view" => "ranking");
            $params["cfg_which_database"] = $jinput->request->get('cfg_which_database', 0, 'INT');

            $params['s'] = $jinput->request->get('s', 0, 'INT');

            if (isset($paramconfig['p'])) {
                $params['p'] = $paramconfig['p'];
            } else {
                $params['p'] = $jinput->request->get('p', '0', 'STR');
            }

            $params['type'] = $jinput->request->get('type', '0', 'STR');

            if (isset($paramconfig['r'])) {
                $params['r'] = $paramconfig['r'];
            } else {
                $params['r'] = $jinput->request->get('r', '0', 'STR');
            }

            if (isset($paramconfig['from'])) {
                $params['from'] = $paramconfig['from'];
            } else {
                $params['from'] = $jinput->request->get('from', '0', 'STR');
            }

            if (isset($paramconfig['to'])) {
                $params['to'] = $paramconfig['to'];
            } else {
                $params['to'] = $jinput->request->get('to', '0', 'STR');
            }

            $params['division'] = $jinput->request->get('division', '0', 'STR');

            if ($jinput->request->get('order', '', 'STR') == $paramName) {
                $params["order"] = $paramName;
                $params["dir"] = ( Factory::getApplication()->input->getVar('dir', '') == 'ASC' ) ? 'DESC' : 'ASC';
                $imgname = 'sort' . (Factory::getApplication()->input->getVar('dir', '') == 'ASC' ? "02" : "01" ) . '.gif';
                $img = HTMLHelper::image('media/com_sportsmanagement/jl_images/' . $imgname, $params["dir"]);
            } else {
                $params["order"] = $paramName;
                $params["dir"] = $default;
            }

            $query = Uri::buildQuery($params);
            echo HTMLHelper::link(
                    Route::_("index.php?" . $query), Text::_($columnTitle), array("class" => "jl_rankingheader")) . $img;
        } else {
            echo Text::_($columnTitle);
        }
    }

    /**
     * sportsmanagementHelperHtml::nextLastPages()
     * 
     * @param mixed $url
     * @param mixed $text
     * @param mixed $maxentries
     * @param integer $limitstart
     * @param integer $limit
     * @return void
     */
    public static function nextLastPages($url, $text, $maxentries, $limitstart = 0, $limit = 10) {
        $latestlimitstart = 0;
        if (intval($limitstart - $limit) > 0) {
            $latestlimitstart = intval($limitstart - $limit);
        }
        $nextlimitstart = 0;
        if (( $limitstart + $limit ) < $maxentries) {
            $nextlimitstart = $limitstart + $limit;
        }
        $lastlimitstart = ( $maxentries - ( $maxentries % $limit ) );
        if (( $maxentries % $limit ) == 0) {
            $lastlimitstart = ( $maxentries - ( $maxentries % $limit ) - $limit );
        }

        echo '<center>';
        echo '<table style="width: 50%; align: center;" cellspacing="0" cellpadding="0" border="0">';
        echo '<tr>';
        echo '<td style="width: 10%; text-align: left;" nowrap="nowrap">';
        if ($limitstart > 0) {
            $query = Uri::buildQuery(
                            array(
                                "limit" => $limit,
                                "limitstart" => 0));
            echo HTMLHelper::link($url . $query, '&lt;&lt;&lt;');
            echo '&nbsp;&nbsp;&nbsp';
            $query = Uri::buildQuery(
                            array(
                                "limit" => $limit,
                                "limitstart" => $latestlimitstart));
            echo HTMLHelper::link($url . $query, '&lt;&lt;');
            echo '&nbsp;&nbsp;&nbsp;';
        }
        echo '</td>';
        echo '<td style="text-align: center;" nowrap="nowrap">';
        $players_to = $maxentries;
        if (( $limitstart + $limit ) < $maxentries) {
            $players_to = ( $limitstart + $limit );
        }
        echo sprintf($text, $maxentries, ($limitstart + 1) . ' - ' . $players_to);
        echo '</td>';
        echo '<td style="width: 10%; text-align: right;" nowrap="nowrap">';
        if ($nextlimitstart > 0) {
            echo '&nbsp;&nbsp;&nbsp;';
            $query = Uri::buildQuery(
                            array(
                                "limit" => $limit,
                                "limitstart" => $nextlimitstart));
            echo HTMLHelper::link($url . $query, '&gt;&gt;');
            echo '&nbsp;&nbsp;&nbsp';
            $query = Uri::buildQuery(
                            array(
                                "limit" => $limit,
                                "limitstart" => $lastlimitstart));
            echo HTMLHelper::link($url . $query, '&gt;&gt;&gt;');
        }
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</center>';
    }

}
