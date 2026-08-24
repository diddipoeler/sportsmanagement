<?php
/** SportsManagement DFB-key round creation template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$templatesToLoad = ['footer', 'listheader'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_3');
$this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_4');
echo $this->loadTemplate('jsm_notes');
echo $this->loadTemplate('jsm_tips');
?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
    <div id="editcell">
        <fieldset class="adminform">
            <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_2', $this->project_id); ?></legend>
            <table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
                <thead>
                <tr>
                    <th class="title" nowrap="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_3'); ?></th>
                    <th class="title" nowrap="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_4'); ?></th>
                    <th class="title" nowrap="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_5'); ?></th>
                    <th class="title" nowrap="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_6'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ((array) $this->newmatchdays as $i => $rowdays) : ?>
                    <tr>
                        <td>
                            <input type="hidden" name="roundcode[]" value="<?php echo (int) $rowdays->spieltag; ?>" />
                            <?php echo (int) $rowdays->spieltag; ?>
                        </td>
                        <td>
                            <input type="text" name="name[]" value="<?php echo (int) $rowdays->spieltag . Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_7'); ?>" />
                        </td>
                        <td>
                            <?php echo HTMLHelper::calendar(
                                '',
                                'round_date_first[' . $i . ']',
                                'round_date_first[' . $i . ']',
                                '%d-%m-%Y',
                                'size="10" style="background-color:#bbffff;"'
                            ); ?>
                        </td>
                        <td>
                            <?php echo HTMLHelper::calendar(
                                '',
                                'round_date_last[' . $i . ']',
                                'round_date_last[' . $i . ']',
                                '%d-%m-%Y',
                                'size="10" style="background-color:#bbffff;"'
                            ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </fieldset>
    </div>
    <input type="hidden" name="sent" value="1" />
    <input type="hidden" name="projectid" value="<?php echo (int) $this->project_id; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="divisionid" value="<?php echo (int) $this->division_id; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
