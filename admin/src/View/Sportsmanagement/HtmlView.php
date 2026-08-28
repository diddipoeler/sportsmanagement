<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Sportsmanagement;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator edit view for the SportsManagement sample record. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $app->getInput()->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('SportsManagement form could not be loaded.', 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $app = Factory::getApplication();
        $identity = $app->getIdentity();
        $itemId = (int) ($this->item->id ?? 0);
        $isNew = $itemId === 0;
        $asset = $isNew ? 'com_sportsmanagement' : 'com_sportsmanagement.message.' . $itemId;
        $canCreate = $identity->authorise('core.create', 'com_sportsmanagement');
        $canEdit = $identity->authorise('core.edit', $asset)
            || $identity->authorise('core.edit', 'com_sportsmanagement');

        $this->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.admin.user-icons',
            Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
            ['version' => 'auto']
        );

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT__NEW' : 'COM_SPORTSMANAGEMENT__EDIT'),
            'sportsmanagement'
        );

        if (($isNew && $canCreate) || (!$isNew && $canEdit)) {
            ToolbarHelper::apply('sportsmanagement.apply');
            ToolbarHelper::save('sportsmanagement.save');
        }

        if ($canCreate) {
            ToolbarHelper::save2new('sportsmanagement.save2new');

            if (!$isNew) {
                ToolbarHelper::save2copy('sportsmanagement.save2copy');
            }
        }

        ToolbarHelper::cancel(
            'sportsmanagement.cancel',
            $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
        );
    }
}
