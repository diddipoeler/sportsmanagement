<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage leagues
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
?>
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
                echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_NAME', 'obj.name', $this->sortDirection, $this->sortColumn);
                ?>
            </th>
            <th>
                <?php
                echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_SHORT_NAME', 'obj.short_name', $this->sortDirection, $this->sortColumn);
                ?>
            </th>
            <th width="10%">
                <?php
                echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_COUNTRY', 'obj.country', $this->sortDirection, $this->sortColumn);
                ?>
            </th>
            <th class="title">
                <?php
                echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE', 'st.name', $this->sortDirection, $this->sortColumn);
                ?>
            </th>
            <th class="title">
                <?php
                echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP', 'ag.name', $this->sortDirection, $this->sortColumn);
                ?>
            </th>
            <th class="title">
                <?php
                echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_NAME', 'fed.name', $this->sortDirection, $this->sortColumn);
                ?>
            </th>
            <th>
                <?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
            </th>
            <th width="" class="nowrap center">
                <?php
                echo JHtml::_('grid.sort', 'JSTATUS', 'objassoc.published', $this->sortDirection, $this->sortColumn);
                ?>
            </th>
            <th width="10%">
                <?php
                echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn);
                echo JHtml::_('grid.order', $this->items, 'filesave.png', 'leagues.saveorder');
                ?>
            </th>
            <th width="20">
                <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="9">
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
            $link = JRoute::_('index.php?option=com_sportsmanagement&task=league.edit&id=' . $row->id);
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
            $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
            $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'leagues.', $canCheckin);
            $canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement.league.' . $row->id) && $canCheckin;
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
                <td class="center">
                    <?php if ($row->checked_out) : ?>
                        <?php echo JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'leagues.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit) : ?>
                        <a href="<?php echo JRoute::_('index.php?option=com_sportsmanagement&task=league.edit&id=' . (int) $row->id); ?>">
                            <?php echo $this->escape($row->name); ?></a>
                    <?php else : ?>
                        <?php echo $this->escape($row->name); ?>
                    <?php endif; ?>
                    <div class="small">
                        <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?>
                    </div>
                </td>
                <td><?php echo $row->short_name; ?></td>
                <td class="center">
                    <?php
                    echo JSMCountries::getCountryFlag($row->country);
                    $append = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
                    echo JHtml::_('select.genericlist', $this->lists['nation'], 'country' . $row->id, 'class="form-control form-control-inline" style="width:150px" size="1"' . $append, 'value', 'text', $row->country);
                    ?>
                </td>
                <td class="center"><?php echo JText::_($row->sportstype); ?></td>
                <td class="center">
                    <?php
                    //echo JText::_($row->agegroup); 
                    $inputappend = '';
                    $append = ' style="background-color:#bbffff"';
                    echo JHtml::_('select.genericlist', $this->lists['agegroup'], 'agegroup' . $row->id, $inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
                            $i . '\').checked=true"' . $append, 'value', 'text', $row->agegroup_id);
                    ?>
                </td>
                <td class="center">
                    <?php
                    //echo JText::_($row->fedname); 

                    $imageTitle = '';
                    $append = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
                    if (isset($this->lists['association'][$row->country])) {
                        echo JHtml::_('select.genericlist', $this->lists['association'][$row->country], 'association' . $row->id, 'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $row->associations);
                    } else {
                        echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, 'title= "' . $imageTitle . '"');
                    }
                    ?>
                </td>
                <td class="center">
                    <?php
                    if (empty($row->picture)) {
                        $imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . COM_SPORTSMANAGEMENT_PICTURE_SERVER . $row->picture;
                        echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, 'title= "' . $imageTitle . '"');
                    } elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player")) {
                        $imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
                        echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/images/information.png', $imageTitle, 'title= "' . $imageTitle . '"');
                    } else {
                        ?>                                    
                        <a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER . $row->picture; ?>" title="<?php echo $row->name; ?>" class="modal">
                            <img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER . $row->picture; ?>" alt="<?php echo $row->name; ?>" width="20" />
                        </a>
                        <?PHP
                    }
                    ?>
                </td>
                <td class="center">
                    <div class="btn-group">
                        <?php echo JHtml::_('jgrid.published', $row->published, $i, 'leagues.', $canChange, 'cb'); ?>
                        <?php
                        // Create dropdown items and render the dropdown list.
                        if ($canChange) {
                            JHtml::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'leagues');
                            JHtml::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'leagues');
                            echo JHtml::_('actionsdropdown.render', $this->escape($row->name));
                        }
                        ?>
                    </div>
                </td>    
                <td class="order">
                    <span>
                        <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'leagues.orderup', 'JLIB_HTML_MOVE_UP', 'obj.ordering'); ?>
                    </span>
                    <span>
                        <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'leagues.orderdown', 'JLIB_HTML_MOVE_DOWN', 'obj.ordering'); ?>
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

