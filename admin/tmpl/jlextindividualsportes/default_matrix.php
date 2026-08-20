<?php
/** Joomla 5/6 player-pairing matrix for individual-sport matches. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$homePlayers = $this->getHomePlayer;
$awayPlayers = $this->getAwayPlayer;
$existingPairs = [];

foreach ($this->matches as $match) {
    $existingPairs[(int) $match->teamplayer1_id . ':' . (int) $match->teamplayer2_id] = true;
}
?>

<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&id=' . $this->match_id . '&team1=' . $this->projectteam1_id . '&team2=' . $this->projectteam2_id . '&rid=' . $this->rid); ?>"
    method="post"
    name="matrixForm"
    id="matrixForm"
>
    <fieldset class="adminform">
        <legend>
            <?php echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_SINGLE_MATCHES_TITLE',
                '<i>' . $this->roundws->name . '</i>',
                '<i>' . $this->projectws->name . '</i>'
            ); ?>
        </legend>

        <div class="alert alert-info">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATRIX_HINT'); ?>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle jsm-individual-match-matrix">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <?php foreach ($awayPlayers as $awayPlayer) : ?>
                            <th
                                scope="col"
                                class="rotated_cell text-center"
                                title="<?php echo $this->escape($awayPlayer->text); ?>"
                            >
                                <div class="rotate_text"><?php echo $this->escape($awayPlayer->text); ?></div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($homePlayers as $rowIndex => $homePlayer) : ?>
                        <tr class="row<?php echo $rowIndex % 2; ?>">
                            <th scope="row"><?php echo $this->escape($homePlayer->text); ?></th>

                            <?php foreach ($awayPlayers as $awayPlayer) : ?>
                                <?php
                                $homeId = (int) $homePlayer->value;
                                $awayId = (int) $awayPlayer->value;
                                $exists = isset($existingPairs[$homeId . ':' . $awayId]);
                                $cellClass = $exists ? 'jsm-matrix-existing' : 'jsm-matrix-available';
                                ?>
                                <td
                                    class="text-center <?php echo $cellClass; ?>"
                                    title="<?php echo $this->escape($homePlayer->text . ' - ' . $awayPlayer->text); ?>"
                                >
                                    <input
                                        type="radio"
                                        name="match_<?php echo $homeId . $awayId; ?>"
                                        <?php echo $exists ? 'checked' : ''; ?>
                                        <?php if (!$exists) : ?>
                                            data-jsm-action="save-match"
                                            data-home-player="<?php echo $homeId; ?>"
                                            data-away-player="<?php echo $awayId; ?>"
                                        <?php endif; ?>
                                    >
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </fieldset>

    <?php $matchDate = $this->roundws->round_date_first . ' ' . $this->projectws->start_time; ?>
    <input type="hidden" name="match_date" value="<?php echo $matchDate; ?>">
    <input type="hidden" name="projectteam1_id" value="<?php echo $this->projectteam1_id; ?>">
    <input type="hidden" name="projectteam2_id" value="<?php echo $this->projectteam2_id; ?>">
    <input type="hidden" name="match_id" value="<?php echo $this->match_id; ?>">
    <input type="hidden" name="teamplayer1_id" value="">
    <input type="hidden" name="teamplayer2_id" value="">
    <input type="hidden" name="published" value="1">
    <input type="hidden" name="task" value="jlextindividualsport.addmatch">
    <?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
