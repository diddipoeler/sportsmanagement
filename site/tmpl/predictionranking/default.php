<?php
\defined('_JEXEC') or die;
?>
<div class="<?php echo htmlspecialchars($this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>" id="defaultpredictionranking">
    <?php echo $this->loadTemplate('predictionheading'); ?>
    <?php echo $this->loadTemplate('sectionheader'); ?>
    <?php echo $this->loadTemplate('ranking'); ?>

    <?php if (!empty($this->config['show_all_user_google_map']) && $this->predictionKmlUrl) : ?>
        <?php echo $this->loadTemplate('maps'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_help'])) : ?>
        <?php echo $this->loadTemplate('show_help'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
