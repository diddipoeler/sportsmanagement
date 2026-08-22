<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Statistic;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator edit view for a statistic definition. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public ?Form $formparams = null;

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
            throw new \RuntimeException('Statistic form could not be loaded.', 500);
        }

        $statisticId = (int) ($this->item->id ?? 0);
        $isNew = $statisticId <= 0;

        if ($isNew) {
            $this->item->class = 'basic';
            $this->item->calculated = 0;
            $this->form->setValue('class', null, 'basic');
            $this->form->setValue('calculated', null, 0);
        }

        if ($this->getLayout() === 'edit_3') {
            $this->setLayout('edit');
        }

        $this->formparams = $this->loadStatisticParameters(
            (string) ($this->item->class ?? 'basic'),
            (string) ($this->item->params ?? '')
        );

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_STATISTIC_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_STATISTIC_EDIT'),
            'statistic'
        );
        ToolbarHelper::apply('statistic.apply');
        ToolbarHelper::save('statistic.save');
        ToolbarHelper::save2new('statistic.save2new');
        ToolbarHelper::save2copy('statistic.save2copy');
        ToolbarHelper::cancel('statistic.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }

    private function loadStatisticParameters(string $class, string $stored): ?Form
    {
        $class = preg_replace('/[^A-Za-z0-9_-]/', '', trim($class)) ?: 'basic';
        $path = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/statistics/' . $class . '.xml';

        if (!is_file($path)) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('JLIB_FORM_ERROR_XML_FILE_DID_NOT_LOAD', $path),
                'warning'
            );

            return null;
        }

        try {
            Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/fields');
            $registry = new Registry();

            if ($stored !== '') {
                $decoded = json_decode($stored, true);

                if (is_array($decoded)) {
                    $registry->loadArray($decoded);
                } else {
                    $registry->loadString($stored);
                }
            }

            $form = Form::getInstance(
                'com_sportsmanagement.statistic.params.' . $class,
                $path,
                ['control' => 'params'],
                false,
                '/config'
            );
            $form->bind($registry);

            return $form;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }
}
