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
?>


<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
            <tr>
                <th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                </th>

                <th>
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_NAME', 'objcountry.name', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="5" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_FLAG'); ?></th>

                <th width="5" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_TRANSLATION'); ?></th>
                <th width="5" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_FEDERATION'); ?></th>

                <th>
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ALPHA2', 'objcountry.alpha2', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="10%">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ALPHA3', 'objcountry.alpha3', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>

                <th width="10%">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ITU', 'objcountry.itu', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="10%">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_FIPS', 'objcountry.fips', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="10%">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_IOC', 'objcountry.ioc', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="10%">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_FIFA', 'objcountry.fifa', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="10%">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_DS', 'objcountry.ds', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="10%">
                    <?php
                    echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_WMO', 'objcountry.wmo', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="" class="nowrap center">
                    <?php
                    echo JHtml::_('grid.sort', 'JSTATUS', 's.published', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="10%">
                    <?php
                    echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'objcountry.ordering', $this->sortDirection, $this->sortColumn);
                    echo JHtml::_('grid.order', $this->items, 'filesave.png', 'jlextcountries.saveorder');
                    ?>
                </th>
                <th width="20">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'objcountry.id', $this->sortDirection, $this->sortColumn); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="13"><?php echo $this->pagination->getListFooter(); ?>
                <td colspan="4"><?php echo $this->pagination->getResultsCounter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php
            $k = 0;
            for ($i = 0, $n = count($this->items); $i < $n; $i++) {
                $row = & $this->items[$i];
                $link = JRoute::_('index.php?option=com_sportsmanagement&task=jlextcountry.edit&id=' . $row->id);
                $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
                $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
                $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'jlextcountries.', $canCheckin);
                $canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement.jlextcountry.' . $row->id) && $canCheckin;
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
                    <td class="center">
                        <?php if ($row->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'jlextcountries.', $canCheckin); ?>
                        <?php endif; ?>
                        <?php if ($canEdit) : ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_sportsmanagement&task=jlextcountry.edit&id=' . (int) $row->id); ?>">
                                <?php echo $this->escape($row->name); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($row->name); ?>
                        <?php endif; ?>
                        <?php //echo $checked;  ?>
                    </td>
                    <?php $inputappend = ''; ?>
                    <td><?php echo JSMCountries::getCountryFlag($row->alpha3); ?></td>
                    <td><?php echo JText::_($row->name); ?></td>
                    <td><?php echo $row->federation_name; ?></td>
                    <td><?php echo $row->alpha2; ?></td>
                    <td><?php echo $row->alpha3; ?></td>
                    <td><?php echo $row->itu; ?></td>
                    <td><?php echo $row->fips; ?></td>
                    <td><?php echo $row->ioc; ?></td>
                    <td><?php echo $row->fifa; ?></td>
                    <td><?php echo $row->ds; ?></td>
                    <td><?php echo $row->wmo; ?></td>
                    <td class="center">
                        <div class="btn-group">
                            <?php echo JHtml::_('jgrid.published', $row->published, $i, 'jlextcountries.', $canChange, 'cb'); ?>
                            <?php
                            // Create dropdown items and render the dropdown list.
                            if ($canChange) {
                                JHtml::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'jlextcountries');
                                JHtml::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'jlextcountries');
                                echo JHtml::_('actionsdropdown.render', $this->escape($row->name));
                            }
                            ?>
                        </div>
                    </td>    
                    <td class="order">
                        <span>
                            <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'jlextcountries.orderup', 'JLIB_HTML_MOVE_UP', 'objcountry.ordering'); ?>
                        </span>
                        <span>
                            <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'jlextcountries.orderdown', 'JLIB_HTML_MOVE_DOWN', 'objcountry.ordering'); ?>
                            <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                        </span>
                        <input	type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
                               class="form-control form-control-inline" style="text-align: center" />
                    </td>
                    <td class="center"><?php echo $row->id; ?></td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
        </tbody>
    </table>
</div>
