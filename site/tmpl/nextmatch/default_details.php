<?php
/**
 * Native Joomla 5/6 next-match details.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$databaseSelector = $this->input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
$seasonId = max(0, $this->input->getInt('s', 0));
$projectRoute = $this->project->slug ?? $this->project->id ?? 0;
?>
<div class="<?php echo $escape($this->divclassrow); ?> table-responsive" id="nextmatch-details">
    <?php
    $this->notes = [Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DETAILS')];
    echo $this->loadTemplate('jsm_notes');
    ?>

    <table class="table">
        <?php if ((int) ($this->match->old_match_id ?? 0) > 0) : ?>
            <?php
            $oldLink = SiteRouteHelper::view('matchreport', [
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
                'p' => $projectRoute,
                'mid' => (int) $this->match->old_match_id,
            ]);
            ?>
            <tr>
                <td colspan="3">
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_OLD_MATCH'); ?></span>
                    <span><?php echo HTMLHelper::link($oldLink, $escape($this->oldmatchtext)); ?></span>
                </td>
            </tr>
        <?php endif; ?>

        <?php if ((int) ($this->match->new_match_id ?? 0) > 0) : ?>
            <?php
            $newLink = SiteRouteHelper::view('nextmatch', [
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
                'p' => $projectRoute,
                'mid' => (int) $this->match->new_match_id,
            ]);
            ?>
            <tr>
                <td colspan="3">
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_NEW_MATCH'); ?></span>
                    <span><?php echo HTMLHelper::link($newLink, $escape($this->newmatchtext)); ?></span>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_date']) && !empty($this->match->match_date)) : ?>
            <tr>
                <td colspan="3">
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATE'); ?></span>
                    <span><?php echo HTMLHelper::date(
                        (string) $this->match->match_date,
                        Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE')
                    ); ?></span>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_time']) && !empty($this->match->match_date)) : ?>
            <tr>
                <td colspan="3">
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_TIME'); ?></span>
                    <span><?php echo MatchTimeHelper::format(
                        $this->match,
                        $this->config,
                        $this->overallconfig,
                        $this->project
                    ); ?></span>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_time_present']) && !empty($this->match->time_present)) : ?>
            <tr>
                <td colspan="3">
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PRESENT'); ?></span>
                    <span><?php echo $escape($this->match->time_present); ?></span>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_number']) && !empty($this->match->match_number)) : ?>
            <tr>
                <td colspan="3">
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_NUMBER'); ?></span>
                    <span><?php echo $escape($this->match->match_number); ?></span>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->match->cancel)) : ?>
            <tr>
                <td colspan="3">
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_CANCEL_REASON'); ?></span>
                    <span><?php echo $escape($this->match->cancel_reason ?? ''); ?></span>
                </td>
            </tr>
        <?php endif; ?>

        <tr><td colspan="3">&nbsp;</td></tr>

        <?php if (!empty($this->config['show_match_playground']) && (int) ($this->match->playground_id ?? 0) > 0) : ?>
            <?php
            $playgroundLink = SiteRouteHelper::view('playground', [
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
                'p' => $projectRoute,
                'pgid' => $this->match->playground_slug ?? (int) $this->match->playground_id,
            ]);
            ?>
            <tr>
                <td colspan="3">
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PLAYGROUND'); ?></span>
                    <span>
                        <?php echo isset($this->playground->name)
                            ? HTMLHelper::link($playgroundLink, $escape($this->playground->name))
                            : Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PLAYGROUND_NO_ASSIGN'); ?>
                    </span>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_referees']) && $this->referees) : ?>
            <?php
            $refereeLinks = [];
            foreach ($this->referees as $referee) {
                $name = PersonNameFormatter::format(
                    null,
                    (string) ($referee->firstname ?? ''),
                    (string) ($referee->nickname ?? ''),
                    (string) ($referee->lastname ?? ''),
                    $this->config['name_format'] ?? 0
                );
                $link = SiteRouteHelper::view('referee', [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => $projectRoute,
                    'pid' => (int) ($referee->person_id ?? 0),
                ]);
                $position = trim((string) ($referee->position_name ?? ''));
                $label = HTMLHelper::link($link, $name);
                if ($position !== '') {
                    $label .= ' (' . $escape(Text::_($position)) . ')';
                }
                $refereeLinks[] = $label;
            }
            ?>
            <tr>
                <td colspan="3">
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_REFEREE'); ?></span>
                    <span><?php echo implode(', ', $refereeLinks); ?></span>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <br>
</div>
