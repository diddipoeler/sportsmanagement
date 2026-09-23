<?php
/**
 * Native Joomla 5/6 staff layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;
?>
<div class="<?php echo $this->escape((string) $this->divclasscontainer); ?>" id="staff">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <?php echo $this->loadTemplate('sectionheader'); ?>
    <?php endif; ?>

    <?php
    // Preserve the legacy output collector used by staff sublayouts.
    $this->output = [];
    ?>

    <?php if (!empty($this->config['show_info'])) : ?>
        <?php echo $this->loadTemplate('info'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_extended'])) : ?>
        <?php echo $this->loadTemplate('extended'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_status'])) : ?>
        <?php echo $this->loadTemplate('status'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_description'])) : ?>
        <?php echo $this->loadTemplate('description'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_careerstats'])) : ?>
        <?php echo $this->loadTemplate('careerstats'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_career'])) : ?>
        <?php echo $this->loadTemplate('career'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
