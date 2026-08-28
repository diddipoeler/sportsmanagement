<?php
/**
 * Native Joomla 5/6 next-match layout.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<div class="<?php echo htmlspecialchars((string) $this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>" id="nextmatch">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if ($this->match) : ?>
        <?php if (!empty($this->config['show_sectionheader'])) : ?>
            <?php echo $this->loadTemplate('sectionheader'); ?>
        <?php endif; ?>

        <?php if (!empty($this->config['show_nextmatch'])) : ?>
            <?php echo $this->loadTemplate('nextmatch'); ?>
        <?php endif; ?>

        <?php
        $this->output = [];

        if (!empty($this->config['show_details'])) {
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_DETAILS'] = 'details';
        }
        if (!empty($this->config['show_preview'])) {
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIEW'] = 'preview';
        }
        if (!empty($this->config['show_comments'])) {
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_COMMENTS'] = 'comments';
        }
        if (!empty($this->config['show_stats'])) {
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_H2H'] = 'stats';
        }
        if (!empty($this->config['show_history'])) {
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY'] = 'history';
        }
        if (!empty($this->config['show_events'])) {
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_ALLOVEREVENTSRANKING'] = 'allovereventsranking';
        }
        if (!empty($this->config['show_previousx'])) {
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIOUS'] = 'previousx';
        }
        if (!empty($this->config['show_commentary']) && $this->matchcommentary) {
            $this->output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY'] = 'commentary';
        }

        echo $this->loadTemplate((string) ($this->config['show_nextmatch_tabs'] ?? 'no_tabs'));
        echo $this->loadTemplate('jsminfo');
        ?>
    <?php else : ?>
        <p><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_NO_MORE_MATCHES'); ?></p>
    <?php endif; ?>
</div>
