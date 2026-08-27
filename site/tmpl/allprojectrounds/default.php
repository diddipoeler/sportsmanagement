<?php
/** SportsManagement all project rounds template for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="allprojectrounds-view">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <div class="<?php echo $this->escape($this->divclassrow); ?>" id="sectionheader">
            <p><strong><?php echo $this->escape($this->headertitle); ?></strong></p>
        </div>
    <?php endif; ?>

    <?php echo $this->loadTemplate('results_all'); ?>
    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
