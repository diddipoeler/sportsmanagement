<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage positions
 */
defined('_JEXEC') or die('Restricted access');

//Ordering allowed ?
//$ordering=($this->sortColumn == 'po.ordering');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
            <tr>
                <th width="5">
                    <?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?>
                </th>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                </th>
                <th>
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_STANDARD_NAME_OF_POSITION', 'po.name', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th>
                    <?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_TRANSLATION'); ?>
                </th>
                <th>
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IMAGE', 'po.picture', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th>
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_PARENTNAME', 'po.parent_id', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th>
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_SPORTSTYPE', 'po.sports_type_id', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th>
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_PERSON_TYPE', 'po.persontype', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="5%">
                    <?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_HAS_EVENTS'); ?>
                </th>
                <th width="5%">
                    <?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_HAS_STATS'); ?>
                </th>
                <th width="5%">
                    <?php
                    echo JHtml::_('grid.sort', 'JSTATUS', 'po.published', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="10%">
                    <?php
                    echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'po.ordering', $this->sortDirection, $this->sortColumn);
                    echo JHtml::_('grid.order', $this->items, 'filesave.png', 'positions.saveorder');
                    ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'po.id', $this->sortDirection, $this->sortColumn); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="10">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
                <td colspan="4">
                    <?php echo $this->pagination->getResultsCounter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php
            $k = 0;
            for ($i = 0, $n = count($this->items); $i < $n; $i++) {
                $row = & $this->items[$i];
                $link = JRoute::_('index.php?option=com_sportsmanagement&task=position.edit&id=' . $row->id);
                $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
                $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
                $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'positions.', $canCheckin);
                $canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement.position.' . $row->id) && $canCheckin;
                ?>
                <tr class="<?php echo 'row' . $k; ?>">
                    <td class="center">
                        <?php
                        echo $this->pagination->getRowOffset($i);
                        ?>
                    </td>
                    <td class="center">
                        <?php
                        echo JHtml::_('grid.id', $i, $row->id);
                        ?>
                    </td>
                    <?php
                    $inputappend = '';
                    ?>
                    <td class="center">
                        <?php if ($row->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'positions.', $canCheckin); ?>
                        <?php endif; ?>
                        <?php if ($canEdit) : ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_sportsmanagement&task=position.edit&id=' . (int) $row->id); ?>">
                                <?php echo $this->escape($row->name); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($row->name); ?>
                        <?php endif; ?>
                        <div class="small">
                            <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?>
                        </div>
                    </td>
                    <td>
                        <?php
                        if ($row->name == JText::_($row->name)) {
                            echo '&nbsp;';
                        } else {
                            echo JText::_($row->name);
                        }
                        ?>
                    </td>
                    <td width="5%" class="center">
                        <?php
                        if ($row->picture == '') {
                            $imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_NO_IMAGE');
                            echo JHtml::_('image', JURI::base() . '/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, 'title= "' . $imageTitle . '"');
                        } elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("icon")) {
                            $imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_DEFAULT_IMAGE');
                            echo JHtml::_('image', JURI::base() . '/components/com_sportsmanagement/assets/images/information.png', $imageTitle, 'title= "' . $imageTitle . '"');
                            ?>
                            <a href="<?php echo JURI::root() . $row->picture; ?>" title="<?php echo $imageTitle; ?>" class="modal">
                                <img src="<?php echo JURI::root() . $row->picture; ?>" alt="<?php echo $imageTitle; ?>" width="20" />
                            </a>
                            <?PHP
                        } elseif ($row->picture !== '') {
                            $imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CUSTOM_IMAGE');
                            echo JHtml::_('image', JURI::base() . '/components/com_sportsmanagement/assets/images/ok.png', $imageTitle, 'title= "' . $imageTitle . '"');
                            ?>
                            <a href="<?php echo JURI::root() . $row->picture; ?>" title="<?php echo $imageTitle; ?>" class="modal">
                                <img src="<?php echo JURI::root() . $row->picture; ?>" alt="<?php echo $imageTitle; ?>" width="20" />
                            </a>
                            <?PHP
                        }
                        ?>
                    </td>

                    <td>
                        <?php
                        echo JHtml::_('select.genericlist', $this->lists['parent_id'], 'parent_id' . $row->id, '' . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' . $i . '\').checked=true"', 'value', 'text', $row->parent_id);
                        ?>
                    </td>
                    <td class="center"><?php echo JText::_(sportsmanagementHelper::getSportsTypeName($row->sports_type_id)); ?></td>
                    <td class="center"><?php echo JText::_(sportsmanagementHelper::getPosPersonTypeName($row->persontype)); ?></td>
                    <td class="center">
                        <?php
                        if ($row->countEvents == 0) {
                            $imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NO_EVENTS');
                            echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', $imageTitle, 'title= "' . $imageTitle . '"');
                        } else {
                            $imageTitle = JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NR_EVENTS', $row->countEvents);
                            echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', $imageTitle, 'title= "' . $imageTitle . '"');
                        }
                        ?>
                    </td>
                    <td class="center">
                        <?php
                        if ($row->countStats == 0) {
                            $imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NO_STATISTICS');
                            echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', $imageTitle, 'title= "' . $imageTitle . '"');
                        } else {
                            $imageTitle = JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NR_STATISTICS', $row->countStats);
                            echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', $imageTitle, 'title= "' . $imageTitle . '"');
                        }
                        ?>
                    </td>
                    <td class="center">
                        <div class="btn-group">
                            <?php echo JHtml::_('jgrid.published', $row->published, $i, 'positions.', $canChange, 'cb'); ?>
                            <?php
                            // Create dropdown items and render the dropdown list.
                            if ($canChange) {
                                JHtml::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'positions');
                                JHtml::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'positions');
                                echo JHtml::_('actionsdropdown.render', $this->escape($row->name));
                            }
                            ?>
                        </div>
                    </td>
                    <td class="order">
                        <span>
                            <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'positions.orderup', 'JLIB_HTML_MOVE_UP', true); ?>
                        </span>
                        <span>
                            <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'positions.orderdown', 'JLIB_HTML_MOVE_DOWN', true); ?>
                            <?php
                            $disabled = true ? '' : 'disabled="disabled"';
                            ?>
                        </span>
                        <input  
                            type="text" name="order[]" 
                            size="2"
                            value="<?php echo $row->ordering; ?>"
                            <?php echo $disabled ?>
                            class="form-control form-control-inline"
                            style="text-align: center" />
                    </td>
                    <td align="center"><?php echo $row->id; ?></td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
        </tbody>
    </table>
</div>
