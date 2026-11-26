<?php
/**
 * Native Joomla 5/6 administrator list view for positions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Positions;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositionsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

final class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public $activeFilters = [];
    public $parents = [];

    /**
     * Prepare the administrator list of sports positions and assignments.
     *
     * Require the native PositionsModel and retrieve its records, state,
     * filters and parent-position options directly. Redact internal errors. Set up
     * the native Joomla toolbar for save-short, publish, import, export and
     * other position-management tasks before rendering the list.
     *
     * @param string|null $tpl Optional Joomla list template suffix.
     * @return void The inherited view renders the positions list.
     * @throws \RuntimeException When the model reports loading errors.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof PositionsModel) {
            throw new \RuntimeException('PositionsModel is unavailable.', 500);
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

        $this->parents = $model->getParentsPositions();

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_TITLE'), 'list');
        ToolbarHelper::apply('positions.saveshort');
        ToolbarHelper::addNew('position.add');
        ToolbarHelper::editList('position.edit');
        ToolbarHelper::publish('positions.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('positions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('positions.checkin');
        ToolbarHelper::trash('positions.trash');
        ToolbarHelper::custom('position.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('position.export', Text::_('JTOOLBAR_EXPORT'));

        parent::display($tpl);
    }
}
