<?php
/** SportsManagement DFB.net import update result. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

$showDebug = (bool) ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info_backend', 0);
?>
<div id="editcell">
    <a name="page_top"></a>
    <table class="adminlist">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_UPDATE_MATCH_DATA_TITLE'); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr><td>&nbsp;</td></tr>
        </tbody>
    </table>

    <?php if (is_array($this->importData)) : ?>
        <?php foreach ($this->importData as $key => $value) : ?>
            <fieldset>
                <legend><?php echo Text::_((string) $key); ?></legend>
                <table class="adminlist">
                    <tr><td><?php echo $value; ?></td></tr>
                </table>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($showDebug) : ?>
        <fieldset>
            <legend><?php echo Text::_('Post data from importform was:'); ?></legend>
            <table class="adminlist">
                <tr>
                    <td><pre><?php echo htmlspecialchars(print_r($this->postData, true), ENT_QUOTES, 'UTF-8'); ?></pre></td>
                </tr>
            </table>
        </fieldset>
    <?php endif; ?>
</div>
<p style="text-align:right;"><a href="#page_top"><?php echo Text::_('top'); ?></a></p>
<?php if ($showDebug) : ?>
    <div class="text-center">
        <hr>
        <?php echo Text::sprintf('Memory Limit is %1$s', ini_get('memory_limit')); ?><br />
        <?php echo Text::sprintf('Memory Peak Usage was %1$s Bytes', number_format(memory_get_peak_usage(true), 0, '', '.')); ?><br />
        <?php echo Text::sprintf('Time Limit is %1$s seconds', ini_get('max_execution_time')); ?><br />
        <?php echo Text::sprintf('This page was created in %1$s seconds', microtime(true) - $this->starttime); ?>
        <hr>
    </div>
<?php endif; ?>
