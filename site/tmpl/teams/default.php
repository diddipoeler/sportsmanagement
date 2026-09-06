<?php
/**
 * SportsManagement teams template for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="defaultteams">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <div class="<?php echo $this->escape($this->divclassrow); ?>" id="sectionheader">
            <p><strong><?php echo $this->escape($this->headertitle); ?></strong></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->config['show_teams'])) : ?>
        <?php echo $this->loadTemplate('teams'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
