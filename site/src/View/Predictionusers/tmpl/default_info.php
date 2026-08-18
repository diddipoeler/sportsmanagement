<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$stats = $this->memberStats;
$formatDate = static function ($value): string {
    if (empty($value) || $value === '0000-00-00 00:00:00') {
        return Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_UNKNOWN');
    }
    return HTMLHelper::date($value, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE'));
};
$renderTeams = static function (array $rows, string $emptyKey): string {
    if (!$rows) {
        return Text::_($emptyKey);
    }
    $out = [];
    foreach ($rows as $row) {
        if (empty($row['visible'])) {
            $out[] = Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_SHOW_AFTER_START');
            continue;
        }
        $team = htmlspecialchars((string) ($row['team_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $project = htmlspecialchars((string) ($row['project_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $out[] = $project !== '' ? $team . ' <small>(' . $project . ')</small>' : $team;
    }
    return implode('<br>', array_unique($out));
};
?>
<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_PERS_DATA'); ?></h2>
<div class="<?php echo htmlspecialchars($this->divclassrow, ENT_QUOTES, 'UTF-8'); ?> table-responsive" id="info">
    <?php if (count($this->projectOptions) > 1) : ?>
        <form method="post" class="mb-3">
            <input type="hidden" name="option" value="com_sportsmanagement">
            <input type="hidden" name="prediction_id" value="<?php echo (int) $this->predictionGameID; ?>">
            <input type="hidden" name="uid" value="<?php echo (int) $this->predictionMemberID; ?>">
            <input type="hidden" name="r" value="<?php echo (int) $this->roundID; ?>">
            <input type="hidden" name="pggroup" value="<?php echo (int) $this->predictionGroupID; ?>">
            <input type="hidden" name="task" value="predictionusers.selectprojectround">
            <label for="predictionusers-project" class="visually-hidden"><?php echo Text::_('COM_SPORTSMANAGEMENT_ALL_PROJECTS'); ?></label>
            <select id="predictionusers-project" name="pj" class="form-select inputbox" onchange="this.form.submit()">
                <?php foreach ($this->projectOptions as $option) : ?>
                    <?php $text = (string) $option->text === 'JALL' ? Text::_('JALL') : (string) $option->text; ?>
                    <option value="<?php echo (int) $option->value; ?>"<?php echo (int) $option->value === (int) $this->projectID ? ' selected' : ''; ?>>
                        <?php echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    <?php endif; ?>

    <table class="table">
        <tbody>
        <?php if (!empty($this->config['show_photo'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_AVATAR'); ?></th>
                <td><?php echo $this->memberAvatar(); ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NAME'); ?></th>
            <td><?php echo $this->memberNameLink(); ?></td>
        </tr>
        <?php if (!empty($this->config['show_register_date'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_MEMBERSHIP'); ?></th>
                <td><?php echo $formatDate($this->predictionMember->pmRegisterDate ?? null); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_slogan'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_SLOGAN'); ?></th>
                <td><?php echo !empty($this->predictionMember->slogan)
                    ? htmlspecialchars(strip_tags((string) $this->predictionMember->slogan), ENT_QUOTES, 'UTF-8')
                    : Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NO_SLOGAN'); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_lasttip'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_LAST_PRED'); ?></th>
                <td><?php echo !empty($this->predictionMember->last_tipp) && $this->predictionMember->last_tipp !== '0000-00-00 00:00:00'
                    ? HTMLHelper::date($this->predictionMember->last_tipp, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE'))
                    : Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NEVER'); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_fav_team'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_FAVTEAMS'); ?></th>
                <td><?php echo $renderTeams($this->favouriteTeams, 'COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NO_FAVTEAM'); ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_CHAMPIONS'); ?></th>
            <td><?php echo $renderTeams($this->championTips, 'COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NO_CHAMP'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_FINAL4'); ?></th>
            <td><?php echo $renderTeams($this->final4Tips, 'COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NO_FINAL4TEAM'); ?></td>
        </tr>
        <?php if (!empty($this->config['show_ranking'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_RANK'); ?></th>
                <td><?php echo !empty($stats['rank']) ? Text::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_RANK_OUTPUT', (int) $stats['rank']) : '-'; ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_totalpoints'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_TOTAL_POINTS'); ?></th>
                <td><?php echo (int) ($stats['totalPoints'] ?? 0); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_lastpoints'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_LAST_ROUND'); ?></th>
                <td><?php echo (int) ($stats['lastPoints'] ?? 0); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_counttipps'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_PRED_COUNT'); ?></th>
                <td><?php echo (int) ($stats['predictionsCount'] ?? 0); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_averagepoints'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_AVERAGE_POINTS'); ?></th>
                <td><?php echo number_format((float) ($stats['averagePoints'] ?? 0), 2); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_toptipps'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_TOPS'); ?></th>
                <td><?php echo (int) ($stats['totalTop'] ?? 0); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_difftipps'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_MARGINS'); ?></th>
                <td><?php echo (int) ($stats['totalDiff'] ?? 0); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($this->config['show_tendtipps'])) : ?>
            <tr>
                <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_TENDENCIES'); ?></th>
                <td><?php echo (int) ($stats['totalTend'] ?? 0); ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
