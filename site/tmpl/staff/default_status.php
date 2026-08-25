<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage staff
 * @file       default_status.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$staff = $this->inprojectinfo;

if (!$staff || !((int) ($staff->injury ?? 0) > 0
    || (int) ($staff->suspension ?? 0) > 0
    || (int) ($staff->away ?? 0) > 0)) {
    return;
}

$formatDate = static function ($value): string {
    $value = trim((string) $value);

    if ($value === '' || str_starts_with($value, '0000-00-00')) {
        return '';
    }

    return HTMLHelper::date($value, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
};

$legacyRound = static function ($value): string {
    $round = (int) $value;

    return $round > 0
        ? $round . '. ' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME')
        : '';
};

$statusDate = static function ($dateValue, $roundValue) use ($formatDate, $legacyRound): string {
    $date = $formatDate($dateValue);

    return $date !== '' ? $date : $legacyRound($roundValue);
};

$renderStatus = static function (
    object $staff,
    string $flagField,
    string $startDateField,
    string $endDateField,
    string $legacyStartField,
    string $legacyEndField,
    string $detailField,
    string $icon,
    string $statusLabel,
    string $startLabel,
    string $endLabel,
    string $detailLabel
) use ($statusDate): void {
    if ((int) ($staff->{$flagField} ?? 0) <= 0) {
        return;
    }

    $start = $statusDate($staff->{$startDateField} ?? '', $staff->{$legacyStartField} ?? 0);
    $end = $statusDate($staff->{$endDateField} ?? '', $staff->{$legacyEndField} ?? 0);
    $sameDate = $start !== '' && $start === $end;
    $imageTitle = Text::_($statusLabel);
    ?>
    <tr>
        <td class="label">
            <?php
            echo '&nbsp;&nbsp;' . HTMLHelper::image(
                $icon,
                $imageTitle,
                ['title' => $imageTitle]
            );
            echo Text::_($statusLabel);
            ?>
        </td>
        <?php if ($sameDate) : ?>
            <td class="data"><?php echo htmlspecialchars($end, ENT_QUOTES, 'UTF-8'); ?></td>
        <?php endif; ?>
    </tr>

    <?php if (!$sameDate && $start !== '') : ?>
        <tr>
            <td class="label"><?php echo Text::_($startLabel); ?></td>
            <td class="data"><?php echo htmlspecialchars($start, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    <?php endif; ?>

    <?php if (!$sameDate && $end !== '') : ?>
        <tr>
            <td class="label"><?php echo Text::_($endLabel); ?></td>
            <td class="data"><?php echo htmlspecialchars($end, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    <?php endif; ?>

    <?php if (trim((string) ($staff->{$detailField} ?? '')) !== '') : ?>
        <tr>
            <td class="label"><?php echo Text::_($detailLabel); ?></td>
            <td class="data"><?php echo htmlspecialchars((string) $staff->{$detailField}, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    <?php endif;
};

$eventBase = 'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/';
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="staff">
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_STATUS'); ?></h2>

    <table class="status">
        <?php
        $renderStatus(
            $staff,
            'injury',
            'injury_date_start',
            'injury_date_end',
            'injury_date',
            'injury_end',
            'injury_detail',
            $eventBase . 'injured.gif',
            'COM_SPORTSMANAGEMENT_PERSON_INJURED',
            'COM_SPORTSMANAGEMENT_PERSON_INJURY_DATE',
            'COM_SPORTSMANAGEMENT_PERSON_INJURY_END',
            'COM_SPORTSMANAGEMENT_PERSON_INJURY_TYPE'
        );
        $renderStatus(
            $staff,
            'suspension',
            'susp_date_start',
            'susp_date_end',
            'suspension_date',
            'suspension_end',
            'suspension_detail',
            $eventBase . 'suspension.gif',
            'COM_SPORTSMANAGEMENT_PERSON_SUSPENDED',
            'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_DATE',
            'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_END',
            'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_REASON'
        );
        $renderStatus(
            $staff,
            'away',
            'away_date_start',
            'away_date_end',
            'away_date',
            'away_end',
            'away_detail',
            $eventBase . 'away.gif',
            'COM_SPORTSMANAGEMENT_PERSON_AWAY',
            'COM_SPORTSMANAGEMENT_PERSON_AWAY_DATE',
            'COM_SPORTSMANAGEMENT_PERSON_AWAY_END',
            'COM_SPORTSMANAGEMENT_PERSON_AWAY_REASON'
        );
        ?>
    </table>
</div>
<br/>
