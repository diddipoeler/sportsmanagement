<?php
/** SportsManagement club info template for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Language\Text;

if (!$this->club) {
    return;
}

$container = 'container';
?>
<div class="<?php echo $this->escape($container); ?>" id="clubinfo">
    <?php
    if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
        echo $this->loadTemplate('debug');
    }

    echo $this->loadTemplate('projectheading');

    if (!empty($this->config['show_sectionheader'])) {
        $this->notes = [
            Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_TITLE') . ' ' . (string) ($this->club->name ?? ''),
        ];

        if ($this->showediticon) {
            $clubId = (int) ($this->club->id ?? 0);
            $editLink = SiteRouteHelper::view('editclub', [
                'cfg_which_database' => $this->databaseSelector,
                's' => $this->input->getInt('s', 0),
                'p' => (int) ($this->project->id ?? 0),
                'cid' => $clubId,
                'id' => $clubId,
                'tmpl' => 'component',
            ]);
            $this->notes[] = ModalImageHelper::render(
                'clubedit' . $clubId,
                'administrator/components/com_sportsmanagement/assets/images/edit.png',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBINFO_EDIT_DETAILS'),
                20,
                $editLink,
                $this->modalwidth,
                $this->modalheight,
                (int) ($this->overallconfig['use_jquery_modal'] ?? 0),
                'itemprop',
                'image'
            );
        }

        echo $this->loadTemplate('jsm_notes');
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
