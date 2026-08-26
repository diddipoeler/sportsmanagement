<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (!$this->resultsProject) {
    return;
}

$projectOptions = $this->projectOptions;
$roundOptions = $this->roundOptions;
$groupOptions = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_GROUPS'), 'value', 'text')];
$groupOptions = array_merge($groupOptions, $this->groupOptions);
$cellStyle = "text-align:center; vertical-align:middle;";
$separator = (string) ($this->config['seperator'] ?? ':');
?>

<form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" method="post" class="mb-3">
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="view" value="predictionresults">
    <input type="hidden" name="prediction_id" value="<?php echo (int) $this->predictionGameID; ?>">
    <input type="hidden" name="cfg_which_database" value="<?php echo (int) $this->databaseSelector; ?>">
    <input type="hidden" name="task" value="predictionresults.selectprojectround">

    <div class="table-responsive">
        <table class="table">
            <tbody>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_SUBTITLE_01'); ?></th>
                <td>
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $roundOptions,
                        'r',
                        'class="form-select" onchange="this.form.submit()"',
                        'value',
                        'text',
                        $this->selectedRoundID
                    ); ?>
                </td>
                <td>
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $projectOptions,
                        'pj',
                        'class="form-select" onchange="this.form.submit()"',
                        'value',
                        'text',
                        $this->projectID
                    ); ?>
                </td>
                <td>
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $groupOptions,
                        'pggroup',
                        'class="form-select" onchange="this.form.submit()"',
                        'value',
                        'text',
                        $this->predictionGroupID
                    ); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php if ($this->allowedAdmin) : ?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" method="post" class="mb-3">
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="view" value="predictionresults">
    <input type="hidden" name="prediction_id" value="<?php echo (int) $this->predictionGameID; ?>">
    <input type="hidden" name="pj" value="<?php echo (int) $this->projectID; ?>">
    <input type="hidden" name="r" value="<?php echo (int) $this->selectedRoundID; ?>">
    <input type="hidden" name="pggroup" value="<?php echo (int) $this->predictionGroupID; ?>">
    <input type="hidden" name="cfg_which_database" value="<?php echo (int) $this->databaseSelector; ?>">
    <input type="hidden" name="task" value="predictionresults.recalculatepoints">
    <button type="submit" class="btn btn-outline-secondary">
        <?php echo Text::_('JTOOLBAR_REBUILD'); ?>
    </button>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php endif; ?>

<div class="table-responsive">
    <table class="<?php echo htmlspecialchars((string) $this->config['table_class'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars((string) $this->config['table_class_responsive'], ENT_QUOTES, 'UTF-8'); ?>">
        <thead>
        <tr>
            <th style="<?php echo $cellStyle; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK'); ?></th>
            <?php if (!empty($this->config['show_user_icon'])) : ?>
                <th style="<?php echo $cellStyle; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_AVATAR'); ?></th>
            <?php endif; ?>
            <th style="<?php echo $cellStyle; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_MEMBER'); ?></th>
            <?php if (!empty($this->config['show_pred_group'])) : ?>
                <th style="<?php echo $cellStyle; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_MEMBER_GROUP'); ?></th>
            <?php endif; ?>

            <?php foreach ($this->matches as $match) : ?>
                <?php
                $homeResult = $match->homeDecision !== null ? $match->homeDecision : $match->homeResult;
                $awayResult = $match->awayDecision !== null ? $match->awayDecision : $match->awayResult;
                $resultText = ($homeResult === null ? '-' : (string) $homeResult)
                    . ' ' . $separator . ' '
                    . ($awayResult === null ? '-' : (string) $awayResult);
                ?>
                <th style="<?php echo $cellStyle; ?>">
                    <div><?php echo $this->teamVisual($match, 'home'); ?></div>
                    <?php if (!empty($this->config['show_team_names'])) : ?>
                        <div><?php echo htmlspecialchars((string) ($match->homeShortName ?: $match->homeName), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div class="hasTip" title="<?php echo htmlspecialchars(Text::sprintf('COM_SPORTSMANAGEMENT_PRED_RESULTS_RESULT_HINT', $match->homeName, $match->awayName, $resultText), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($resultText, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div><?php echo $this->teamVisual($match, 'away'); ?></div>
                    <?php if (!empty($this->config['show_team_names'])) : ?>
                        <div><?php echo htmlspecialchars((string) ($match->awayShortName ?: $match->awayName), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                </th>
            <?php endforeach; ?>

            <?php if (!empty($this->config['show_points'])) : ?>
                <th style="<?php echo $cellStyle; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_POINTS'); ?></th>
            <?php endif; ?>
            <?php if (!empty($this->config['show_average_points'])) : ?>
                <th style="<?php echo $cellStyle; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_AVERAGE'); ?></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $row) : ?>
            <?php
            $isCurrent = (int) $row['pmID'] === $this->currentPredictionMemberID;
            $rowStyle = $isCurrent
                ? 'background-color:' . htmlspecialchars((string) ($this->config['background_color_ranking'] ?? '#6F7860'), ENT_QUOTES, 'UTF-8') . ';color:black;'
                : '';
            ?>
            <tr<?php echo $rowStyle !== '' ? ' style="' . $rowStyle . '"' : ''; ?>>
                <td style="<?php echo $cellStyle; ?>"><?php echo htmlspecialchars((string) $row['rank'], ENT_QUOTES, 'UTF-8'); ?></td>
                <?php if (!empty($this->config['show_user_icon'])) : ?>
                    <td style="<?php echo $cellStyle; ?>"><?php echo $this->memberAvatar($row['member']); ?></td>
                <?php endif; ?>
                <td style="<?php echo $cellStyle; ?>"><?php echo $this->memberName($row); ?></td>
                <?php if (!empty($this->config['show_pred_group'])) : ?>
                    <td style="<?php echo $cellStyle; ?>"><?php echo htmlspecialchars((string) $row['pg_group_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <?php endif; ?>

                <?php foreach ($this->matches as $match) : ?>
                    <?php $tip = $row['matches'][(int) $match->mID] ?? null; ?>
                    <td style="<?php echo $cellStyle; ?>">
                        <?php if ($tip === null) : ?>
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOT_AVAILABLE'); ?>
                        <?php elseif (!$tip['shown']) : ?>
                            <?php echo htmlspecialchars('- ' . $separator . ' -', ENT_QUOTES, 'UTF-8'); ?>
                        <?php else : ?>
                            <?php echo htmlspecialchars((string) $tip['tip'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ($tip['points'] !== null) : ?>
                                <sub style="color:red;"><?php echo (int) $tip['points']; ?></sub>
                            <?php else : ?>
                                <sub>&nbsp;</sub>
                            <?php endif; ?>
                            <?php if (!empty($tip['joker'])) : ?>
                                <sub style="color:red;">*</sub>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>

                <?php if (!empty($this->config['show_points'])) : ?>
                    <td style="<?php echo $cellStyle; ?>"><?php echo (int) $row['totalPoints']; ?></td>
                <?php endif; ?>
                <?php if (!empty($this->config['show_average_points'])) : ?>
                    <td style="<?php echo $cellStyle; ?>">
                        <?php echo number_format(
                            (int) $row['predictionsCount'] > 0
                                ? round((int) $row['totalPoints'] / (int) $row['predictionsCount'], 2)
                                : 0,
                            2
                        ); ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($this->pagination) : ?>
<div class="pagination">
    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
    <p class="counter"><?php echo $this->pagination->getResultsCounter(); ?></p>
    <?php echo $this->pagination->getPagesLinks(); ?>
</div>
<?php endif; ?>
