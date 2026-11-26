<?php
/**
 * Native Joomla 5/6 administrator list view for seasons.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Seasons;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for seasons. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];

    /**
     * Populate and render the season administration list.
     *
     * Require the native SeasonsModel, collect rows, pagination, filter form
     * and state, redact reported model errors and prepare the list toolbar.
     * The inherited view then outputs the current Joomla administrator layout.
     *
     * @param string|null $tpl Optional template suffix.
     * @return void The list is rendered by the parent Joomla view.
     * @throws \RuntimeException If the model is missing or reports errors.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof SeasonsModel) {
            throw new \RuntimeException('Seasons model could not be loaded.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters() ?: [];

        if ($errors = $model->getErrors()) {
            Log::add(__METHOD__ . ': model reported ' . count((array) $errors) . ' errors.', Log::ERROR, 'com_sportsmanagement');
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Register season management actions in the administrator toolbar.
     *
     * Display the translated list title and expose add, edit, publish,
     * unpublish, check-in and trash tasks for the seasons controller.
     * Individual task permissions remain enforced by Joomla controllers.
     *
     * @return void Toolbar buttons are registered as a side effect.
     */
    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_TITLE'), 'list');
        ToolbarHelper::addNew('season.add');
        ToolbarHelper::editList('season.edit');
        ToolbarHelper::publish('seasons.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('seasons.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('seasons.checkin');
        ToolbarHelper::trash('seasons.trash');
    }
}
