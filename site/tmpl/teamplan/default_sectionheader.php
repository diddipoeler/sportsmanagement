<?php
/** Joomla 5/6 team-plan section header. */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$title = (string) $this->headertitle;
if ($this->division && !empty($this->division->name)) {
    $title .= ' – ' . (string) $this->division->name;
}
?>
<div class="<?php echo $this->escape($this->divclassrow); ?>" id="teamplan-sectionheader">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
        <h2 class="h4 mb-0"><?php echo $this->escape($title); ?></h2>

        <?php if (!empty($this->config['show_ical_link']) && $this->ptid > 0 && isset($this->teams[$this->ptid])) : ?>
            <?php
            $team = $this->teams[$this->ptid];
            $link = SiteRouteHelper::view('ical', [
                'cfg_which_database' => $this->databaseSelector,
                's' => $this->seasonId,
                'p' => (string) ($this->project->slug ?? $this->project->id ?? ''),
                'tid' => (string) ($team->team_slug ?? $team->team_id ?? $team->id ?? ''),
                'division' => 0,
                'mode' => 0,
                'ptid' => $this->ptid,
            ]);
            $label = Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT');
            $icon = HTMLHelper::image(
                'administrator/components/com_sportsmanagement/assets/images/calendar.png',
                $label,
                ['title' => $label]
            );
            echo HTMLHelper::link($link, $icon, ['title' => $label]);
            ?>
        <?php endif; ?>
    </div>
</div>
