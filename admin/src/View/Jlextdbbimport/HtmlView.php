<?php
/**
 * Native Joomla 5/6 administrator DBB import view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextdbbimport;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 DBB import administrator view. */
final class HtmlView extends BaseHtmlView
{
    public $project = null;
    public string $request_url = '';
    public ?Registry $config = null;
    public string $revisionDate = '2011-04-28 - 12:00';
    public string $import_version = 'NEW';
    public array $uploadArray = [];
    public $importData = null;

    /**
     * Initialise the administrator DBB import view and register shared footer and list-header template directories.
     * @param array $config Joomla view configuration.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/footer/tmpl');
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/listheader/tmpl');
    }

    /**
     * Translate historical default and update layout aliases to their native names, prepare the corresponding import screen and configure its toolbar before rendering.
     * @param string|null $tpl Optional template name.
     * @return void
     */
    public function display($tpl = null)
    {
        $layout = $this->getLayout();

        if (in_array($layout, ['default_3', 'default_4', 'default_5'], true)) {
            $this->setLayout('default');
            $layout = 'default';
        } elseif (in_array($layout, ['default_update_3', 'default_update_4', 'default_update_5'], true)) {
            $this->setLayout('default_update');
            $layout = 'default_update';
        }

        if ($layout === 'default_update') {
            $this->_displayDefaultUpdate();
        } else {
            $this->init();
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Restore the selected project from administrator user state, initialise the request URL and media configuration and set the historical import format metadata.
     * @return void
     */
    public function init(): void
    {
        $app = self::administratorApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');

        $this->project = $app->getUserState($option . 'project');
        $this->request_url = Uri::getInstance()->toString();
        $this->config = ComponentHelper::getParams('com_media');
        $this->revisionDate = '2011-04-28 - 12:00';
        $this->import_version = 'NEW';
    }

    /**
     * Provide the historical default-layout preparation entry point by delegating to the common initialisation method.
     * @return void
     */
    public function _displayDefault(): void
    {
        $this->init();
    }

    /**
     * Restore the project and upload context and load update information from the assigned DBB import model for the update screen.
     * @return void
     * @throws \RuntimeException If the model does not provide the update-data method.
     */
    public function _displayDefaultUpdate(): void
    {
        $app = self::administratorApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $model = $this->getModel();

        if (!is_object($model) || !method_exists($model, 'getUpdateData')) {
            throw new \RuntimeException('DBB import update data is unavailable.', 500);
        }

        $this->project = $app->getUserState($option . 'project');
        $this->request_url = Uri::getInstance()->toString();
        $this->config = ComponentHelper::getParams('com_media');
        $this->uploadArray = (array) $app->getUserState($option . 'uploadArray', []);
        $this->importData = $model->getUpdateData();
    }

    /**
     * Activate the importer stylesheet for HTML documents using Joomla's Web Asset Manager and set the translated DBB import toolbar title.
     * @return void
     */
    protected function addToolbar(): void
    {
        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'com_sportsmanagement.jlextdbbimport',
                'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
                ['version' => 'auto']
            );
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT'), 'dbb-cpanel');
    }
    /**
     * Resolve the administrator application from Joomla's dependency injection container and verify that it belongs to the administrator client.
     * @return AdministratorApplication Active administrator application.
     * @throws \RuntimeException If the resolved application is not the administrator client.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement DBB import view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
