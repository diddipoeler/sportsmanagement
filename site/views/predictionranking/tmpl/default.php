<?php
/**
 * Joomla 5/6 prediction ranking compatibility layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
