<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextdfbnetplayerimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
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

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/footer/tmpl');
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/listheader/tmpl');
    }

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

    private function prepareDefault(): void
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';

        $this->project = $app->getUserState($option . 'project');
        $this->request_url = Uri::getInstance()->toString();
        $this->config = ComponentHelper::getParams('com_media');
        $this->revisionDate = '2011-04-28 - 12:00';
        $this->import_version = 'NEW';
        $this->lists = $this->buildFilters();
    }

    private function prepareUpdate(): void
    {
        $app = Factory::getApplication();
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

    private function buildFilters(): array
    {
        $app = Factory::getApplication();
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

    protected function addToolbar(): void
    {
        $this->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.jlextdfbnetplayerimport',
            Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
            ['version' => 'auto']
        );

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT'), 'dfbnet');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
        ToolbarHelper::divider();
        ToolbarHelper::preferences('com_sportsmanagement');
    }
}
