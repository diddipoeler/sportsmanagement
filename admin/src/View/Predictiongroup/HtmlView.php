<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongroup;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for a prediction group. */
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
            throw new \RuntimeException('Prediction group form could not be loaded.', 500);
        }

        $this->getDocument()->getWebAssetManager()->useScript('form.validate');
        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        $isNew = empty($this->item->id);
        ToolbarHelper::title(
            Text::_($isNew
                ? 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTION_GROUP_NEW'
                : 'COM_SPORTSMANAGEMENT_PREDICTION_GROUP_EDIT'),
            'users'
        );
        ToolbarHelper::apply('predictiongroup.apply');
        ToolbarHelper::save('predictiongroup.save');
        ToolbarHelper::save2new('predictiongroup.save2new');
        ToolbarHelper::cancel('predictiongroup.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
