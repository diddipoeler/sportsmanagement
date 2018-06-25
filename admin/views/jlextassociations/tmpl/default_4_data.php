<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version         1.0.05
 * @file                agegroup.php
 * @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license                This file is part of SportsManagement.
 *
 * SportsManagement is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SportsManagement is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil von SportsManagement.
 *
 * SportsManagement ist Freie Software: Sie können es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
 * veröffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
 * OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License für weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */
defined('_JEXEC') or die('Restricted access');


//Ordering allowed ?
$ordering = ($this->sortColumn == 'objassoc.ordering');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
            <tr>
                <th width="1%" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
                <th width="1%" style="vertical-align: top; ">
                    <input  type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
                </th>

                <th class="title" nowrap="nowrap" style="vertical-align: top; ">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_NAME', 'objassoc.name', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th class="title" nowrap="nowrap" style="vertical-align: top; ">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_SHORT_NAME', 'objassoc.short_name', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>					
                <th width="5%" class="title" style="vertical-align: top; ">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_COUNTRY', 'objassoc.country', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>

                <th width="1%" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_FLAG'); ?></th>
                <th width="1%" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_ICON'); ?></th>

                <th width="5%" class="title">
                    <?php
                    echo JHtml::_('grid.sort', 'JSTATUS', 'objassoc.published', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>

                <th width="5%" nowrap="nowrap" style="vertical-align: top; ">
                    <?php
                    echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'objassoc.ordering', $this->sortDirection, $this->sortColumn);
                    echo '<br />';
                    echo JHtml::_('grid.order', $this->items, 'filesave.png', 'jlextassociations.saveorder');
                    ?>
                </th>
                <th width="1%" style="vertical-align: top; ">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'objassoc.id', $this->sortDirection, $this->sortColumn); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="7">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
                <td colspan='4'>
                    <?php echo $this->pagination->getResultsCounter(); ?>
                </td>
            </tr></tfoot>
        <tbody>
            <?php
            $k = 0;
            for ($i = 0, $n = count($this->items); $i < $n; $i++) {
                $row = & $this->items[$i];
                $link = JRoute::_('index.php?option=com_sportsmanagement&task=jlextassociation.edit&id=' . $row->id);
                $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
                $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
                $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'jlextassociations.', $canCheckin);
                $canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement.jlextassociation.' . $row->id) && $canCheckin;
                ?>
                <tr class="<?php echo "row$k"; ?>">
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
                    <td style="text-align:center; ">
                        <?php if ($row->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'jlextassociations.', $canCheckin); ?>
                        <?php endif; ?>
                        <?php if ($canEdit) : ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_sportsmanagement&task=jlextassociation.edit&id=' . (int) $row->id); ?>">
                                <?php echo $this->escape($row->name); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($row->name); ?>
                        <?php endif; ?>
                        <?php //echo $checked;  ?>
                        <?php //echo $row->name;  ?>
                        <div class="small">
                            <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?></div>
                    </td>
                    <?php ?>

                    <td><?php echo $row->short_name; ?></td>
                    <td style="text-align:center; "><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
                    <td style="text-align:center; ">
                        <?php
                    //$path = JURI::root().$row->assocflag;
                    //$attributes='';
                    //$html .= 'title="'.$row->name.'" '.$attributes.' />';
                    //echo $html;
                        if (empty($row->assocflag) || !JFile::exists(JPATH_SITE . DS . $row->assocflag)) {
                            echo '<i class="fa fa-flag text-danger"></i>';
                        } else {
                            ?>                                    
                            <a href="<?php echo JURI::root() . $row->assocflag; ?>" title="<?php echo $row->name; ?>" class="modal">
                                <img src="<?php echo JURI::root() . $row->assocflag; ?>" alt="<?php echo $row->name; ?>" width="20" />
                            </a>
                            <?PHP
                        }
                        ?>
                    </td>
                    <td style="text-align:center; ">
                        <?php
                    //$path = JURI::root().$row->assocflag;
                    //$attributes='';
                    //$html .= 'title="'.$row->name.'" '.$attributes.' />';
                    //echo $html;
                        if (empty($row->picture) || !JFile::exists(JPATH_SITE . DS . $row->picture)) {
                            echo '<i class="fa fa-picture-o text-danger"></i>';
                        } else {
                            ?>                                    
                            <a href="<?php echo JURI::root() . $row->picture; ?>" title="<?php echo $row->name; ?>" class="modal">
                                <img src="<?php echo JURI::root() . $row->picture; ?>" alt="<?php echo $row->name; ?>" width="20" />
                            </a>
                            <?PHP
                        }
                        ?>
                    </td>
                    <td class="center">
                        <div class="btn-group">
                            <?php echo JHtml::_('jgrid.published', $row->published, $i, 'jlextassociations.', $canChange, 'cb'); ?>
                            <?php
                            // Create dropdown items and render the dropdown list.
                            if ($canChange) {
                                JHtml::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'jlextassociations');
                                JHtml::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'jlextassociations');
                                echo JHtml::_('actionsdropdown.render', $this->escape($row->name));
                            }
                            ?>
                        </div>

                    </td>
                    <td class="order">
                        <span>
                            <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'jlextassociations.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?>
                        </span>
                        <span>
                            <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'jlextassociations.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?>
                            <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                        </span>
                        <input	type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
                               class="form-control form-control-inline" style="text-align: center" />
                    </td>
                    <td style="text-align:center; "><?php echo $row->id; ?></td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
        </tbody>
    </table>
</div>

