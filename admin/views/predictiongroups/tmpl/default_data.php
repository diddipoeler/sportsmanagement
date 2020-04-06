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
 * @subpackage predictiongroups
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

//Ordering allowed ?
$ordering = ($this->sortColumn == 's.ordering');


?>
    <div id="editcell">
        <table class="<?php echo $this->table_data_class; ?>">
            <thead>
                <tr>
                    <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
                    <th width="20">
                        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                    </th>
                    <th width="20">&nbsp;</th>
                    <th>
        <?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_NAME', 's.name', $this->sortDirection, $this->sortColumn); ?>
                    </th>
                    <th width="10%">
        <?php
        echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 's.ordering', $this->sortDirection, $this->sortColumn);
        echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'predictiongroups.saveorder');
        ?>
                    </th>
                    <th width="20">
        <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 's.id', $this->sortDirection, $this->sortColumn); ?>
                    </th>
                  
                    <th width="" class="title">
        <?php
        echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL');
        ?>
                    </th>
                    <th width="" class="title">
        <?php
        echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL');
        ?>
                    </th>
                  
                </tr>
            </thead>
            <tfoot>
            <tr>
            <td colspan="6"><?php echo $this->pagination->getListFooter(); ?>
            </td>
            </td>
                    <td colspan="2"><?php echo $this->pagination->getResultsCounter(); ?>
            </td>
            </tr>
            </tfoot>
            <tbody>
                <?php
                $k=0;
                for ($i=0,$n=count($this->items); $i < $n; $i++)
                {
                    $row =& $this->items[$i];
                    $link = Route::_('index.php?option=com_sportsmanagement&task=predictiongroup.edit&id='.$row->id);
                    $canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
                    $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
                    $checked = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'predictiongroups.', $canCheckin);
        ?>
                    <tr class="<?php echo "row$k"; ?>">
                        <td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
                        <td class="center"><?php echo HTMLHelper::_('grid.id', $i, $row->id); ?></td>
        <?php
                      
        $inputappend='';
        ?>
                            <td class="center">
                            <?php
                            if ($row->checked_out) : ?>
            <?php echo HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'predictiongroups.', $canCheckin); ?>
                            <?php endif; ?>
                                <a href="<?php echo $link; ?>">
            <?php
            $imageTitle=Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_EDIT_DETAILS');
            echo HTMLHelper::_(
                'image', 'administrator/components/com_sportsmanagement/assets/images/edit.png',
                $imageTitle, 'title= "'.$imageTitle.'"'
            );
            ?>
                                </a>
                            </td>
        <?php
                      
        ?>
                        <td><?php echo $row->name; ?></td>
                        <td class="order">
                            <span>
                                <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'predictiongroups.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?>
                            </span>
                            <span>
                                <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'predictiongroups.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?>
                                <?php $disabled=true ? '' : 'disabled="disabled"'; ?>
                            </span>
                            <input    type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?>
                                    class="text_area" style="text-align: center" />
                        </td>
                        <td class="center"><?php echo $row->id; ?></td>
                        <td><?php echo $row->modified; ?></td>
                            <td><?php echo $row->username; ?></td>
                    </tr>
        <?php
        $k=1 - $k;
                }
                ?>
            </tbody>
        </table>
    </div>
