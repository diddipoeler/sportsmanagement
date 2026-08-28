<?php
/** SportsManagement DFB-key match creation template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_12');
echo $this->loadTemplate('jsm_notes');
echo $this->loadTemplate('jsm_tips');
?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
    <div id="editcell">
        <fieldset class="adminform">
            <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_2', $this->project_id); ?></legend>
            <table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
                <thead>
                <tr>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_3'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_4'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_5'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_6'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_7'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ((array) $this->import as $i => $rowdays) : ?>
                    <tr>
                        <td>
                            <input type="hidden" name="round_id[]" value="<?php echo (int) $rowdays->round_id; ?>" />
                            <input type="hidden" name="roundcode[]" value="<?php echo (int) $rowdays->spieltag; ?>" />
                            <?php echo (int) $rowdays->spieltag; ?>
                        </td>
                        <td>
                            <input type="hidden" name="match_number[]" value="<?php echo (int) $rowdays->spielnummer; ?>" />
                            <?php echo (int) $rowdays->spielnummer; ?>
                        </td>
                        <td>
                            <input type="hidden" name="projectteam1_id[]" value="<?php echo (int) $rowdays->projectteam1_id; ?>" />
                            <?php echo $this->escape($rowdays->projectteam1_name); ?>
                        </td>
                        <td>
                            <input type="hidden" name="projectteam2_id[]" value="<?php echo (int) $rowdays->projectteam2_id; ?>" />
                            <?php echo $this->escape($rowdays->projectteam2_name); ?>
                        </td>
                        <td>
                            <?php
                            $date = Factory::getDate($rowdays->match_date)->format('d-m-Y');
                            echo HTMLHelper::calendar(
                                $date,
                                'match_date[' . $i . ']',
                                'match_date[' . $i . ']',
                                '%d-%m-%Y',
                                'size="10" style="background-color:#bbffff;"'
                            );
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </fieldset>
    </div>
    <input type="hidden" name="sent" value="3" />
    <input type="hidden" name="projectid" value="<?php echo (int) $this->project_id; ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
