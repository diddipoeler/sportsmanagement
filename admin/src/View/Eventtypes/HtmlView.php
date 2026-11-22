<?php
/**
 * Native Joomla 5/6 event-types list view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Eventtypes;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\EventtypesModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 event-types list view. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];

    /**
     * Prepare the event-type administration list and available actions.
     *
     * Verify the native event-types model, load events, pagination and
     * filters, and fail when the model reports errors. Configure Joomla
     * toolbar commands for editing, publishing, importing and exporting
     * before rendering the list template.
     *
     * @param string|null $tpl Optional Joomla list layout suffix.
     * @return void The inherited view renders the event-type list.
     * @throws \RuntimeException If the model is missing or reports errors.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof EventtypesModel) {
            throw new \RuntimeException('Eventtypes model could not be loaded.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters() ?: [];

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_TITLE'), 'list');
        ToolbarHelper::addNew('eventtype.add');
        ToolbarHelper::editList('eventtype.edit');
        ToolbarHelper::publish('eventtypes.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('eventtypes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('eventtypes.checkin');
        ToolbarHelper::trash('eventtypes.trash');
        ToolbarHelper::custom('eventtype.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('eventtype.export', Text::_('JTOOLBAR_EXPORT'));

        parent::display($tpl);
    }
}
