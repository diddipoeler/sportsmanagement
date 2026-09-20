<?php
/**
 * Native Joomla 5/6 administrator SportsManagement edit view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Sportsmanagement;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for the SportsManagement sample record. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;

    public function display($tpl = null)
    {
        $app = self::administratorApplication();
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
        $app = self::administratorApplication();
        $identity = $app->getIdentity();
        $itemId = (int) ($this->item->id ?? 0);
        $isNew = $itemId === 0;
        $asset = $isNew ? 'com_sportsmanagement' : 'com_sportsmanagement.message.' . $itemId;
        $canCreate = $identity->authorise('core.create', 'com_sportsmanagement');
        $canEdit = $identity->authorise('core.edit', $asset)
            || $identity->authorise('core.edit', 'com_sportsmanagement');

        $this->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.admin.user-icons',
            'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
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
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement administrator edit view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
