<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Playground edit view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Playground;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsReadHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundModel;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 administrator edit view for a playground. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public ?Form $extended = null;
    public ?Form $extendeduser = null;
    public ?Form $logoHistoryForm = null;
    public array $extraFields = [];
    public array $playgroundnotic = [];
    public array $logohistory = [];
    public bool $map = false;

    /**
     * Load the native playground model's form, item and state directly.
     * Prepare extended fields, logo history, map state and toolbar actions.
     * Redact internal model and optional form errors before rendering.
     * @param string|null $tpl Optional layout suffix.
     * @return void
     */
    public function display($tpl = null)
    {
        $app = self::administratorApplication();
        $app->getInput()->set('hidemainmenu', true);

        $model = $this->getModel();
        if (!$model instanceof PlaygroundModel) {
            throw new \RuntimeException('Playground view requires PlaygroundModel.', 500);
        }

        $this->form = $model->getForm();
        $this->item = $model->getItem();
        $this->state = $model->getState();

        if ($errors = $model->getErrors()) {
            Log::add(__METHOD__ . ': model reported ' . count((array) $errors) . ' errors.', Log::ERROR, 'com_sportsmanagement');
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 500);
        }

        if (!$this->form instanceof Form || !is_object($this->item)) {
            throw new \RuntimeException('Playground form could not be loaded.', 500);
        }

        $playgroundId = (int) ($this->item->id ?? 0);
        $extendedLoader = new ExtendedFormHelper();
        $this->extended = $extendedLoader->load(
            'extended',
            'playground',
            (string) ($this->item->extended ?? '')
        );
        $this->extendeduser = $extendedLoader->load(
            'extendeduser',
            'playground',
            (string) ($this->item->extendeduser ?? '')
        );

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $databaseSelector = $app->getInput()->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );
        $database = (new SportsManagementDatabaseResolver())->resolve($databaseSelector, $joomlaDatabase);

        $this->extraFields = (new ExtraFieldsReadHelper())->getFields(
            $playgroundId,
            'playground',
            'backend',
            $database
        );

        if ($playgroundId > 0) {
            $this->playgroundnotic = $model->getPlaygroundNotic($playgroundId);
            $this->logohistory = $model->getlogohistoryPlayground($playgroundId, 0);
            $this->configureMapState($app);
        }

        $this->logoHistoryForm = $this->loadLogoHistoryForm($app);
        $this->registerDetailRowScript();
        $this->configureToolbar($playgroundId <= 0);

        parent::display($tpl);
    }

    /**
     * Check whether the playground coordinates can be displayed on a map.
     *
     * @param CMSApplicationInterface $app app value.
     * @return void
     */
    private function configureMapState(CMSApplicationInterface $app): void
    {
        $latitude = is_numeric($this->item->latitude ?? null) ? (float) $this->item->latitude : 255.0;
        $longitude = is_numeric($this->item->longitude ?? null) ? (float) $this->item->longitude : 255.0;
        $this->map = $latitude >= -90.0 && $latitude <= 90.0
            && $longitude >= -180.0 && $longitude <= 180.0;

        if (!$this->map) {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'warning');
        }
    }

    /**
     * Load the optional seasonal playground logo history form.
     *
     * @param CMSApplicationInterface $app app value.
     * @return ?Form
     */
    private function loadLogoHistoryForm(CMSApplicationInterface $app): ?Form
    {
        $path = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms/playgroundlogohistory.xml';

        if (!is_file($path)) {
            return null;
        }

        try {
            $factory = Factory::getContainer()->get(FormFactoryInterface::class);
            $form = $factory->createForm(
                'com_sportsmanagement.playgroundlogohistory',
                ['control' => '']
            );

            return $form->loadFile($path) ? $form : null;
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'com_sportsmanagement');
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');

            return null;
        }
    }

    /**
     * Register playground create, edit and save toolbar actions.
     *
     * @param bool $isNew isNew value.
     * @return void
     */
    private function configureToolbar(bool $isNew): void
    {
        $title = $isNew ? Text::_('JTOOLBAR_NEW') : Text::_('JTOOLBAR_EDIT');
        $name = trim((string) ($this->item->name ?? ''));

        if ($name !== '') {
            $title .= ': ' . $name;
        }

        ToolbarHelper::title($title, 'playground');
        ToolbarHelper::apply('playground.apply');
        ToolbarHelper::save('playground.save');
        ToolbarHelper::save2new('playground.save2new');
        ToolbarHelper::save2copy('playground.save2copy');
        ToolbarHelper::cancel('playground.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }

    /**
     * Register JavaScript to edit additional playground detail rows.
     * @return void
     */
    private function registerDetailRowScript(): void
    {
        $document = $this->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $document->getWebAssetManager()->registerAndUseScript(
            'com_sportsmanagement.admin.playground-detail',
            'administrator/components/com_sportsmanagement/assets/js/playground-detail.js',
            ['version' => 'auto'],
            ['defer' => true]
        );
    }

    /**
     * Resolve the administrator application using the shared client service.
     * @return AdministratorApplication Validated backend application.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }
}
