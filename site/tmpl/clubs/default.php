<?php
/** SportsManagement clubs template for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="clubs">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <div class="<?php echo $this->escape($this->divclassrow); ?>" id="sectionheader">
            <p><strong><?php echo $this->escape($this->headertitle); ?></strong></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->config['show_clubs'])) : ?>
        <?php echo $this->loadTemplate('clubs'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
