<?php
/**
 * Joomla 5/6 administrator list view for alternative club names.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    SportsManagement
 * @subpackage com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Clubnames;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ClubnamesModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for alternative club names. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof ClubnamesModel) {
            throw new \RuntimeException('Clubnames view requires ClubnamesModel.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters() ?: [];

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_TITLE'), 'address');
        ToolbarHelper::addNew('clubname.add');
        ToolbarHelper::editList('clubname.edit');
        ToolbarHelper::publish('clubnames.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('clubnames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('clubnames.checkin');
        ToolbarHelper::trash('clubnames.trash');
        ToolbarHelper::custom('clubnames.import', 'upload', 'upload', Text::_('JTOOLBAR_INSTALL'), false);
    }
}
