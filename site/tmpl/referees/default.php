<?php
/**
 * Native Joomla 5/6 referees layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

if (!isset($this->config['show_referees'])) {
    $this->config['show_referees'] = 1;
}
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="referees">
    <?php if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) : ?>
        <?php echo $this->loadTemplate('debug'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader']) && $this->headertitle !== '') : ?>
        <div class="<?php echo $this->escape($this->divclassrow); ?>" id="sectionheader">
            <table class="table">
                <tr>
                    <td class="contentheading"><?php echo $this->escape($this->headertitle); ?></td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->config['show_referees'])) : ?>
        <?php echo $this->loadTemplate('referees'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
