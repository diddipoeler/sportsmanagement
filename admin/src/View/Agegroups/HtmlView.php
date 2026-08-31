<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Agegroups;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\AgegroupsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Native Joomla 5/6 age-groups list view.
 */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof AgegroupsModel) {
            throw new \RuntimeException('Agegroups model could not be loaded.', 500);
        }

        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->items) {
            $this->seedAgegroups();
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function seedAgegroups(): void
    {
        $app = Factory::getApplication();
        $mvcFactory = $app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $databaseTool = $mvcFactory->createModel('Databasetool', 'Administrator');

        if ($databaseTool instanceof DatabasetoolModel) {
            $databaseTool->insertAgegroup(
                (string) $this->state->get('filter.search_nation'),
                (int) $this->state->get('filter.sports_type')
            );
        }

        $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_NO_RESULT'), 'warning');
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_TITLE'), 'users');
        ToolbarHelper::addNew('agegroup.add');
        ToolbarHelper::editList('agegroup.edit');
        ToolbarHelper::apply('agegroups.saveshort');
        ToolbarHelper::custom('agegroups.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('agegroup.export', Text::_('JTOOLBAR_EXPORT'));
    }
}
