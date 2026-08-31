<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiontemplate;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiontemplateModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator editor for prediction template settings. */
final class HtmlView extends BaseHtmlView
{
    public $item;
    public $form;
    public $predictionGame = null;
    public int $prediction_id = 0;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof PredictiontemplateModel) {
            throw new \RuntimeException('PredictiontemplateModel is unavailable.', 500);
        }

        $this->item = $this->get('Item');

        if (!$this->item || empty($this->item->template)) {
            throw new \RuntimeException('Prediction template data is unavailable.', 500);
        }

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $template = basename((string) $this->item->template);

        if ($template !== (string) $this->item->template) {
            throw new \RuntimeException('Invalid prediction template name.', 400);
        }

        $xmlFile = JPATH_COMPONENT_SITE . '/settings/default/' . $template . '.xml';
        $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
        $form = $formFactory->createForm('predictiontemplate.' . $template, ['control' => 'params']);

        if (!$form->loadFile($xmlFile)) {
            throw new \RuntimeException('Prediction template form could not be loaded.', 500);
        }

        $params = $this->item->params ?? [];

        if (is_string($params)) {
            $decoded = json_decode($params, true);
            $params = is_array($decoded) ? $decoded : [];
        } elseif (is_object($params)) {
            $params = get_object_vars($params);
        }

        $form->bind(is_array($params) ? $params : []);
        $this->form = $form;
        $this->prediction_id = (int) ($this->item->prediction_id ?? Factory::getApplication()->getInput()->getInt('predid'));
        $this->predictionGame = $model->getPredictionGame($this->prediction_id);

        Factory::getApplication()->setUserState('com_sportsmanagement.prediction_id', $this->prediction_id);
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $layout = strtolower((string) $this->getLayout());

        if ($layout === 'edit_3' || $layout === 'edit_4') {
            $this->setLayout('edit');
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(
            Text::_((int) ($this->item->id ?? 0) > 0
                ? 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_EDIT'
                : 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_NEW'),
            'options'
        );
        ToolbarHelper::apply('predictiontemplate.apply');
        ToolbarHelper::save('predictiontemplate.save');
        ToolbarHelper::cancel('predictiontemplate.cancel');
    }
}
