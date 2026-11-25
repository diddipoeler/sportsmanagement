<?php
/**
 * Native Joomla 5/6 administrator DFB.net player/import view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextdfbnetplayerimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator view for the DFB.net player/import workflow. */
final class HtmlView extends BaseHtmlView
{
    public $project = null;
    public string $request_url = '';
    public ?Registry $config = null;
    public string $revisionDate = '2011-04-28 - 12:00';
    public string $import_version = 'NEW';
    public array $uploadArray = [];
    public $importData = null;
    public array $lists = [];
    public array $search_nation = [];
    public array $postData = [];
    public float $starttime = 0.0;

    /**
     * Initialise the administrator DFB.net import view and register shared footer and list-header template directories.
     * @param array $config Joomla view configuration.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/footer/tmpl');
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/listheader/tmpl');
    }

    /**
     * Record the start time, normalise historical layout suffixes and prepare the default import or update screen. Configure the toolbar before delegating rendering to Joomla.
     * @param string|null $tpl Optional template name.
     * @return void
     */
    public function display($tpl = null)
    {
        $this->starttime = microtime(true);
        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'default';
        $this->setLayout($layout);

        if ($layout === 'default_update') {
            $this->prepareUpdate();
        } else {
            $this->prepareDefault();
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Restore the selected project from administrator user state, load media settings and initialise the import metadata and country and season filters.
     * @return void
     */
    private function prepareDefault(): void
    {
        $app = self::administratorApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';

        $this->project = $app->getUserState($option . 'project');
        $this->request_url = Uri::getInstance()->toString();
        $this->config = ComponentHelper::getParams('com_media');
        $this->revisionDate = '2011-04-28 - 12:00';
        $this->import_version = 'NEW';
        $this->lists = $this->buildFilters();
    }

    /**
     * Restore the project and uploaded-file context, collect the submitted form data and obtain update information from the assigned import model.
     * @return void
     * @throws \RuntimeException If the model does not provide the update-data method.
     */
    private function prepareUpdate(): void
    {
        $app = self::administratorApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';
        $model = $this->getModel();

        if (!is_object($model) || !method_exists($model, 'getUpdateData')) {
            throw new \RuntimeException('DFB.net import update data is unavailable.', 500);
        }

        $this->project = $app->getUserState($option . 'project');
        $this->request_url = Uri::getInstance()->toString();
        $this->config = ComponentHelper::getParams('com_media');
        $this->uploadArray = (array) $app->getUserState($option . 'uploadArray', []);
        $this->postData = $app->getInput()->post->getArray();
        $this->importData = $model->getUpdateData();
    }

    /**
     * Create the native Seasons model without request-dependent state, load available seasons and translated country options and build the corresponding selection controls.
     * @return array Filter options and rendered selection lists.
     * @throws \RuntimeException If the Seasons model is unavailable.
     */
    private function buildFilters(): array
    {
        $app = self::administratorApplication();
        $seasonsModel = $app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Seasons', 'Administrator', ['ignore_request' => true]);

        if (!is_object($seasonsModel) || !method_exists($seasonsModel, 'getSeasons')) {
            throw new \RuntimeException('SportsManagement Seasons model is unavailable.', 500);
        }

        $seasons = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name'),
        ];
        $allSeasons = $seasonsModel->getSeasons();
        $seasons = array_merge($seasons, is_array($allSeasons) ? $allSeasons : []);

        $countries = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];
        $countryOptions = $this->getCountryOptions();
        $countries = array_merge($countries, $countryOptions);
        $this->search_nation = $countryOptions;

        return [
            'nation' => $countries,
            'nation2' => HTMLHelper::_(
                'select.genericList',
                $countries,
                'filter_nation',
                'class="inputbox" style="width:220px"',
                'value',
                'text',
                'DEU'
            ),
            'seasons' => HTMLHelper::_(
                'select.genericList',
                $seasons,
                'filter_season',
                'class="inputbox" style="width:220px"',
                'id',
                'name',
                0
            ),
        ];
    }

    /**
     * Load country codes and names from the default SportsManagement database connection, translate their names and sort the resulting selection options using natural case-insensitive ordering.
     * @return array<int,object> Translated country options.
     */
    private function getCountryOptions(): array
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, 0);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('alpha3'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        $options = [];
        foreach ($db->loadObjectList() ?: [] as $country) {
            $options[] = HTMLHelper::_(
                'select.option',
                (string) $country->alpha3,
                Text::_((string) $country->name)
            );
        }

        usort(
            $options,
            static fn (object $left, object $right): int => strnatcasecmp((string) $left->text, (string) $right->text)
        );

        return $options;
    }

    /**
     * Activate the importer stylesheet for HTML documents through Joomla's Web Asset Manager and configure the title, back action and component preferences.
     * @return void
     */
    protected function addToolbar(): void
    {
        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'com_sportsmanagement.jlextdfbnetplayerimport',
                'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
                ['version' => 'auto']
            );
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT'), 'dfbnet');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
        ToolbarHelper::divider();
        ToolbarHelper::preferences('com_sportsmanagement');
    }
    /**
     * Resolve the Joomla administrator application through its dependency injection container and validate the administrator client.
     * @return AdministratorApplication Active administrator application.
     * @throws \RuntimeException If the resolved application is not the administrator client.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement DFB.net player import view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
