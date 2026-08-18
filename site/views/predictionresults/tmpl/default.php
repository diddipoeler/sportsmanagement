<?php
\defined('_JEXEC') or die;
?>
<div class="<?php echo htmlspecialchars($this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>" id="defaultpredictionresults">
    <?php echo $this->loadTemplate('predictionheading'); ?>
    <?php echo $this->loadTemplate('sectionheader'); ?>
    <?php echo $this->loadTemplate('results'); ?>

    <?php if (!empty($this->config['show_help'])) : ?>
        <?php echo $this->loadTemplate('show_help'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
