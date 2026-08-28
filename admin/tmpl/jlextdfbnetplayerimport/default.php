<?php
/** SportsManagement DFB.net import form. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$url = 'administrator/components/com_sportsmanagement/assets/icons/dfbnet-logo.gif';
$alt = 'DFBNet';
$attribs = [
    'width' => '184px',
    'height' => '77px',
    'align' => 'left',
];
?>
<div id="editcell">
    <form enctype="multipart/form-data" action="<?php echo $this->escape($this->request_url); ?>" method="post" name="adminForm" id="adminForm">
        <table class="table">
            <thead>
            <tr>
                <th>
                    <?php echo HTMLHelper::_('image', $url, $alt, $attribs); ?>
                    <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_TABLE_TITLE_1', $this->config->get('upload_maxsize')); ?>
                </th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td>
                    <br />
                    <b><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_EXTENTION_INFO'); ?></b><br />
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_HINT1'); ?><br />
                    <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_HINT2', $this->revisionDate); ?>
                </td>
            </tr>
            </tfoot>
            <tbody>
            <tr>
                <td>
                    <fieldset>
                        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_WHICH_FILE'); ?></legend>
                        <input type="radio" name="whichfile" value="playerfile" checked>
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_PLAYERFILE'); ?>
                        <br><br>
                        <input type="radio" name="whichfile" value="matchfile">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE'); ?>
                        <br><br>
                        <input type="radio" name="whichfile" value="icsfile">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_ICSFILE'); ?>
                        <br>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td>
                    <fieldset>
                        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_WHICH_SEASON'); ?></legend>
                        <?php echo $this->lists['seasons']; ?>
                        <?php echo $this->lists['nation2']; ?>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td>
                    <fieldset>
                        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_FILE'); ?></legend>
                        <input class="input_box" id="import_package" name="import_package" type="file" size="57" />
                        <input class="button" type="submit"
                               onclick="return Joomla.submitform('jlextdfbnetplayerimport.save')"
                               value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_UPLOAD_BUTTON'); ?>" />
                    </fieldset>
                </td>
            </tr>
            </tbody>
        </table>
        <input type="hidden" name="sent" value="1" />
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo (int) $this->config->get('upload_maxsize'); ?>" />
        <input type="hidden" name="task" value="jlextdfbnetplayerimport.save" />
        <?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </form>
</div>
<?php echo $this->loadTemplate('footer'); ?>
