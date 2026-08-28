<?php
/** Native results not-playing-teams output. */
defined('_JEXEC') or die('Restricted access');

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
