<?php
/**
 * Native Joomla 5/6 combined results and matrix page layout.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$this->project) {
    return;
}

$escapeCombined = static fn (string $text): string =>
    htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$projectName = is_string($this->project->name ?? null)
    ? $this->project->name : '';
$matrixFirst = (int) $this->params->get('what_to_show_first', 0) === 1;
$panels = $matrixFirst ? ['matrix', 'results'] : ['results', 'matrix'];
$labels = [
    'results' => Text::_('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS'),
    'matrix' => Text::_('COM_SPORTSMANAGEMENT_MATRIX'),
];
?>
<div class="<?= $escapeCombined((string) $this->divclasscontainer) ?>" id="resultsmatrix">
    <h1 class="h3"><?= $escapeCombined($projectName !== ''
        ? $projectName
        : Text::_('COM_SPORTSMANAGEMENT_XML_RESULTS_MATRIX_LAYOUT_TITLE')) ?></h1>

    <nav aria-label="<?= $escapeCombined(Text::_('COM_SPORTSMANAGEMENT_XML_RESULTS_MATRIX_LAYOUT_TITLE')) ?>">
        <ul class="nav nav-pills mb-3">
            <?php foreach ($panels as $panel) : ?>
                <li class="nav-item"><a class="nav-link" href="#resultsmatrix-<?= $panel ?>">
                    <?= $escapeCombined($labels[$panel]) ?>
                </a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <?php foreach ($panels as $panel) : ?>
        <section id="resultsmatrix-<?= $panel ?>" class="mb-4"
                 aria-label="<?= $escapeCombined($labels[$panel]) ?>">
            <h2 class="h4"><?= $escapeCombined($labels[$panel]) ?></h2>
            <?= $this->loadTemplate($panel) ?>
        </section>
    <?php endforeach; ?>
</div>
