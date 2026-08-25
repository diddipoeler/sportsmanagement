<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

if (!$this->club) {
    return;
}

$container = 'container';
?>
<div class="<?php echo $container; ?>" id="clubinfo">
    <?php
    if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
        echo $this->loadTemplate('debug');
    }

    echo $this->loadTemplate('projectheading');

    if (!empty($this->config['show_sectionheader'])) {
        echo $this->loadTemplate('sectionheader');
    }

    echo $this->loadTemplate('clubinfo');

    $this->output = [];

    if (!empty($this->config['show_fusion'])) {
        $this->output['COM_SPORTSMANAGEMENT_TABS_EXTRA_FUSION'] = 'fusion';
    }

    if (!empty($this->config['show_extra_fields'])) {
        $this->output['COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'] = 'extrafields';
    }

    if (!empty($this->config['show_extended'])) {
        $this->output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
    }

    if (
        !empty($this->config['show_maps'])
        && !empty($this->club->latitude)
        && !empty($this->club->longitude)
        && (string) $this->club->latitude !== '0.00000000'
        && (string) $this->club->longitude !== '0.00000000'
    ) {
        $this->output['COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'] = 'googlemap';
    }

    if (!empty($this->config['show_teams_of_club'])) {
        $this->output['COM_SPORTSMANAGEMENT_CLUBINFO_TEAMS'] = 'teams';
    }

    if (!empty($this->config['show_club_rssfeed']) && $this->rssfeeditems) {
        $this->output['COM_SPORTSMANAGEMENT_CLUBINFO_RSSFEED'] = 'rssfeed_4';
    }

    $template = (string) ($this->config['show_clubinfo_tabs'] ?? 'no_tabs');
    echo $this->loadTemplate($template);
    echo $this->loadTemplate('jsminfo');
    ?>
</div>
