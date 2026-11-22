<?php
/**
 * Native Joomla 5/6 administrator edit view for a prediction group.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongroup;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Document\HtmlDocument;
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

    /**
     * Prepare and render the prediction-group editing interface.
     *
     * Resolve the Joomla administrator application, hide the main menu
     * while editing, obtain the form, record and state from the MVC model,
     * and report any loading errors. Enable the Joomla form-validation
     * asset on HTML documents, configure group actions in the toolbar
     * and delegate rendering to the base administrator view.
     *
     * @param string|null $tpl Optional Joomla layout template suffix.
     * @return void The parent view renders the form.
     * @throws \RuntimeException If the form or record cannot be loaded.
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

        if (!$this->form || !$this->item) {
            throw new \RuntimeException('Prediction group form could not be loaded.', 500);
        }

        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->useScript('form.validate');
        }
        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add prediction-group editing actions to the Joomla toolbar.
     *
     * Select the translated title based on whether the current record
     * is new, then register apply, save, save-as-new and cancel commands.
     * Controller permissions are checked when each command is submitted.
     *
     * @return void The Joomla administrator toolbar is updated.
     */
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
    /**
     * Resolve and verify the currently active administrator application.
     *
     * Use Joomla's dependency-injection container to obtain the concrete
     * administrator client before accessing backend request and document
     * state; reject execution from the site or another application client.
     *
     * @return AdministratorApplication Validated backend application.
     * @throws \RuntimeException If the current client is not administrator.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement prediction group view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
