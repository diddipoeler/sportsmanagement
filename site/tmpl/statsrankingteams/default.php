<?php
/**
 * Native Joomla 5/6 team statistics ranking layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;
?>
<div>
    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <?php echo $this->loadTemplate('sectionheader'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('projectheading'); ?>
    <?php echo $this->loadTemplate('stats'); ?>
    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
