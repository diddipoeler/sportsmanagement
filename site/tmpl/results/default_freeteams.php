<?php
/**
 * Native Joomla 5/6 results not-playing-teams layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ResultsPresentationHelper;

$output = ResultsPresentationHelper::renderNotPlayingTeams(
    $this->matches,
    array_values($this->teams),
    $this->config,
    $this->favteams,
    $this->project,
    $this->cfg_which_database,
    $this->season_id,
    $this->modalwidth,
    $this->modalheight,
    (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
);
?>
<?php if ($output !== '') : ?>
    <div class="text-center my-3 results-not-playing">
        <?php echo $output; ?>
    </div>
<?php endif; ?>
