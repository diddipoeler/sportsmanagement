<?php
/** SportsManagement DFB-key first-matchday template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$templatesToLoad = ['footer', 'listheader'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_2');
echo $this->loadTemplate('jsm_notes');
echo $this->loadTemplate('jsm_tips');

$url = 'administrator/components/com_sportsmanagement/assets/images/dfb-key.jpg';
$alt = 'Lmo Logo';
$attribs = ['align' => 'left'];
?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
    <div id="editcell">
        <fieldset class="adminform">
            <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_2', $this->dfbteams); ?></legend>
            <table class="adminlist">
                <thead>
                <tr>
                    <th>
                        <?php echo HTMLHelper::_('image', $url, $alt, $attribs); ?>
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT11'); ?>
                    </th>
                </tr>
                </thead>
            </table>
            <table class="<?php echo $this->table_data_class; ?>">
                <thead>
                <tr>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_3'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_4'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_5'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_6'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_7'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_8'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_9'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ((array) ($this->lists['dfbday'] ?? []) as $rowdfb) :
                    $pairing = preg_replace('/\s+/', '', (string) $rowdfb->paarung);
                    $parts = explode(',', $pairing);
                    if (count($parts) < 2) {
                        continue;
                    }
                    $homeSlot = (int) $parts[0];
                    $awaySlot = (int) $parts[1];
                    ?>
                    <tr>
                        <td><?php echo (int) $rowdfb->schluessel; ?></td>
                        <td><?php echo (int) $rowdfb->spieltag; ?></td>
                        <td><?php echo $homeSlot; ?></td>
                        <td><?php echo HTMLHelper::_(
                            'select.genericlist',
                            $this->lists['projectteams'],
                            'chooseteam_' . $homeSlot,
                            'class="inputbox" size="1"',
                            'value',
                            'text',
                            0
                        ); ?></td>
                        <td><?php echo $awaySlot; ?></td>
                        <td><?php echo HTMLHelper::_(
                            'select.genericlist',
                            $this->lists['projectteams'],
                            'chooseteam_' . $awaySlot,
                            'class="inputbox" size="1"',
                            'value',
                            'text',
                            0
                        ); ?></td>
                        <td><?php echo (int) $rowdfb->spielnummer; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </fieldset>
    </div>
    <input type="hidden" name="sent" value="1" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="projectid" value="<?php echo (int) $this->project_id; ?>" />
    <input type="hidden" name="divisionid" value="<?php echo (int) $this->division_id; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
