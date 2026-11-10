<?php
/**
 * Native Joomla 5/6 combined results and rankings page layout.
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

$escapeCombined = static fn (string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$name = is_string($this->project->name ?? null) ? $this->project->name : '';
$resultsFirst = (int) $this->params->get('what_to_show_first', 0) === 1;
$sections = $resultsFirst ? ['results', 'ranking'] : ['ranking', 'results'];
$labels = [
    'ranking' => Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE'),
    'results' => Text::_('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS'),
];
?>
<div class="<?= $escapeCombined((string) $this->divclasscontainer) ?>" id="resultsranking">
    <h1 class="h3"><?= $escapeCombined($name !== ''
        ? $name : Text::_('COM_SPORTSMANAGEMENT_XML_RESULTS_RANKING_LAYOUT_TITLE')) ?></h1>

    <nav aria-label="<?= $escapeCombined(Text::_('COM_SPORTSMANAGEMENT_XML_RESULTS_RANKING_LAYOUT_TITLE')) ?>">
        <ul class="nav nav-pills mb-3">
            <?php foreach ($sections as $section) : ?>
                <li class="nav-item">
                    <a class="nav-link" href="#resultsranking-<?= $section ?>">
                        <?= $escapeCombined($labels[$section]) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <?php foreach ($sections as $section) : ?>
        <section id="resultsranking-<?= $section ?>" class="mb-4"
                 aria-label="<?= $escapeCombined($labels[$section]) ?>">
            <h2 class="h4"><?= $escapeCombined($labels[$section]) ?></h2>
            <?= $this->loadTemplate($section) ?>
        </section>
    <?php endforeach; ?>
</div>
