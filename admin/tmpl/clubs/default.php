<?php
/** @package SportsManagement */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');

$search = (string) $this->state->get('filter.search');
$state = $this->state->get('filter.state');
$geoData = $this->state->get('filter.geo_daten');
$standardPicture = (int) $this->state->get('filter.standard_picture');
$order = (string) $this->state->get('list.ordering', 'a.name');
$direction = (string) $this->state->get('list.direction', 'ASC');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=clubs'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="container-fluid">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label" for="filter_search"><?php echo Text::_('JSEARCH_FILTER'); ?></label>
                        <input class="form-control" type="search" name="filter_search" id="filter_search"
                               value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>">
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="filter_state"><?php echo Text::_('JSTATUS'); ?></label>
                        <select class="form-select" name="filter_state" id="filter_state">
                            <option value=""<?php echo $state === '' ? ' selected' : ''; ?>><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                            <option value="1"<?php echo (string) $state === '1' ? ' selected' : ''; ?>><?php echo Text::_('JPUBLISHED'); ?></option>
                            <option value="0"<?php echo (string) $state === '0' ? ' selected' : ''; ?>><?php echo Text::_('JUNPUBLISHED'); ?></option>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="filter_search_nation"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_COUNTRY'); ?></label>
                        <?php echo $this->lists['nation2']; ?>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="filter_season"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'); ?></label>
                        <?php echo $this->lists['seasons']; ?>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="filter_search_association"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'); ?></label>
                        <?php echo $this->lists['association']; ?>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="filter_geo_daten"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_GEO_DATEN'); ?></label>
                        <select class="form-select" name="filter_geo_daten" id="filter_geo_daten">
                            <option value=""<?php echo $geoData === '' ? ' selected' : ''; ?>><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_GEO_DATEN'); ?></option>
                            <option value="1"<?php echo (string) $geoData === '1' ? ' selected' : ''; ?>><?php echo Text::_('JYES'); ?></option>
                            <option value="0"<?php echo (string) $geoData === '0' ? ' selected' : ''; ?>><?php echo Text::_('JNO'); ?></option>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="filter_standard_picture"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_STANDARD_PICTURE'); ?></label>
                        <select class="form-select" name="filter_standard_picture" id="filter_standard_picture">
                            <option value=""<?php echo $standardPicture === 0 ? ' selected' : ''; ?>><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_STANDARD_PICTURE'); ?></option>
                            <option value="1"<?php echo $standardPicture === 1 ? ' selected' : ''; ?>><?php echo Text::_('JYES'); ?></option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                        <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=clubs'); ?>"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                                <th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
                                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_COUNTRY'); ?></th>
                                <th><?php echo Text::_('JGLOBAL_FIELD_LOCATION_LABEL'); ?></th>
                                <th><?php echo Text::_('JSTATUS'); ?></th>
                                <th><?php echo Text::_('JGLOBAL_FIELD_ID_LABEL'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!$this->items) : ?>
                            <tr><td colspan="6" class="text-center py-4"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($this->items as $i => $item) : ?>
                                <tr>
                                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id); ?></td>
                                    <td>
                                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=club.edit&id=' . (int) $item->id); ?>">
                                            <?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                        <?php if (!empty($item->unique_id)) : ?>
                                            <div class="small text-muted"><?php echo htmlspecialchars((string) $item->unique_id, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars((string) $item->country, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(trim((string) $item->zipcode . ' ' . (string) $item->location), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php if ((int) $item->published === 1) : ?>
                                            <span class="badge bg-success"><?php echo Text::_('JPUBLISHED'); ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary"><?php echo Text::_('JUNPUBLISHED'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo (int) $item->id; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($this->pagination) : ?>
            <div class="mt-3"><?php echo $this->pagination->getListFooter(); ?></div>
        <?php endif; ?>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo htmlspecialchars($order, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars($direction, ENT_QUOTES, 'UTF-8'); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
