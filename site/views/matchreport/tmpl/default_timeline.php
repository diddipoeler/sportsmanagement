<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_timeline.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * The legacy timeline formatter divides event/substitution minutes by the
 * match duration. Do not render a timeline when the duration cannot be
 * determined safely; this avoids undefined values and division-by-zero under
 * PHP 8 / Joomla 5/6.
 */
$resultType = (int) ($this->match->match_result_type ?? 0);
$regularTime = (int) ($this->project->game_regular_time ?? 0);

if (!in_array($resultType, [0, 1, 2], true) || $regularTime <= 0 || !$this->team1 || !$this->team2) {
    return;
}

$tooltipPlacement = (string) ($this->config['which_position_tooltip_subst'] ?? 'bottom');
$useJqueryModal = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$modalWidth = (int) ($this->modalwidth ?? 100);
$modalHeight = (int) ($this->modalheight ?? 200);
$divClassRow = (string) ($this->divclassrow ?? '');
$imageRoot = rtrim(Uri::root(true), '/') . '/images/com_sportsmanagement/database/matchreport/';

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['placement' => $tooltipPlacement]);
?>
<!-- START of match timeline -->
<div class="<?php echo $divClassRow; ?>" id="matchreport-timeline">
    <script type="text/javascript">
        function gotoevent(row) {
            const target = document.getElementById('event-' + row);
            if (target) {
                target.scrollIntoView();
            }
        }
    </script>
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE'); ?></h2>
    <div id="timelineheader" class="row-fluid" style="position:relative;height:15px;">
        <div id="timelinetop" style="position:relative;width:100%;">
            <div id="firsthalftime"
                 style="position:absolute; top:0; left:0; width:50%; height:15px;text-align:center;color:#fff;background-color:lightgrey;">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE_FIRST_HALF'); ?>
            </div>
            <div id="secondhalftime"
                 style="position:absolute; top:0; left:50%; width:50%; height:15px;text-align:center;color:#fff;background-color:grey;">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE_SECOND_HALF'); ?>
            </div>
        </div>
    </div>

    <div id="matchreport-homeline">
        <div class="matchreport-timeline-logo">
            <?php
            echo sportsmanagementModelProject::getClubIconHtml(
                $this->team1,
                1,
                0,
                'logo_big',
                Factory::getApplication()->getInput()->getInt('cfg_which_database', 0),
                0,
                $modalWidth,
                $modalHeight,
                $useJqueryModal
            );
            ?>
        </div>
        <div>
            <?php
            echo $this->showSubstitution_Timelines(0, 'projectteam1_id');
            echo $this->showEvents_Timelines(0, 0, 'projectteam1_id');
            ?>
        </div>
    </div>

    <div id="matchreport-guestline">
        <div class="matchreport-timeline-logo">
            <?php
            echo sportsmanagementModelProject::getClubIconHtml(
                $this->team2,
                1,
                0,
                'logo_big',
                Factory::getApplication()->getInput()->getInt('cfg_which_database', 0),
                0,
                $modalWidth,
                $modalHeight,
                $useJqueryModal
            );
            ?>
        </div>
        <div>
            <?php
            echo $this->showSubstitution_Timelines(0, 'projectteam2_id');
            echo $this->showEvents_Timelines(0, 0, 'projectteam2_id');
            ?>
        </div>
    </div>
</div>

<?php
if (!$this->playgroundheight) {
    $this->playgroundheight = 2;
}
$timelineHeight = max(2, (int) $this->playgroundheight) * 25;
?>

<style>
#matchreport-homeline,
#matchreport-guestline {
    position: relative;
    height: <?php echo $timelineHeight; ?>px;
    background-repeat: no-repeat;
    background-size: 100% <?php echo max(1, $timelineHeight - 1); ?>px;
}

#matchreport-homeline {
    background-image: url("<?php echo $imageRoot; ?>spielfeld_top.png");
}

#matchreport-guestline {
    background-image: url("<?php echo $imageRoot; ?>spielfeld_bottom.png");
    vertical-align: baseline;
}

.matchreport-timeline-logo {
    position: absolute;
    left: -15px;
    top: 0;
    z-index: 1;
}
</style>
