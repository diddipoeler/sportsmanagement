<?php
/**
 * Native Joomla 5/6 UEFA five-year coefficient table.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escapeUefa = static fn (string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$title = is_string($this->headertitle ?? null) && $this->headertitle !== ''
    ? $this->headertitle
    : Text::_('COM_SPORTSMANAGEMENT_XML_UEFAWERTUNG_LAYOUT_TITLE');
$seasons = array_values(array_filter($this->seasonnames, 'is_string'));
?>
<section class="<?= $escapeUefa((string) $this->divclasscontainer) ?>" id="uefawertung">
    <h1 class="h3"><?= $escapeUefa($title) ?></h1>

    <form method="get" action="<?= $escapeUefa($this->uri->toString()) ?>" class="mb-3">
        <label for="uefa-coefficient-year" class="form-label">
            <?= $escapeUefa(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON')) ?>
        </label>
        <select id="uefa-coefficient-year" name="coefficientyear" class="form-select"
                onchange="this.form.submit()">
            <option value=""><?= $escapeUefa(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON')) ?></option>
            <?php foreach ($this->availableYears as $year => $label) : ?>
                <option value="<?= $escapeUefa((string) $year) ?>"
                    <?= (string) $year === $this->selectedYear ? ' selected' : '' ?>>
                    <?= $escapeUefa((string) $label) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary mt-2">
            <?= $escapeUefa(Text::_('JSEARCH_FILTER_SUBMIT')) ?>
        </button>
    </form>

    <?php if ($this->selectedYear === '' || $this->uefapoints === []) : ?>
        <p class="text-muted"><?= $escapeUefa(Text::_('JGLOBAL_NO_MATCHING_RESULTS')) ?></p>
    <?php else : ?>
        <div class="table-responsive" id="uefawertunganzeige">
            <table class="table table-striped table-bordered table-sm">
                <caption><?= $escapeUefa($title . ' – ' . $this->selectedYear) ?></caption>
                <thead><tr>
                    <th scope="col"><?= $escapeUefa(Text::_('COM_SPORTSMANAGEMENT_COUNTRY')) ?></th>
                    <?php foreach ($seasons as $season) : ?>
                        <th scope="col"><?= $escapeUefa($season) ?></th>
                    <?php endforeach; ?>
                    <th scope="col"><?= $escapeUefa(Text::_('COM_SPORTSMANAGEMENT_TOTAL')) ?></th>
                </tr></thead>
                <tbody>
                <?php foreach (array_slice($this->uefapoints, 0, 300) as $row) : ?>
                    <?php if (!is_object($row)) { continue; } ?>
                    <tr>
                        <th scope="row">
                            <?= $escapeUefa(is_string($row->team ?? null) ? $row->team : '') ?>
                        </th>
                        <?php foreach ($seasons as $season) : ?>
                            <?php
                            $points = $row->{$season} ?? 0;
                            $points = (is_int($points) || is_float($points) || is_string($points))
                                && is_numeric($points) && is_finite((float) $points)
                                ? (string) $points : '–';
                            ?>
                            <td><?= $escapeUefa($points) ?></td>
                        <?php endforeach; ?>
                        <?php
                        $total = $row->total ?? 0;
                        $total = (is_int($total) || is_float($total) || is_string($total))
                            && is_numeric($total) && is_finite((float) $total)
                            ? (string) $total : '–';
                        ?>
                        <td><?= $escapeUefa($total) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
