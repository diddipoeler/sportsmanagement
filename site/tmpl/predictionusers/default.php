<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<div class="<?php echo htmlspecialchars($this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>" id="defaultpredictionusers">
    <?php echo $this->loadTemplate('predictionheading'); ?>

    <?php if ($this->predictionMemberID > 0) : ?>
        <?php echo $this->loadTemplate('sectionheader'); ?>

        <?php if ($this->canViewProfile) : ?>
            <?php echo $this->loadTemplate('info'); ?>

            <?php if (!empty($this->config['show_flash_statistic_points'])) : ?>
                <?php echo $this->loadTemplate('pointsflashchart'); ?>
            <?php endif; ?>

            <?php if (!empty($this->config['show_flash_statistic_ranks'])) : ?>
                <?php echo $this->loadTemplate('rankflashchart'); ?>
            <?php endif; ?>
        <?php else : ?>
            <div class="alert alert-info">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_MEMBER_NO_PROFILE_SHOW'); ?>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SELECT_EXISTING_MEMBER'); ?></h3>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
