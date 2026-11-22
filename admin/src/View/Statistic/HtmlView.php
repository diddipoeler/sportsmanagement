<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Statistic edit view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Statistic;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
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

    /**
     * Load the statistic editing form and render its administrator layout.
     *
     * Retrieve the statistic record, form and model state; initialise new
     * records with basic-statistic defaults; map the legacy edit_3 layout
     * to the native edit layout; and load class-specific XML parameters.
     * Register statistic editing toolbar commands before rendering.
     *
     * @param string|null $tpl Optional Joomla template suffix.
     * @return void The base view renders the selected edit form.
     * @throws \RuntimeException If the statistic form cannot be loaded.
     */
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

    /**
     * Obtain the Joomla administrator application for statistic editing.
     *
     * Resolve the concrete backend application using the service container
     * and refuse to operate when the current client is not administrator.
     *
     * @return AdministratorApplication Active administrator application.
     * @throws \RuntimeException If the application has the wrong client.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement statistic view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

    /**
     * Build the class-specific form for a statistic's stored parameters.
     *
     * Restrict the statistic class to a safe XML filename, locate its
     * administrator form schema, register component form fields and load
     * JSON or legacy registry parameter values. Bind the decoded values to
     * the Joomla form. Report missing or invalid XML as a warning while
     * allowing the edit page to remain available.
     *
     * @param string $class Statistic type used to select an XML form.
     * @param string $stored Saved JSON or Joomla registry configuration.
     * @return Form|null Prepared parameter form, or null if unavailable.
     */
    private function loadStatisticParameters(string $class, string $stored): ?Form
    {
        $class = preg_replace('/[^A-Za-z0-9_-]/', '', trim($class)) ?: 'basic';
        $path = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/statistics/' . $class . '.xml';

        if (!is_file($path)) {
            self::administratorApplication()->enqueueMessage(
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
            self::administratorApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }
}
