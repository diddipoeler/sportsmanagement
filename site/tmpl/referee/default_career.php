<?php
/** Native Joomla 5/6 referee career history. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!$this->history) {
    return;
}

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$databaseSelector = $this->input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
$seasonId = max(0, $this->input->getInt('s', 0));
?>
<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER'); ?></h2>
<div class="<?php echo $escape($this->divclassrow); ?> table-responsive" id="referee_career">
    <table class="<?php echo $escape($this->config['career_table_class']); ?>">
        <thead>
        <tr class="sectiontableheader">
            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->history as $station) : ?>
            <?php
            $link = SiteRouteHelper::view('referee', [
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
                'p' => (string) ($station->project_slug ?? $station->project_id ?? ''),
                'pid' => (string) ($this->referee->slug ?? $this->referee->pid ?? ''),
            ]);
            ?>
            <tr>
                <td class="td_l">
                    <?php echo HTMLHelper::link($link, $escape($station->project_name ?? '')); ?>
                </td>
                <td class="td_l"><?php echo $escape($station->season_name ?? ''); ?></td>
                <td class="td_l">
                    <?php
                    echo !empty($station->position_name)
                        ? Text::_((string) $station->position_name)
                        : '';
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
<br>
