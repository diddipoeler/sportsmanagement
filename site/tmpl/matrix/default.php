<?php
/**
 * Native Joomla 5/6 site results matrix, without legacy global factories.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatrixPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Language\Text;

if (!$this->project) {
    return;
}

$escapeMatrix = static fn (string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$sections = [];
$showDivisions = $this->divisionid === 0 && count($this->divisions) > 1;
if ($showDivisions) {
    foreach ($this->divisions as $division) {
        if (!is_object($division)) {
            continue;
        }

        $divisionId = (int) ($division->id ?? 0);
        if ($divisionId <= 0) {
            continue;
        }

        $visible = array_filter(
            $this->teams,
            static fn ($team): bool => is_object($team)
                && (int) ($team->division_id ?? 0) === $divisionId
        );

        if ($visible !== []) {
            $sections[] = [
                'title' => is_string($division->name ?? null) ? $division->name : '',
                'teams' => $visible,
            ];
        }
    }
} else {
    $sections[] = [
        'title' => '',
        'teams' => $this->teams,
    ];
}

$projectName = $this->project->name ?? '';
$projectName = is_string($projectName) ? $projectName : '';
$showMatrix = !array_key_exists('show_matrix', $this->config) || !empty($this->config['show_matrix']);
$linkTeams = !empty($this->config['link_teams']);
$projectId = max(0, (int) ($this->project->id ?? 0));
?>
<section class="<?= $escapeMatrix((string) $this->divclasscontainer) ?>" id="matrix">
    <h2 class="h4"><?= $escapeMatrix(Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE')) ?></h2>
    <?php if ($projectName !== '') : ?>
        <p class="text-muted"><?= $escapeMatrix($projectName) ?></p>
    <?php endif; ?>

    <?php if ($showMatrix) : ?>
        <?php foreach ($sections as $section) : ?>
            <?php
            $teams = MatrixPresentationHelper::teams($section['teams']);
            $scores = MatrixPresentationHelper::results($this->results, $teams);
            ?>
            <?php if ($section['title'] !== '') : ?>
                <h3 class="h5"><?= $escapeMatrix($section['title']) ?></h3>
            <?php endif; ?>
            <?php if ($teams === []) : ?>
                <p class="text-muted"><?= $escapeMatrix(Text::_('JGLOBAL_NO_MATCHING_RESULTS')) ?></p>
            <?php else : ?>
                <div class="table-responsive mb-4">
                    <table class="table table-striped table-bordered table-sm jsm-native-matrix">
                        <caption><?= $escapeMatrix($section['title'] !== '' ? $section['title'] : $projectName) ?></caption>
                        <thead>
                        <tr>
                            <th scope="col"><?= $escapeMatrix(Text::_('COM_SPORTSMANAGEMENT_MATRIX_TEAM_NAME')) ?></th>
                            <?php foreach ($teams as $column) : ?>
                                <th scope="col"><?= $escapeMatrix($column['name']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($teams as $row) : ?>
                            <tr>
                                <th scope="row">
                                    <?php if ($linkTeams && $row['teamSlug'] !== '' && $projectId > 0) : ?>
                                        <?php
                                        $href = SiteRouteHelper::view('roster', [
                                            'cfg_which_database' => $this->databaseSelector === 1 ? 1 : 0,
                                            'p' => $projectId,
                                            'tid' => $row['teamSlug'],
                                            'ptid' => 0,
                                        ]);
                                        ?>
                                        <a href="<?= $escapeMatrix($href) ?>"><?= $escapeMatrix($row['name']) ?></a>
                                    <?php else : ?>
                                        <?= $escapeMatrix($row['name']) ?>
                                    <?php endif; ?>
                                </th>
                                <?php foreach ($teams as $column) : ?>
                                    <?php
                                    $cell = $row['id'] === $column['id']
                                        ? '—'
                                        : implode(' / ', $scores[$row['id']][$column['id']] ?? []);
                                    ?>
                                    <td><?= $escapeMatrix($cell !== '' ? $cell : '–') ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_help'])) : ?>
        <p class="small text-muted"><?= $escapeMatrix(Text::_('COM_SPORTSMANAGEMENT_MATRIX_HINT')) ?></p>
    <?php endif; ?>
</section>
