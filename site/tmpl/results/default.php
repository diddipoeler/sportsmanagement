<?php
/** Native Joomla 5/6 results layout. */
defined('_JEXEC') or die('Restricted access');
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="defaultresults">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <?php echo $this->loadTemplate('sectionheader'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('results_native'); ?>

    <?php if (!empty($this->config['show_dnp_teams'])) : ?>
        <?php echo $this->loadTemplate('freeteams'); ?>
    <?php endif; ?>

    <?php if (!empty($this->overallconfig['show_project_rss_feed']) && $this->rssfeeditems) : ?>
        <?php echo $this->loadTemplate('rssfeed'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
