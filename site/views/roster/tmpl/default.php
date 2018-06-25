<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage roster
 */
 
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');

//echo ' rows<br><pre>'.print_r($this->rows,true).'</pre>';
//echo ' positioneventtypes<br><pre>'.print_r($this->positioneventtypes,true).'</pre>';
// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

if ($this->config['show_staff_layout'] == 'staff_johncage' ||
        $this->config['show_players_layout'] == 'player_johncage') {
// johncage css:
    $css = "";
    if (count($this->rows) > 0) {
        if ($this->config['show_player_icon']) {
            $css .= ".jl_rp0 .jl_rosterperson_picture_column,.jl_rosterperson_pic > img, .jl_rosterperson_pic
		{
		width: " . $this->config['player_picture_width'] . "px;
		
		}
		.jl_rp1 .jl_rosterperson_detail_column
		{
		
		margin-right:" . ($this->config['player_picture_width'] + 10) . "px;
		}
		";
        }//if ($this->config['show_player_icon']) ends
        $InOutStats = array();
        $InOutStats[1] = array('icon' => 'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/shirt.png');
        $InOutStats[2] = array('icon' => 'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/startroster.png');
        $InOutStats[3] = array('icon' => 'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/in.png');
        $InOutStats[4] = array('icon' => 'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/out.png');
        for ($x = count($InOutStats); $x >= 1; $x--) {
            $css .= ".jl_roster_in_out$x { 
		background: #0a0 url('" . JURI::base() . $InOutStats[$x]['icon'] . "') left top  no-repeat;
		-moz-background-size: 14px;
		-o-background-size: 14px;
		-webkit-background-size: 14px; 
		-khtml-background-size: 14px;  
		background-size: 14px;
	}\n";
        }//for ($x=count($InOutStats);$x>=1;$x--) ends
        unset($InOutStats);
        if ($this->config['show_events_stats'] AND $this->positioneventtypes) {
            $positions = array_keys($this->rows);
            $eventtypes_done = array();
            foreach ($positions AS $position_id) {
                foreach ((array) $this->positioneventtypes[$position_id] AS $eventtype) {
                    if (!in_array($eventtype->eventtype_id, $eventtypes_done)) {
                        $iconPath = $eventtype->icon;
                        if (!strpos(' ' . $iconPath, '/')) {
                            $iconPath = 'media/com_sportsmanagement/event_icons/' . $iconPath;
                        }
                        $css .= ".jl_roster_event" . $eventtype->eventtype_id . " { 
					background: #ddd url('" . JURI::base() . $iconPath . "') left top  no-repeat;
					-moz-background-size: 12px;
					-o-background-size: 12px;
					-webkit-background-size: 12px; 
					-khtml-background-size: 12px;  
					background-size: 12px;
					}\n";
                        $eventtypes_done[] = $eventtype->eventtype_id;
                    }
                }
            }
            unset($positions);
            unset($eventtypes_done);
        }//if ($this->config['show_events_stats'] AND $this->positioneventtypes) ends
    }//if (count($this->rows > 0)) ends

    if (count($this->stafflist) > 0 AND $this->config['show_staff_icon'] == 1) {
        $css .= ".jl_rp0 .jl_rosterperson_staffpicture_column,.jl_roster_staffperson_pic > img, .jl_roster_staffperson_pic
		{
		width: " . $this->config['staff_picture_width'] . "px;

		}
		.jl_rp1 .jl_roster_staffperson_detail_column
		{
		margin-right:" . ($this->config['staff_picture_width'] + 10) . "px;
		}
		";
    }

    if (!empty($css)) {
        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration($css);
    }
// johncage css ends    
}
?>
<div class="container-fluid">
<?php
if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
    echo $this->loadTemplate('debug');
}
if ($this->config['show_projectheader']) {
    echo $this->loadTemplate('projectheading');
}

if ($this->projectteam) {
    if ($this->config['show_sectionheader']) {
        echo $this->loadTemplate('sectionheader');
    }

    if ($this->config['show_team_logo']) {
        echo $this->loadTemplate('picture');
    }

    if ($this->config['show_description']) {
        echo $this->loadTemplate('description');
    }

    if ($this->config['show_players']) {
        if (($this->config['show_players_layout']) == 'player_standard') {
            echo $this->loadTemplate('players');
        } else if (($this->config['show_players_layout']) == 'player_card') {
            $document = JFactory::getDocument();
            $option = JFactory::getApplication()->input->getCmd('option');
            $version = urlencode(sportsmanagementHelper::getVersion());
            $document->addStyleSheet($this->baseurl . '/components/' . $option . '/assets/css/' . $this->getName() . '_card.css?v=' . $version);
            echo $this->loadTemplate('players_card');
        } else if (($this->config['show_players_layout']) == 'player_johncage') {
            $document = JFactory::getDocument();
            $option = JFactory::getApplication()->input->getCmd('option');
            $version = urlencode(sportsmanagementHelper::getVersion());
            $document->addStyleSheet($this->baseurl . '/components/' . $option . '/assets/css/' . $this->getName() . '_johncage.css?v=' . $version);
            echo $this->loadTemplate('players_johncage');
//            echo $this->loadTemplate('person_player');
        }
    }

    if ($this->config['show_staff']) {
        if (($this->config['show_staff_layout']) == 'staff_standard') {
            echo $this->loadTemplate('staff');
        } else if (($this->config['show_staff_layout']) == 'staff_card') {
            $document = JFactory::getDocument();
            $option = JFactory::getApplication()->input->getCmd('option');
            $version = urlencode(sportsmanagementHelper::getVersion());
            $document->addStyleSheet($this->baseurl . '/components/' . $option . '/assets/css/' . $this->getName() . '_card.css?v=' . $version);
            echo $this->loadTemplate('staff_card');
        } else if (($this->config['show_staff_layout']) == 'staff_johncage') {
            $document = JFactory::getDocument();
            $option = JFactory::getApplication()->input->getCmd('option');
            $version = urlencode(sportsmanagementHelper::getVersion());
            $document->addStyleSheet($this->baseurl . '/components/' . $option . '/assets/css/' . $this->getName() . '_johncage.css?v=' . $version);
            echo $this->loadTemplate('staff_johncage');
//            echo $this->loadTemplate('person_staff');
        }
    }
} else {
    echo JText::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_PROJECT_TEAM');
}
?>
<div>
<?php
echo $this->loadTemplate('backbutton');
echo $this->loadTemplate('footer');
?>
</div>
</div>
