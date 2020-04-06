<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage jsmgcalendars
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>

    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
            <tr>
                <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_NAME_ID_LABEL'); ?></th>
                <th width="20"><input type="checkbox" name="toggle" value=""
                    onclick="checkAll(<?php echo count($this->items); ?>);" /></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_NAME_LABEL'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_COLOR_LABEL'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_CALENDAR_ID_LABEL'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_COLUMN_AUTHENTICATION'); ?></th>
            </tr>
        </thead>
        <tbody>
    <?php foreach($this->items as $i => $item){?>
            <tr class="row<?php echo $i % 2; ?>">
                <td><?php echo $item->id; ?></td>
                <td>
        <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                </td>
                <td>
                    <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=jsmgcalendar.edit&id='. $item->id); ?>">
        <?php echo $item->name; ?>
                    </a>
                </td>
                <td width="40px"><div style="background-color: <?php echo $item->color; ?>;width: 40px;height: 40px;"></div></td>
                <td style="border: 0;"><?php echo urldecode($item->calendar_id); ?></td>
                <td style="border: 0;"><?php
                if(!empty($item->magic_cookie)) {
                    echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_FIELD_MAGIC_COOKIE_LABEL');
                } else if(!empty($item->username)) {
                    echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_COLUMN_AUTHENTICATION_USERNAME');
                } else {
                    echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_COLUMN_AUTHENTICATION_NO');
                }
                ?></td>
            </tr>
    <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">
        <?php echo $this->pagination->getListFooter(); ?>
                    <br/><br/>
                    
                </td>
            </tr>
        </tfoot>
    </table>


