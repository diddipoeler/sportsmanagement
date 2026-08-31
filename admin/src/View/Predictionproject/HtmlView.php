<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictionproject;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Native Joomla 5/6 administrator edit view for prediction projects. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form || !$this->item) {
            throw new \RuntimeException('Prediction project form data is unavailable.', 500);
        }

        $this->item->name = '';
        Factory::getApplication()->setUserState(
            'com_sportsmanagement.pid',
            (int) ($this->item->project_id ?? 0)
        );

        $layout = strtolower((string) $this->getLayout());

        if (in_array($layout, ['edit_3', 'edit_4'], true)) {
            $this->setLayout('edit');
        }

        parent::display($tpl);
    }
}
