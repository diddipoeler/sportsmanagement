<?php
/**
 * Joomla 5/6 match comments integration for the next-match view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchCommentsHelper;

if (!$this->match || !isset($this->teams[0], $this->teams[1])) {
    return;
}

$comments = MatchCommentsHelper::render(
    $this->match,
    $this->teams[0],
    $this->teams[1],
    $this->config,
    $this->project
);

if ($comments === '') {
    return;
}
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatch-comments">
    <?php echo $comments; ?>
</div>
