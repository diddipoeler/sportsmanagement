<?php
/** Joomla 5/6 individual-sport pairing generator. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$homeById = [];
foreach ($this->homeplayers as $player) {
    $homeById[(int) $player->season_team_person_id] = $player;
}

$awayById = [];
foreach ($this->awayplayers as $player) {
    $awayById[(int) $player->season_team_person_id] = $player;
}
?>
<?php if ($this->debug) : ?>
    <details>
        <summary>Individual match generator debug</summary>
        <pre><?php echo htmlspecialchars(print_r([
            'homeplayers' => $this->homeplayers,
            'awayplayers' => $this->awayplayers,
            'show_matches' => $this->show_matches,
        ], true), ENT_QUOTES, 'UTF-8'); ?></pre>
    </details>
<?php endif; ?>

<div class="table-responsive" id="editcell">
    <form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
        <fieldset>
            <div class="fltlft">
                <button type="button" data-jsm-action="submit-task" data-task="jlextindividualsportes.generatematchsingles">
                    <?php echo Text::_('JAPPLY'); ?>
                </button>
            </div>
        </fieldset>

        <table class="table">
            <tbody>
                <?php foreach ($this->show_matches as $item) : ?>
                    <?php
                    $homeId = (int) $item->teamplayer1_id;
                    $awayId = (int) $item->teamplayer2_id;
                    $home = $homeById[$homeId] ?? null;
                    $away = $awayById[$awayId] ?? null;
                    $matchType = ($item->teamplayer1_position === 'Double' || $item->teamplayer2_position === 'Double') ? 'DOUBLE' : 'SINGLE';
                    ?>
                    <tr>
                        <td>
                            <input type="hidden" name="match_type[]" value="<?php echo $matchType; ?>">
                            <?php echo $item->teamplayer1_position; ?>
                        </td>
                        <td><?php echo $item->teamplayer2_position; ?></td>
                        <?php if ($home) : ?>
                            <td>
                                <input type="hidden" name="teamplayer1_id[]" value="<?php echo $homeId; ?>">
                                <?php echo $home->lastname; ?>
                            </td>
                            <td><?php echo $home->firstname; ?></td>
                        <?php endif; ?>
                        <?php if ($away) : ?>
                            <td>
                                <input type="hidden" name="teamplayer2_id[]" value="<?php echo $awayId; ?>">
                                <?php echo $away->lastname; ?>
                            </td>
                            <td><?php echo $away->firstname; ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <input type="hidden" name="project_id" value="<?php echo $this->pid; ?>">
        <input type="hidden" name="match_id" value="<?php echo $this->id; ?>">
        <input type="hidden" name="projectteam1_id" value="<?php echo $this->projectteam1_id; ?>">
        <input type="hidden" name="projectteam2_id" value="<?php echo $this->projectteam2_id; ?>">
        <input type="hidden" name="round_id" value="<?php echo $this->rid; ?>">
        <input type="hidden" name="task" value="" id="task">
        <?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </form>
</div>
