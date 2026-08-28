<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Clubinfo;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ClubKmlHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedDataHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ExtraFieldsReadHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\LocationAddressHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\ClubinfoModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
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
    public array $clubhistory = [];
    public string $clubhistoryhtml = '';
    public array $clubhistoryfamilytree = [];
    public $genfamilytree = null;
    public string $familytree = '';
    public array $clubhistorytree = [];
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
        // Joomla injects the Document after constructing the view. Keep the
        // historical property for the tmpl files, but initialise it only once
        // display() has started and the Document is available.
        $this->document = $this->getDocument();

        /** @var ClubinfoModel $model */
        $model = $this->getModel();
        if (!$model instanceof ClubinfoModel) {
            throw new \RuntimeException('Clubinfo view requires ClubinfoModel.', 500);
        }

        $this->model = $model;
        $viewName = $this->input->getCmd('view', 'clubinfo');
        $this->logohistory_detail = [];
        $this->mapconfig = ['map_kmlfile' => 0];

        $database = $model->getDatabase();
        $this->checkextrafields = ExtraFieldsReadHelper::hasFields($database, $viewName);

        $this->club = ClubinfoModel::getClub(1);
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

        $this->clubassoc = ClubinfoModel::getClubAssociation((int) ($this->club->associations ?? 0));
        $this->extended = ExtendedDataHelper::toArray((string) ($this->club->extended ?? ''));

        $showTeams = (int) ($this->config['show_teams_of_club'] ?? 1);
        $teams = ClubinfoModel::getTeamsByClubId($showTeams);
        $this->teams = is_array($teams) ? $teams : [];

        if (ClubinfoModel::$projectid > 0) {
            $stadiums = ClubinfoModel::getStadiums($showTeams);
            $playgrounds = ClubinfoModel::getPlaygrounds($showTeams);
            $this->stadiums = is_array($stadiums) ? $stadiums : [];
            $this->playgrounds = is_array($playgrounds) ? $playgrounds : [];
        }

        $this->showediticon = $model->hasEditPermission('club.edit');
        $this->address_string = LocationAddressHelper::build($database, $this->club);

        if (!empty($this->config['show_club_rssfeed'])) {
            $rssfeedlink = (string) ($this->extended['COM_SPORTSMANAGEMENT_CLUB_RSS_FEED'] ?? '');
            if ($rssfeedlink !== '') {
                $this->rssfeeditems = ClubinfoModel::getRssFeeds(
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

        $this->clubhistory = ClubinfoModel::getClubHistory($clubId);
        $this->clubhistoryhtml = (string) ClubinfoModel::getClubHistoryHTML($clubId);

        if ((int) ($this->club->new_club_id ?? 0) > 0) {
            $mainClub = $this->club;
            $mainClubId = ClubinfoModel::$clubid;

            ClubinfoModel::$club = null;
            $this->new_club = ClubinfoModel::getClub(0, (int) $this->club->new_club_id);

            ClubinfoModel::$club = $mainClub;
            ClubinfoModel::$clubid = $mainClubId;

            if ($this->new_club) {
                $link = SiteRouteHelper::view('clubinfo', [
                    'cfg_which_database' => ClubinfoModel::$cfg_which_database,
                    's' => $this->input->getInt('s', 0),
                    'p' => (int) ($this->project->id ?? 0),
                    'cid' => (int) $this->new_club->id,
                ]);
                $imageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM');
                $this->clubhistoryhtml = '<ul>'
                    . HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/club_from.png', $imageTitle, 'title="' . $imageTitle . '"')
                    . '&nbsp;' . HTMLHelper::link($link, (string) $this->new_club->name)
                    . '<ul>' . $this->clubhistoryhtml . '</ul></ul>';
            }
        }

        $treeFusion = is_array(ClubinfoModel::$tree_fusion) ? ClubinfoModel::$tree_fusion : [];
        $this->clubhistoryfamilytree = ClubinfoModel::fbTreeRecurse(
            $clubId,
            '',
            [],
            $treeFusion,
            10,
            0,
            1
        );

        ClubinfoModel::$historyhtmltree = '';
        $this->genfamilytree = ClubinfoModel::generateTree(
            $clubId,
            (int) ($this->config['show_bootstrap_tree'] ?? 0)
        );
        $this->familytree = ClubinfoModel::$historyhtmltree;

        $this->clubhistorytree = ClubinfoModel::getClubHistoryTree(
            $clubId,
            (int) ($this->club->new_club_id ?? 0)
        );
        $this->clubhistorysorttree = ClubinfoModel::getSortClubHistoryTree(
            $this->clubhistorytree,
            $clubId,
            (string) $this->club->name
        );

        if (!ClubinfoModel::$historyobj) {
            $this->clubhistorysorttree = '';
        }

        $assets = $this->document->getWebAssetManager();
        if (!empty($this->config['show_bootstrap_tree'])) {
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

        $this->document->setTitle($pageTitle);

        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }
    }
}
