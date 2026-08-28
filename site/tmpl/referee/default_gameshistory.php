<?php
/** Native Joomla 5/6 referee games history. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamLogoHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!$this->games) {
    return;
}

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$databaseSelector = $this->input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
$seasonId = max(0, $this->input->getInt('s', 0));
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$separator = (string) ($this->overallconfig['seperator'] ?? ':');
$timezone = trim((string) ($this->project->timezone ?? 'UTC')) ?: 'UTC';
?>
<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY'); ?></h2>
<div class="<?php echo $escape($this->divclassrow); ?> table-responsive" id="referee_gameshistory">
    <table class="<?php echo $escape($this->config['history_table_class']); ?>">
        <thead>
        <tr class="sectiontableheader">
            <th colspan="6"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->games as $game) : ?>
            <?php
            $reportLink = SiteRouteHelper::view('matchreport', [
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
                'p' => (string) ($this->project->slug ?? $this->project->id ?? ''),
                'mid' => (int) ($game->id ?? 0),
            ]);

            $date = Factory::getDate((string) ($game->match_date ?? ''));
            try {
                $date->setTimezone(new \DateTimeZone($timezone));
            } catch (\Throwable) {
                $date->setTimezone(new \DateTimeZone('UTC'));
            }
            $dateLabel = $date->format('l, d. F Y H:i');

            $homeProjectTeamId = (int) ($game->projectteam1_id ?? 0);
            $awayProjectTeamId = (int) ($game->projectteam2_id ?? 0);
            $home = $this->teams[$homeProjectTeamId] ?? (object) [
                'id' => (int) ($game->team1 ?? 0),
                'name' => (string) ($game->home_name ?? ''),
                'logo_big' => (string) ($game->home_logo ?? ''),
            ];
            $away = $this->teams[$awayProjectTeamId] ?? (object) [
                'id' => (int) ($game->team2 ?? 0),
                'name' => (string) ($game->away_name ?? ''),
                'logo_big' => (string) ($game->away_logo ?? ''),
            ];
            ?>
            <tr>
                <td><?php echo HTMLHelper::link($reportLink, $escape($dateLabel)); ?></td>
                <td class="td_r">
                    <?php
                    echo TeamLogoHelper::renderVariant(
                        $home,
                        'logo_big',
                        'gamehistory' . (int) ($game->id ?? 0) . '-' . $homeProjectTeamId,
                        20,
                        $this->modalwidth,
                        $this->modalheight,
                        $modalMode
                    );
                    ?>
                    <?php echo $escape($home->name ?? $game->home_name ?? ''); ?>
                </td>
                <td class="td_r"><?php echo $escape($game->team1_result ?? ''); ?></td>
                <td class="td_c"><?php echo $escape($separator); ?></td>
                <td class="td_l"><?php echo $escape($game->team2_result ?? ''); ?></td>
                <td class="td_l">
                    <?php
                    echo TeamLogoHelper::renderVariant(
                        $away,
                        'logo_big',
                        'gamehistory' . (int) ($game->id ?? 0) . '-' . $awayProjectTeamId,
                        20,
                        $this->modalwidth,
                        $this->modalheight,
                        $modalMode
                    );
                    ?>
                    <?php echo $escape($away->name ?? $game->away_name ?? ''); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
