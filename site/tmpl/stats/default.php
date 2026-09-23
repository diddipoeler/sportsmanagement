<?php
/**
 * Native Joomla 5/6 statistics layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

if (empty($this->project)) {
    Log::add(
        Text::_('Error: ProjectID was not submitted in URL or project was not found in database!'),
        Log::WARNING,
        'com_sportsmanagement'
    );

    return;
}
?>
<div class="<?php echo $this->escape((string) $this->divclasscontainer); ?>" id="defaultstats">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <?php echo $this->loadTemplate('sectionheader'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_general_stats'])) : ?>
        <?php echo $this->loadTemplate('stats'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_goals_stats'])) : ?>
        <?php echo $this->loadTemplate('goals_stats'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_attendance_stats'])) : ?>
        <?php echo $this->loadTemplate('attendance_stats'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_goals_stats_flash'])) : ?>
        <?php echo $this->loadTemplate('flashchart'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_attendance_ranking'])) : ?>
        <?php echo $this->loadTemplate('ranking'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
