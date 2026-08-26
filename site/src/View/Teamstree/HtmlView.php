<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Teamstree;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubinfoModel;
use Diddipoeler\Component\SportsManagement\Site\Model\TeamstreeModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $teams = [];
    public array $findclub = [];
    public int $firstclubid = 0;
    public array $clubhistory = [];
    public string $clubhistoryhtml = '';
    public array $clubhistoryfamilytree = [];
    public $genfamilytree = null;
    public string $familytree = '';
    public array $familyteamstree = [];
    public array $familyclub = [];
    public $document;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->document = $this->getDocument();
    }

    protected function prepareView(): void
    {
        /** @var TeamstreeModel $model */
        $model = $this->getModel();
        if (!$model instanceof TeamstreeModel) {
            throw new \RuntimeException('Teamstree view requires TeamstreeModel.', 500);
        }

        $this->teams = $model->getTeamsForTree();
        ClubinfoModel::$projectid = $model->getProjectId();
        ClubinfoModel::$cfg_which_database = $this->databaseSelector;

        $processedClubs = [];
        foreach ($this->teams as $rowclub) {
            $clubId = (int) ($rowclub->club_id ?? 0);
            if ($clubId <= 0) {
                continue;
            }

            $this->findclub[$clubId] = $clubId;
            if (isset($processedClubs[$clubId])) {
                continue;
            }
            $processedClubs[$clubId] = true;

            ClubinfoModel::$tree_fusion = [];
            ClubinfoModel::$historyhtmltree = '';
            ClubinfoModel::$first_club_id = 0;
            ClubinfoModel::$clubid = $clubId;

            $treeClubId = $clubId;
            $newClubId = (int) ($rowclub->new_club_id ?? 0);
            if ($newClubId > 0) {
                $this->firstclubid = (int) ClubinfoModel::getFirstClubId($clubId, $newClubId);
                $treeClubId = ClubinfoModel::$first_club_id > 0
                    ? ClubinfoModel::$first_club_id
                    : $clubId;
            }

            $this->clubhistory = ClubinfoModel::getClubHistory($treeClubId);
            $this->clubhistoryhtml = (string) ClubinfoModel::getClubHistoryHTML($treeClubId);
            $treeFusion = is_array(ClubinfoModel::$tree_fusion)
                ? ClubinfoModel::$tree_fusion
                : [];
            $this->clubhistoryfamilytree = ClubinfoModel::fbTreeRecurse(
                $treeClubId,
                '',
                [],
                $treeFusion,
                10,
                0,
                1
            );

            ClubinfoModel::$historyhtmltree = '';
            $this->genfamilytree = ClubinfoModel::generateTree(
                $treeClubId,
                (int) ($this->config['show_bootstrap_tree'] ?? 0)
            );
            $this->familytree = ClubinfoModel::$historyhtmltree;

            if (!array_key_exists($treeClubId, $this->familyteamstree)) {
                $this->familyteamstree[$treeClubId] = $this->familytree;
            }

            $firstClub = ClubinfoModel::getFirstClub($treeClubId);
            if ($firstClub) {
                $firstClub->club_name = (string) ($firstClub->name ?? '');
                $this->familyclub[$treeClubId] = $firstClub;
                continue;
            }

            $fallback = clone $rowclub;
            $fallback->club_name = (string) ($rowclub->club_name ?? '');
            $fallback->name = $fallback->club_name;
            $fallback->clublink = \sportsmanagementHelperRoute::getClubInfoRoute(
                $rowclub->project_slug ?? $model->getProjectId(),
                $rowclub->club_slug ?? $clubId,
                null,
                $this->databaseSelector
            );
            $this->familyclub[$treeClubId] = $fallback;
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

        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_TEAMSTREE_PAGE_TITLE');
        $this->document->setTitle($this->headertitle);
    }
}
