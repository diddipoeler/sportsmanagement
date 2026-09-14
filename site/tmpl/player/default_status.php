<?php
/**
 * Native Joomla 5/6 player status layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa https://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$teamPlayer = $this->teamPlayer ?? null;
$containerClass = $this->escape((string) ($this->divclassrow ?? ''));
$config = is_array($this->config ?? null) ? $this->config : [];
$tableClass = $this->escape((string) ($config['player_table_class'] ?? $config['table_class'] ?? 'table'));
$sportType = trim((string) ($this->project->fs_sport_type_name ?? ''));
$dateFormat = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');

if (!$teamPlayer) {
    return;
}

$statuses = [
    [
        'enabled' => (int) ($teamPlayer->injury ?? 0) > 0,
        'date' => (string) ($teamPlayer->injury_date ?? ''),
        'end' => (string) ($teamPlayer->injury_end ?? ''),
        'range_from' => (string) ($teamPlayer->rinjury_from ?? ''),
        'range_to' => (string) ($teamPlayer->rinjury_to ?? ''),
        'detail' => (string) ($teamPlayer->injury_detail ?? ''),
        'icon' => 'injured.gif',
        'status' => 'COM_SPORTSMANAGEMENT_PERSON_INJURED',
        'date_label' => 'COM_SPORTSMANAGEMENT_PERSON_INJURY_DATE',
        'end_label' => 'COM_SPORTSMANAGEMENT_PERSON_INJURY_END',
        'detail_label' => 'COM_SPORTSMANAGEMENT_PERSON_INJURY_TYPE',
    ],
    [
        'enabled' => (int) ($teamPlayer->suspension ?? 0) > 0,
        'date' => (string) ($teamPlayer->suspension_date ?? ''),
        'end' => (string) ($teamPlayer->suspension_end ?? ''),
        'range_from' => (string) ($teamPlayer->rsusp_from ?? ''),
        'range_to' => (string) ($teamPlayer->rsusp_to ?? ''),
        'detail' => (string) ($teamPlayer->suspension_detail ?? ''),
        'icon' => 'suspension.gif',
        'status' => 'COM_SPORTSMANAGEMENT_PERSON_SUSPENDED',
        'date_label' => 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_DATE',
        'end_label' => 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_END',
        'detail_label' => 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_REASON',
    ],
    [
        'enabled' => (int) ($teamPlayer->away ?? 0) > 0,
        'date' => (string) ($teamPlayer->away_date ?? ''),
        'end' => (string) ($teamPlayer->away_end ?? ''),
        'range_from' => (string) ($teamPlayer->raway_from ?? ''),
        'range_to' => (string) ($teamPlayer->raway_to ?? ''),
        'detail' => (string) ($teamPlayer->away_detail ?? ''),
        'icon' => 'away.gif',
        'status' => 'COM_SPORTSMANAGEMENT_PERSON_AWAY',
        'date_label' => 'COM_SPORTSMANAGEMENT_PERSON_AWAY_DATE',
        'end_label' => 'COM_SPORTSMANAGEMENT_PERSON_AWAY_END',
        'detail_label' => 'COM_SPORTSMANAGEMENT_PERSON_AWAY_REASON',
    ],
];

$statuses = array_values(array_filter($statuses, static fn (array $status): bool => $status['enabled']));

if (!$statuses) {
    return;
}

$formatDate = static function (string $date, string $range) use ($dateFormat): string {
    if ($date === '' || $date === '0000-00-00') {
        return trim($range);
    }

    $output = HTMLHelper::date($date, $dateFormat);

    if (trim($range) !== '') {
        $output .= ' - ' . trim($range);
    }

    return $output;
};
?>
<div class="<?php echo $containerClass; ?> table-responsive" id="playerstatus">
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_STATUS'); ?></h2>

    <table class="<?php echo $tableClass; ?>">
        <?php foreach ($statuses as $status) : ?>
            <?php
            $start = $formatDate($status['date'], $status['range_from']);
            $end = $formatDate($status['end'], $status['range_to']);
            $sameDate = $status['date'] !== '' && $status['date'] === $status['end'];
            $statusText = Text::_($status['status']);
            $iconPath = 'media/com_sportsmanagement/events/' . $sportType . '/' . $status['icon'];
            ?>
            <tr>
                <td class="label">
                    <?php echo '&nbsp;&nbsp;' . HTMLHelper::image($iconPath, $statusText, ['title' => $statusText]); ?>
                    <?php if ($sameDate) : ?>
                        <?php echo $statusText; ?>
                    <?php endif; ?>
                </td>
                <?php if ($sameDate) : ?>
                    <td class="data"><?php echo $end; ?></td>
                <?php endif; ?>
            </tr>

            <?php if (!$sameDate) : ?>
                <tr>
                    <td class="label"><?php echo Text::_($status['date_label']); ?></td>
                    <td class="data"><?php echo $start; ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo Text::_($status['end_label']); ?></td>
                    <td class="data"><?php echo $end; ?></td>
                </tr>
            <?php endif; ?>

            <tr>
                <td class="label"><?php echo Text::_($status['detail_label']); ?></td>
                <td class="data"><?php echo $this->escape($status['detail']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
