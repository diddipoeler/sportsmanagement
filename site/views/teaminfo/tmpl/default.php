<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      deafult.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teaminfo
 */
defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>" id="teaminfo">
    <?php
    if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
        echo $this->loadTemplate('debug');
    }
    if ($this->config['show_projectheader']) {
        echo $this->loadTemplate('projectheading');
    }

    if ($this->config['show_sectionheader']) {
        echo $this->loadTemplate('sectionheader');
    }

    /**
     * diddipoeler
     * aufbau der templates
     */
    $this->output = array();
    if ($this->config['show_teaminfo']) {
        echo $this->loadTemplate('teaminfo');
    }

    if ($this->config['show_description']) {
        $this->output['COM_SPORTSMANAGEMENT_TEAMINFO_TEAMINFORMATION'] = 'description';
    }

    if ($this->config['show_extra_fields']) {
        $this->output['COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'] = 'extrafields';
    }

    if ($this->config['show_extended']) {
        $this->output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
    }

    if ($this->config['show_history']) {
        $this->output['COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY'] = 'history';
    }

    if ($this->config['show_history_leagues']) {
        $this->output['COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY_PER_LEAGUE_SUMMARY'] = 'history_leagues';
    }

    if ($this->config['show_training']) {
        $this->output['COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING'] = 'training';
    }

    echo $this->loadTemplate($this->config['show_teaminfo_tabs']);
    ?>
    <div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>" id="backbuttonfooter">
        <?PHP
        echo $this->loadTemplate('backbutton');
        echo $this->loadTemplate('footer');
        ?>
    </div>
</div>
