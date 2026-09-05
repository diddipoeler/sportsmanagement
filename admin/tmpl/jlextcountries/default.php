<?php
/**
 * Joomla 5/6 administrator list layout for countries.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.multiselect');

$search = (string) $this->state->get('filter.search');
$state = $this->state->get('filter.state');
$federation = (int) $this->state->get('filter.federation', 0);
$mapFilter = (string) $this->state->get('filter.search_countrymap');
$order = (string) $this->state->get('list.ordering', 'objcountry.name');
$direction = (string) $this->state->get('list.direction', 'ASC');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jlextcountries'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="container-fluid">
        <div class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label" for="filter_search"><?php echo Text::_('JSEARCH_FILTER'); ?></label>
                <input class="form-control" type="search" name="filter[search]" id="filter_search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filter_federation"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FEDERATION'); ?></label>
                <input class="form-control" type="number" min="0" name="filter_federation" id="filter_federation" value="<?php echo $federation > 0 ? $federation : ''; ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filter_search_countrymap"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_COUNTRYMAP_MAPDATA'); ?></label>
                <select class="form-select" name="filter_search_countrymap" id="filter_search_countrymap">
                    <option value=""<?php echo $mapFilter === '' ? ' selected' : ''; ?>><?php echo Text::_('JALL'); ?></option>
                    <option value="IS NOT NULL"<?php echo $mapFilter === 'IS NOT NULL' ? ' selected' : ''; ?>><?php echo Text::_('JYES'); ?></option>
                    <option value="IS NULL"<?php echo $mapFilter === 'IS NULL' ? ' selected' : ''; ?>><?php echo Text::_('JNO'); ?></option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="filter_state"><?php echo Text::_('JSTATUS'); ?></label>
                <select class="form-select" name="filter[state]" id="filter_state">
                    <option value=""<?php echo $state === '' ? ' selected' : ''; ?>><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                    <option value="1"<?php echo (string) $state === '1' ? ' selected' : ''; ?>><?php echo Text::_('JPUBLISHED'); ?></option>
                    <option value="0"<?php echo (string) $state === '0' ? ' selected' : ''; ?>><?php echo Text::_('JUNPUBLISHED'); ?></option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jlextcountries&filter[search]=&filter[state]='); ?>"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
            </div>
        </div></div></div>

        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead><tr>
                    <th class="text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_FLAG'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FEDERATION'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ALPHA2'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_ALPHA3'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_EDIT_FIFA'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_COUNTRYMAP_MAPDATA'); ?></th>
                    <th><?php echo Text::_('JSTATUS'); ?></th>
                    <th><?php echo Text::_('JGLOBAL_FIELD_ID_LABEL'); ?></th>
                </tr></thead>
                <tbody>
                <?php if (!$this->items) : ?>
                    <tr><td colspan="10" class="text-center py-4"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
                <?php else : ?>
                    <?php foreach ($this->items as $i => $item) : ?>
                        <tr>
                            <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id); ?></td>
                            <td><a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=jlextcountry.edit&id=' . (int) $item->id); ?>"><?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?></a></td>
                            <td><?php if (!empty($item->picture)) : ?><img src="<?php echo htmlspecialchars(Uri::root() . ltrim((string) $item->picture, '/'), ENT_QUOTES, 'UTF-8'); ?>" alt="" style="max-height:28px;max-width:48px"><?php endif; ?></td>
                            <td><?php echo htmlspecialchars((string) ($item->federation_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) $item->alpha2, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) $item->alpha3, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) $item->fifa, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="badge <?php echo !empty($item->countrymap_mapdata) ? 'bg-success' : 'bg-secondary'; ?>"><?php echo Text::_(!empty($item->countrymap_mapdata) ? 'JYES' : 'JNO'); ?></span></td>
                            <td><span class="badge <?php echo (int) $item->published === 1 ? 'bg-success' : 'bg-secondary'; ?>"><?php echo Text::_((int) $item->published === 1 ? 'JPUBLISHED' : 'JUNPUBLISHED'); ?></span></td>
                            <td><?php echo (int) $item->id; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div></div></div>

        <?php if ($this->pagination) : ?><div class="mt-3"><?php echo $this->pagination->getListFooter(); ?></div><?php endif; ?>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo htmlspecialchars($order, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars($direction, ENT_QUOTES, 'UTF-8'); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
