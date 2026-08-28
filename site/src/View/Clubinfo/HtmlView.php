<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Clubinfo;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ClubHistoryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ClubKmlHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedDataHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ExtraFieldsReadHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\LocationAddressHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\ClubHistoryViewDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ClubinfoModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ClubinfoViewDataModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public $model;
    public $jinput;
    public $document;
    public ?object $club = null;
    public ?object $clubassoc = null;
    public ?object $new_club = null;
    public $geo = null;
    public int $databaseSelector = 0;
    public bool $checkextrafields = false;
    public array $extrafields = [];
    public array $extended = [];
    public array $teams = [];
    public array $stadiums = [];
    public array $playgrounds = [];
    public bool $showediticon = false;
    public ?string $address_string = null;
    public $rssfeeditems = null;
    public $rssDoc = null;
    public array $mapconfig = ['map_kmlfile' => 0];
    public array $logohistory = [];
    public array $logohistory_detail = [];

    /** @deprecated Kept for third-party template compatibility. */
    public array $clubhistory = [];

    public string $clubhistoryhtml = '';

    /** @deprecated Kept for third-party template compatibility. */
    public array $clubhistoryfamilytree = [];

    /** @deprecated Kept for third-party template compatibility. */
    public $genfamilytree = null;

    public string $familytree = '';

    /** @deprecated Kept for third-party template compatibility. */
    public array $clubhistorytree = [];

    /** @deprecated Kept for third-party template compatibility. */
    public $clubhistorysorttree = '';

    public int $show_debug_info = 0;
    public int $modid = 0;
    public array $output = [];
    public string $kmlpath = '';
    public string $kmlfile = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->jinput = $this->input;
    }

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    protected function prepareView(): void
    {
        $this->document = $this->getDocument();

        /** @var ClubinfoModel $model */
        $model = $this->getModel();
        if (!$model instanceof ClubinfoModel) {
            throw new \RuntimeException('Clubinfo view requires ClubinfoModel.', 500);
        }

        $this->model = $model;
        $viewName = $this->input->getCmd('view', 'clubinfo');
        $clubId = max(0, $this->input->getInt('cid', 0));
        $this->databaseSelector = $this->input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        $this->logohistory_detail = [];
        $this->mapconfig = ['map_kmlfile' => 0];

        $database = $model->getDatabase();
        $viewDataModel = new ClubinfoViewDataModel();
        $viewDataModel->setDatabaseSelector($this->databaseSelector);
        $this->checkextrafields = ExtraFieldsReadHelper::hasFields($database, $viewName);

        $this->club = $viewDataModel->getClubById($clubId, true);
        if (!$this->club) {
            $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_TITLE');
            $this->document->setTitle($this->headertitle);
            return;
        }

        $clubId = (int) $this->club->id;
        $this->logohistory = $model->getLogoHistory($clubId, 0);
        foreach ($this->logohistory as $entry) {
            $logo = (string) ($entry->logo_big ?? '');
            if ($logo !== '') {
                $this->logohistory_detail[$logo][] = (string) ($entry->seasonname ?? '');
            }
        }

        if ($this->checkextrafields) {
            $this->extrafields = ExtraFieldsReadHelper::load($database, $clubId, $viewName);
        }

        $this->clubassoc = $viewDataModel->getAssociationById((int) ($this->club->associations ?? 0));
        $this->extended = ExtendedDataHelper::toArray((string) ($this->club->extended ?? ''));

        $showTeams = (int) ($this->config['show_teams_of_club'] ?? 1);
        $this->teams = $viewDataModel->getTeamsByClub($clubId, $showTeams);

        if ((int) ($this->project->id ?? 0) > 0) {
            $this->stadiums = $viewDataModel->getStadiumIds($clubId, $this->teams);
            $this->playgrounds = $viewDataModel->getPlaygroundsByIds($this->stadiums);
        }

        $identity = $this->getApplication()->getIdentity();
        $this->showediticon = $identity->authorise('core.edit', 'com_sportsmanagement')
            || $identity->authorise('club.edit', 'com_sportsmanagement')
            || ((int) $identity->id > 0 && (int) ($this->club->admin ?? 0) === (int) $identity->id);
        $this->address_string = LocationAddressHelper::build($database, $this->club);

        if (!empty($this->config['show_club_rssfeed'])) {
            $rssfeedlink = (string) ($this->extended['COM_SPORTSMANAGEMENT_CLUB_RSS_FEED'] ?? '');
            if ($rssfeedlink !== '') {
                $this->rssfeeditems = $viewDataModel->getRssFeeds(
                    $rssfeedlink,
                    (int) ($this->overallconfig['rssitems'] ?? 10)
                );
            }
        }

        if (!empty($this->config['show_maps'])) {
            if (!empty($this->config['use_which_map'])) {
                $this->mapconfig = array_merge(
                    ['map_kmlfile' => 0],
                    $model->getTemplateConfig('map')
                );
            }

            if (!empty($this->mapconfig['map_kmlfile'])) {
                $latitude = is_numeric($this->club->latitude ?? null)
                    ? (float) $this->club->latitude
                    : null;
                $longitude = is_numeric($this->club->longitude ?? null)
                    ? (float) $this->club->longitude
                    : null;

                if (ClubKmlHelper::write(
                    $clubId,
                    (string) $this->address_string,
                    (string) ($this->club->name ?? ''),
                    (string) ($this->club->logo_big ?? ''),
                    $latitude,
                    $longitude
                )) {
                    $this->kmlfile = $clubId . '-club.kml';
                    $this->kmlpath = rtrim((string) Uri::root(), '/') . '/tmp/' . $this->kmlfile;
                }
            }
        }

        $this->show_debug_info = (int) ComponentHelper::getParams('com_sportsmanagement')
            ->get('show_debug_info', 0);

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_TITLE')
            . ': ' . (string) ($this->club->name ?? '');
        $this->headertitle = $pageTitle;
        $this->modid = $clubId;

        if (!empty($this->config['show_fusion'])) {
            $historyModel = new ClubHistoryViewDataModel();
            $historyModel->setDatabaseSelector($this->databaseSelector);
            $relations = $historyModel->getRelations();
            $treeMode = (int) ($this->config['show_bootstrap_tree'] ?? 0);

            $this->familytree = ClubHistoryPresentationHelper::renderPredecessorTree(
                $relations,
                $clubId,
                $treeMode,
                $this->databaseSelector
            );

            if ((int) ($this->club->new_club_id ?? 0) > 0) {
                $this->new_club = $viewDataModel->getClubById((int) $this->club->new_club_id, false);
                $this->clubhistoryhtml = ClubHistoryPresentationHelper::renderSuccessorHistory(
                    $relations,
                    $clubId,
                    $this->new_club,
                    $this->databaseSelector
                );
            }

            $assets = $this->document->getWebAssetManager();
            if ($treeMode > 0) {
                $assets->registerAndUseStyle(
                    'com_sportsmanagement.clubinfo.familytree',
                    'components/com_sportsmanagement/assets/css/bootstrap-familytree.css',
                    ['version' => 'auto']
                );
            } else {
                $javascript = <<<'JS'
jQuery(function ($) {
    $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
    $('.tree li.parent_li > span').on('click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else {
            children.show('fast');
            $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
        }
        e.stopPropagation();
    });
});
JS;
                $assets->addInlineScript($javascript, [], [], ['jquery']);
                $assets->registerAndUseStyle(
                    'com_sportsmanagement.clubinfo.tree',
                    'components/com_sportsmanagement/assets/css/bootstrap-tree2.css',
                    ['version' => 'auto']
                );
            }
        }

        $this->document->setTitle($pageTitle);

        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }
    }
}
