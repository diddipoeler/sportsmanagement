<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$templatesToLoad = ['globalviews'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

echo $this->loadTemplate('jsm_warnings');
echo $this->loadTemplate('jsm_tips');
echo $this->loadTemplate('jsm_notes');

$configDb = $this->databaseSelector;
$seasonId = $this->input->getInt('s', 0);
$showCompactSeason = !empty($this->config['show_leaguechampionoverview_season']);
$showProjectDetail = !empty($this->config['paulpanzer']);

$rankingLink = static function (object $entry) use ($configDb, $seasonId): string {
    return sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', [
        'cfg_which_database' => $configDb,
        's' => $seasonId,
        'p' => $entry->project_id ?? 0,
        'type' => 0,
        'r' => 0,
        'from' => 0,
        'to' => 0,
        'division' => 0,
    ]);
};

$teamLink = static function (object $entry) use ($configDb, $seasonId): string {
    return sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', [
        'cfg_which_database' => $configDb,
        's' => $seasonId,
        'p' => $entry->project_id ?? 0,
        'tid' => $entry->teamid ?? 0,
        'ptid' => $entry->ptid_slug ?? '',
    ]);
};

$renderEntry = static function (string $season, object $entry, bool $compact = false) use ($rankingLink, $teamLink): string {
    $projectLabel = trim($season . (($entry->project_name ?? '') !== '' ? ' - ' . $entry->project_name : ''));
    $project = HTMLHelper::link($rankingLink($entry), htmlspecialchars($projectLabel, ENT_QUOTES, 'UTF-8'));
    $published = !empty($entry->published)
        ? HTMLHelper::image(Uri::root() . 'media/com_sportsmanagement/jl_images/won.png', 'published', ['title' => 'published'])
        : HTMLHelper::image(Uri::root() . 'media/com_sportsmanagement/jl_images/lost.png', 'unpublished', ['title' => 'unpublished']);
    $matches = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES') . ':' . (int) ($entry->project_count_matches ?? 0);

    if ((int) ($entry->teamid ?? 0) <= 0) {
        $teamName = trim((string) ($entry->teamname ?? ''));
        $team = $teamName !== ''
            ? htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8')
            : $project;

        return $compact
            ? $project . ' : ' . $team . ' (' . $matches . ')'
            : '<div class="col-sm-6">' . $project . ' : ' . $published . '</div>'
                . '<div class="col-sm-4">' . $team . '</div>'
                . '<div class="col-sm-2">' . $matches . '</div>';
    }

    $teamName = (string) ($entry->teamname ?? '');
    $logo = (string) ($entry->logo_big ?? '');
    $team = ($logo !== '' ? HTMLHelper::image($logo, $teamName, ['width' => '25', 'height' => 'auto']) . ' ' : '')
        . HTMLHelper::link($teamLink($entry), htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8'));

    return $compact
        ? $project . ' : ' . $team . ' (' . $matches . ')'
        : '<div class="col-sm-6">' . $project . ' : ' . $published . '</div>'
            . '<div class="col-sm-4">' . $team . '</div>'
            . '<div class="col-sm-2">' . $matches . '</div>';
};
?>
<style>
li.hm2 { float: left; list-style-type: none; margin-right: 15px; }
.legend .row:nth-of-type(odd) div { background: #f8f9fa; }
.legend .row:nth-of-type(even) div { background: #fff; }
</style>

<div class="<?php echo htmlspecialchars($this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?> table-responsive legend" id="defaultleaguechampionoverview">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php
    $this->notes = [Text::_('Übersicht nach Saisons')];
    if (!empty($this->project->champions_complete)) {
        $this->notes[] = Text::_('Alle Meister/Erstplazierte Mannschaften der Saisons vorhanden.');
    }
    echo $this->loadTemplate('jsm_notes');
    ?>

    <?php if ($showProjectDetail): ?>
        <?php foreach ($this->leaguechampions_detail as $season => $projects): ?>
            <?php foreach ($projects as $entry): ?>
                <div class="row"><?php echo $renderEntry((string) $season, $entry, false); ?></div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php elseif ($showCompactSeason): ?>
        <div class="row">
            <ul>
                <?php foreach ($this->leaguechampions as $season => $entry): ?>
                    <li class="hm2"><?php echo $renderEntry((string) $season, $entry, true); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <?php foreach ($this->leaguechampions_detail as $season => $projects): ?>
            <?php foreach ($projects as $entry): ?>
                <div class="row"><?php echo $renderEntry((string) $season, $entry, false); ?></div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php
    $this->notes = [Text::_('Übersicht nach Mannschaft')];
    echo $this->loadTemplate('jsm_notes');
    ?>

    <div class="row">
        <table class="<?php echo htmlspecialchars((string) ($this->config['table_class'] ?? 'table'), ENT_QUOTES, 'UTF-8'); ?>">
            <thead>
                <tr>
                    <th><?php echo Text::_('Mannschaft'); ?></th>
                    <th><?php echo Text::_('Titel'); ?></th>
                    <th><?php echo Text::_('Saisons'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->teamstotal as $teamTotal): ?>
                    <?php
                    $teamId = (int) ($teamTotal['team_id'] ?? 0);
                    $entry = $this->leagueteamchampions[$teamId] ?? null;
                    if (!$entry) {
                        continue;
                    }
                    $teamName = (string) ($entry->teamname ?? '');
                    ?>
                    <tr>
                        <td>
                            <?php
                            if (!empty($entry->logo_big)) {
                                echo HTMLHelper::image((string) $entry->logo_big, $teamName, ['width' => '25', 'height' => 'auto']) . ' ';
                            }
                            echo HTMLHelper::link($teamLink($entry), htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8'));
                            ?>
                        </td>
                        <td><?php echo (int) ($this->teamseason[$teamId]['title'] ?? 0); ?></td>
                        <td style="word-break:break-all;word-wrap:break-word">
                            <?php
                            $seasons = array_map(
                                static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'),
                                $this->teamseason[$teamId]['season'] ?? []
                            );
                            echo implode(',', $seasons);
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
