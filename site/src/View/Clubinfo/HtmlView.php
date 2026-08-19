<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Clubinfo;

\defined('_JEXEC') or die;

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
    public $checkextrafields = false;
    public $extrafields = null;
    public $extended = null;
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
        $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/clubinfo/tmpl';
        parent::__construct($config);

        $this->jinput = $this->app->getInput();
        $this->document = $this->getDocument();
    }

    protected function prepareView(): void
    {
        /** @var ClubinfoModel $model */
        $model = $this->getModel();
        if (!$model instanceof ClubinfoModel) {
            throw new \RuntimeException('Clubinfo view requires ClubinfoModel.', 500);
        }

        $this->model = $model;
        $viewName = $this->view ?: 'clubinfo';
        $this->logohistory_detail = [];
        $this->mapconfig = ['map_kmlfile' => 0];
        $this->checkextrafields = \sportsmanagementHelper::checkUserExtraFields(
            'frontend',
            ClubinfoModel::$cfg_which_database,
            $viewName
        );

        $this->club = ClubinfoModel::getClub(1);
        if (!$this->club) {
            $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_TITLE');
            $this->document->setTitle($this->headertitle);
            return;
        }

        $this->logohistory = $model->getLogoHistory((int) $this->club->id, 0);
        foreach ($this->logohistory as $entry) {
            $logo = (string) ($entry->logo_big ?? '');
            if ($logo !== '') {
                $this->logohistory_detail[$logo][] = (string) ($entry->seasonname ?? '');
            }
        }

        if ($this->checkextrafields) {
            $this->extrafields = \sportsmanagementHelper::getUserExtraFields(
                (int) $this->club->id,
                'frontend',
                ClubinfoModel::$cfg_which_database,
                $viewName
            );
        }

        $this->clubassoc = ClubinfoModel::getClubAssociation((int) ($this->club->associations ?? 0));
        $this->extended = \sportsmanagementHelper::getExtended(
            $this->club->extended ?? '',
            'club',
            'ini',
            true
        );

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
        $this->address_string = ClubinfoModel::getAddressString();

        if (!empty($this->config['show_club_rssfeed'])) {
            $rssfeedlink = is_array($this->extended)
                ? (string) ($this->extended['COM_SPORTSMANAGEMENT_CLUB_RSS_FEED'] ?? '')
                : '';
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
                if (!class_exists('JSMsimpleGMapGeocoder')) {
                    \JLoader::register(
                        'JSMsimpleGMapGeocoder',
                        JPATH_SITE . '/components/com_sportsmanagement/helpers/simpleGMapGeocoder.php'
                    );
                }

                if (class_exists('JSMsimpleGMapGeocoder')) {
                    $this->geo = new \JSMsimpleGMapGeocoder();
                    $this->geo->genkml3file(
                        (int) $this->club->id,
                        (string) $this->address_string,
                        'club',
                        (string) ($this->club->logo_big ?? ''),
                        (string) ($this->club->name ?? ''),
                        $this->club->latitude ?? null,
                        $this->club->longitude ?? null
                    );
                }
            }
        }

        $this->show_debug_info = (int) ComponentHelper::getParams('com_sportsmanagement')
            ->get('show_debug_info', 0);

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_TITLE')
            . ': ' . (string) ($this->club->name ?? '');
        $this->headertitle = $pageTitle;
        $this->modid = (int) $this->club->id;

        $this->clubhistory = ClubinfoModel::getClubHistory((int) $this->club->id);
        $this->clubhistoryhtml = (string) ClubinfoModel::getClubHistoryHTML((int) $this->club->id);

        if ((int) ($this->club->new_club_id ?? 0) > 0) {
            $mainClub = $this->club;
            $mainClubId = ClubinfoModel::$clubid;

            ClubinfoModel::$club = null;
            $this->new_club = ClubinfoModel::getClub(0, (int) $this->club->new_club_id);

            ClubinfoModel::$club = $mainClub;
            ClubinfoModel::$clubid = $mainClubId;

            if ($this->new_club) {
                $link = \sportsmanagementHelperRoute::getClubInfoRoute(
                    $this->project->id ?? 0,
                    $this->new_club->id,
                    null,
                    ClubinfoModel::$cfg_which_database
                );
                $imageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM');
                $this->clubhistoryhtml = '<ul>'
                    . HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/club_from.png', $imageTitle, 'title="' . $imageTitle . '"')
                    . '&nbsp;' . HTMLHelper::link($link, (string) $this->new_club->name)
                    . '<ul>' . $this->clubhistoryhtml . '</ul></ul>';
            }
        }

        $treeFusion = is_array(ClubinfoModel::$tree_fusion) ? ClubinfoModel::$tree_fusion : [];
        $this->clubhistoryfamilytree = ClubinfoModel::fbTreeRecurse(
            (int) $this->club->id,
            '',
            [],
            $treeFusion,
            10,
            0,
            1
        );

        ClubinfoModel::$historyhtmltree = '';
        $this->genfamilytree = ClubinfoModel::generateTree(
            (int) $this->club->id,
            (int) ($this->config['show_bootstrap_tree'] ?? 0)
        );
        $this->familytree = ClubinfoModel::$historyhtmltree;

        $this->clubhistorytree = ClubinfoModel::getClubHistoryTree(
            (int) $this->club->id,
            (int) ($this->club->new_club_id ?? 0)
        );
        $this->clubhistorysorttree = ClubinfoModel::getSortClubHistoryTree(
            $this->clubhistorytree,
            (int) $this->club->id,
            (string) $this->club->name
        );

        if (!ClubinfoModel::$historyobj) {
            $this->clubhistorysorttree = '';
        }

        if (!empty($this->config['show_bootstrap_tree'])) {
            $this->document->addStyleSheet(
                Uri::base() . 'components/' . $this->option . '/assets/css/bootstrap-familytree.css'
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
            $this->document->addScriptDeclaration($javascript);
            $this->document->addStyleSheet(
                Uri::base() . 'components/' . $this->option . '/assets/css/bootstrap-tree2.css'
            );
        }

        $this->document->setTitle($pageTitle);

        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }
    }
}
