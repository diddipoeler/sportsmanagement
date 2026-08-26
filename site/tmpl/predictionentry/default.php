<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$identityId = (int) ($this->actJoomlaUser->id ?? 0);
?>
<div class="<?php echo htmlspecialchars($this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>" id="prediction-entry">
    <?php echo $this->loadTemplate('predictionheading'); ?>
    <?php echo $this->loadTemplate('sectionheader'); ?>

    <?php if ($identityId <= 0) : ?>
        <?php echo $this->loadTemplate('view_deny'); ?>
    <?php elseif ((!$this->isPredictionMember && !$this->allowedAdmin) || ($this->isNotApprovedMember && !$this->allowedAdmin)) : ?>
        <?php echo $this->loadTemplate('view_not_member'); ?>
    <?php elseif ($this->allowedAdmin && $this->predictionMemberID <= 0) : ?>
        <div class="alert alert-info"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_SELECT_EXISTING_MEMBER'); ?></div>
    <?php elseif ($this->canEnterTips) : ?>
        <?php if ($this->isNewMember) : ?>
            <?php echo $this->loadTemplate('view_welcome'); ?>
        <?php endif; ?>

        <?php if ($this->tippEntryDone) : ?>
            <?php echo $this->loadTemplate('view_tippentry_done'); ?>
        <?php endif; ?>

        <?php if ((int) ($this->config['show_help'] ?? 3) === 0 || (int) ($this->config['show_help'] ?? 3) === 2) : ?>
            <?php echo $this->helpText(); ?>
        <?php endif; ?>

        <?php echo $this->loadTemplate('view_tippentry_do'); ?>

        <?php if ((int) ($this->config['show_help'] ?? 3) === 1 || (int) ($this->config['show_help'] ?? 3) === 2) : ?>
            <?php echo $this->helpText(); ?>
        <?php endif; ?>
    <?php else : ?>
        <div class="alert alert-warning"><?php echo Text::_('JERROR_ALERTNOAUTHOR'); ?></div>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
