<?php
/**
 * Native Joomla 5/6 section header for the next-match view.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!$this->match) {
    return;
}

$matchDate = (string) ($this->match->match_date ?? '');
?>
<table class="table">
    <tr>
        <td class="contentheading">
            <?php if ($matchDate === '' || str_starts_with($matchDate, '0000-00-00')) : ?>
                <?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATE_EMPTY'); ?>
            <?php else : ?>
                <?php
                echo HTMLHelper::date($matchDate, Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE'));
                $matchTime = MatchTimeHelper::format(
                    $this->match,
                    $this->config,
                    $this->overallconfig,
                    $this->project
                );
                if ($matchTime !== '') {
                    echo ' ' . $matchTime;
                }
                ?>
            <?php endif; ?>
        </td>
    </tr>
</table>
