<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Sportsmanagements;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator list view for SportsManagement sample records. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public bool $canEdit = false;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $identity = Factory::getApplication()->getIdentity();
        $canCreate = $identity->authorise('core.create', 'com_sportsmanagement');
        $this->canEdit = $identity->authorise('core.edit', 'com_sportsmanagement');
        $canDelete = $identity->authorise('core.delete', 'com_sportsmanagement');
        $canAdmin = $identity->authorise('core.admin', 'com_sportsmanagement');

        $this->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.admin.user-icons',
            Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
            ['version' => 'auto']
        );

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_MANAGER'), 'sportsmanagement');

        if ($canCreate) {
            ToolbarHelper::addNew('sportsmanagement.add');
        }

        if ($this->canEdit) {
            ToolbarHelper::editList('sportsmanagement.edit');
        }

        if ($canDelete) {
            ToolbarHelper::deleteList('', 'sportsmanagements.delete');
        }

        if ($canAdmin) {
            ToolbarHelper::preferences('com_sportsmanagement');
        }
    }
}
