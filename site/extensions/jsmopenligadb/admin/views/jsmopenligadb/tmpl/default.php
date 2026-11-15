<?php
/**
 * SportsManagement OpenLigaDB administrator preview template.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

/**
 * Render a preview counter only when the remote value is a valid integer.
 *
 * Legacy view data may include arrays, booleans, negative or overflowing
 * numbers. Avoid PHP 8 array-cast warnings and implausible preview totals.
 *
 * @param mixed $value Untrusted preview summary value.
 * @return int Valid nonnegative total, otherwise zero.
 */
$counter = static function (mixed $value): int {
    if (is_int($value)) {
        return max(0, $value);
    }

    if (!is_string($value) || preg_match('/^[0-9]{1,18}$/D', $value) !== 1) {
        return 0;
    }

    $parsed = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => PHP_INT_MAX],
    ]);

    return $parsed === false ? 0 : $parsed;
};

$rawSummary = $this->previewSummary ?? [];
$values = is_array($rawSummary) ? $rawSummary : [];
$summary = [
    'matches' => $counter($values['matches'] ?? 0),
    'teams' => $counter($values['teams'] ?? 0),
    'playgrounds' => $counter($values['playgrounds'] ?? 0),
    'goals' => $counter($values['goals'] ?? 0),
];
?>
<div class="card">
    <div class="card-body">
        <h2 class="h4">OpenLigaDB Preview</h2>
        <dl class="row mb-0">
            <dt class="col-sm-6">Matches</dt>
            <dd class="col-sm-6"><?php echo (int) $summary['matches']; ?></dd>
            <dt class="col-sm-6">Teams</dt>
            <dd class="col-sm-6"><?php echo (int) $summary['teams']; ?></dd>
            <dt class="col-sm-6">Playgrounds</dt>
            <dd class="col-sm-6"><?php echo (int) $summary['playgrounds']; ?></dd>
            <dt class="col-sm-6">Goals</dt>
            <dd class="col-sm-6"><?php echo (int) $summary['goals']; ?></dd>
        </dl>
    </div>
</div>
