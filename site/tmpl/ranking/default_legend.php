<?php
/** Native ranking color legend. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$shown = [];
?>
<section class="ranking-color-legend my-3" aria-labelledby="ranking-color-legend-title">
    <h3 class="h6" id="ranking-color-legend-title"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_LEGEND'); ?></h3>
    <div class="d-flex flex-wrap gap-3">
        <?php foreach ($this->colorsByDivision as $divisionId => $colors) : ?>
            <?php foreach ($colors as $color) : ?>
                <?php
                $value = trim((string) ($color['color'] ?? ''));
                $description = trim((string) ($color['description'] ?? ''));
                $from = (int) ($color['from'] ?? 0);
                $to = (int) ($color['to'] ?? 0);
                if ($value === '' || $from <= 0) {
                    continue;
                }
                $key = $value . '|' . $description . '|' . $from . '|' . $to;
                if (isset($shown[$key])) {
                    continue;
                }
                $shown[$key] = true;
                $range = $to > 0 && $to !== $from ? $from . '–' . $to : (string) $from;
                ?>
                <span class="d-inline-flex align-items-center gap-1">
                    <span aria-hidden="true" style="display:inline-block;width:1.25rem;height:1.25rem;background:<?php echo $this->escape($value); ?>;border:1px solid currentColor"></span>
                    <span><?php echo $this->escape($range . ($description !== '' ? ': ' . Text::_($description) : '')); ?></span>
                </span>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</section>
