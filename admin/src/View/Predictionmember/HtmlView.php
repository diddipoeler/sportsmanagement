<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictionmember;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for a prediction-game member. */
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

        if (!$this->form || !$this->item) {
            throw new \RuntimeException('Prediction member form could not be loaded.', 500);
        }

        $this->item->name = '';
        $this->getDocument()->getWebAssetManager()->useScript('form.validate');
        $this->addToolbar();

        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        $isNew = empty($this->item->id);

        ToolbarHelper::title(
            Text::_($isNew
                ? 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_ADD_NEW'
                : 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_EDIT'),
            'users'
        );
        ToolbarHelper::apply('predictionmember.apply');
        ToolbarHelper::save('predictionmember.save');
        ToolbarHelper::cancel('predictionmember.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
