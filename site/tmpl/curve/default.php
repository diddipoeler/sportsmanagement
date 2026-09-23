<?php
/**
 * Native Joomla 5/6 curve layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="curve">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <?php echo $this->loadTemplate('sectionheader'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['which_curve'])) : ?>
        <?php echo $this->loadTemplate('curvejs'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_colorlegend'])) : ?>
        <?php echo $this->loadTemplate('colorlegend'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
