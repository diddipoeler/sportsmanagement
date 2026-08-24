<?php
/** SportsManagement administrator countries list data template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$this->saveOrder = $this->sortColumn === 'objcountry.ordering';
$imageTitle = '';

if ($this->saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_sportsmanagement&task=' . $this->view
        . '.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<div class="table-responsive" id="editcell">
    <table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_NAME', 'objcountry.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_FLAG'); ?></th>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FLAG_MAPS'); ?></th>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TRANSLATION'); ?></th>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FEDERATION'); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ALPHA2', 'objcountry.alpha2', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ALPHA3', 'objcountry.alpha3', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ITU', 'objcountry.itu', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_FIPS', 'objcountry.fips', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_IOC', 'objcountry.ioc', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_FIFA', 'objcountry.fifa', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_DS', 'objcountry.ds', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_WMO', 'objcountry.wmo', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo Text::_('MAPDATA'); ?></th>
            <th class="nowrap center"><?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'objcountry.published', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'objcountry.ordering', $this->sortDirection, $this->sortColumn); ?><?php echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'jlextcountries.saveorder'); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'objcountry.id', $this->sortDirection, $this->sortColumn); ?></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="19" class="center">
                <?php echo $this->pagination->getListFooter(); ?>
                <?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody<?php if ($this->saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>"<?php endif; ?>>
        <?php foreach ($this->items as $this->count_i => $this->item) :
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
            $canCheckin = $this->user->authorise('core.manage', 'com_checkin')
                || $this->item->checked_out == $this->user->get('id')
                || $this->item->checked_out == 0;
            $canChange = $this->user->authorise(
                'core.edit.state',
                'com_sportsmanagement.jlextcountry.' . $this->item->id
            ) && $canCheckin;
            ?>
            <tr class="row<?php echo $this->count_i % 2; ?>" data-dragable-group="none">
                <td class="center"><?php echo $this->pagination->getRowOffset($this->count_i); ?></td>
                <td class="center"><?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?></td>
                <td class="center">
                    <?php if ($this->item->checked_out) : ?>
                        <?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'jlextcountries.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=jlextcountry.edit&id=' . (int) $this->item->id); ?>"><?php echo $this->escape($this->item->name); ?></a>
                    <?php else : ?>
                        <?php echo $this->escape($this->item->name); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo JSMCountries::getCountryFlag($this->item->alpha3); ?></td>
                <td class="center">
                    <?php
                    $flagMapPath = JPATH_SITE . DIRECTORY_SEPARATOR . ltrim((string) $this->item->flag_maps, '/\\');
                    if (empty($this->item->flag_maps) || !is_file($flagMapPath)) {
                        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . $this->item->flag_maps;
                        echo HTMLHelper::_(
                            'image',
                            'administrator/components/com_sportsmanagement/assets/images/delete.png',
                            $imageTitle,
                            'title="' . $this->escape($imageTitle) . '"'
                        );
                    } else {
                        echo sportsmanagementHelper::getBootstrapModalImage(
                            'collapseModallogo_picture' . $this->item->id,
                            Uri::root() . $this->item->flag_maps,
                            $this->item->name,
                            '20',
                            Uri::root() . $this->item->flag_maps
                        );
                    }
                    ?>
                </td>
                <td><?php echo Text::_($this->item->name); ?></td>
                <td><?php echo $this->escape((string) $this->item->federation_name); ?></td>
                <td><?php echo $this->escape((string) $this->item->alpha2); ?></td>
                <td><?php echo $this->escape((string) $this->item->alpha3); ?></td>
                <td><?php echo $this->escape((string) $this->item->itu); ?></td>
                <td><?php echo $this->escape((string) $this->item->fips); ?></td>
                <td><?php echo $this->escape((string) $this->item->ioc); ?></td>
                <td><?php echo $this->escape((string) $this->item->fifa); ?></td>
                <td><?php echo $this->escape((string) $this->item->ds); ?></td>
                <td><?php echo $this->escape((string) $this->item->wmo); ?></td>
                <td>
                    <?php echo HTMLHelper::_(
                        'image',
                        $this->item->countrymap_mapdata
                            ? 'administrator/components/com_sportsmanagement/assets/images/ok.png'
                            : 'administrator/components/com_sportsmanagement/assets/images/error.png',
                        $imageTitle,
                        'title="' . $this->escape($imageTitle) . '"'
                    ); ?>
                </td>
                <td class="center">
                    <div class="btn-group">
                        <?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'jlextcountries.', $canChange, 'cb'); ?>
                        <?php if ($canChange) :
                            HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'jlextcountries');
                            HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'jlextcountries');
                            echo HTMLHelper::_('actionsdropdown.render', $this->escape($this->item->name));
                        endif; ?>
                    </div>
                </td>
                <td class="order" id="defaultdataorder"><?php echo $this->loadTemplate('data_order'); ?></td>
                <td class="center"><?php echo $this->item->id; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
