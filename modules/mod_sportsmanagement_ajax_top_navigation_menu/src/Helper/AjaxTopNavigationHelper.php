<?php
namespace Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class AjaxTopNavigationHelper
{
    /**
     * Prepare the navigation data for the Joomla 5/6 module dispatcher.
     *
     * The compatibility helper remains the query/link implementation while the
     * native dispatcher owns module bootstrapping, assets and AJAX behaviour.
     */
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $this->loadLegacyDependencies();

        $input = $app->getInput();
        /** @var DatabaseInterface $database */
        $database = $app->getContainer()->get(DatabaseInterface::class);
        $legacyHelper = new \modSportsmanagementAjaxTopNavigationMenuHelper($params, $app, $database);
        $points = $legacyHelper->getFederations() ?: [];
        $tabPoints = [];
        $navpoint = [];
        $navpointLabel = [];

        for ($i = 1; $i < 23; $i++) {
            $navpoint[] = $params->get('navpoint' . $i);
            $navpointLabel[] = (string) $params->get('navpoint_label' . $i, '');
        }

        foreach ($points as $row) {
            $tabPoints[] = (string) $row->name;
        }

        $projectId = $input->getInt('p', 0);
        $teamId = $input->getInt('tid', 0);
        $divisionId = $input->getInt('division', 0);

        $legacyHelper->setProject($projectId, $teamId, $divisionId);
        $leagueId = (int) $legacyHelper->getLeagueId();
        $countryId = $legacyHelper->getProjectCountry($projectId);
        $leagueAssocId = (int) $legacyHelper->getLeagueAssocId();
        $subAssocParentId = (int) $legacyHelper->getAssocParentId($leagueAssocId);
        $subSubAssocParentId = (int) $legacyHelper->getAssocParentId($subAssocParentId);
        $project = $legacyHelper->getProject($leagueId);

        $assocId = 0;
        $subassocId = 0;
        $subsubassocId = 0;
        $subsubsubassocId = 0;

        if ($subSubAssocParentId > 0) {
            $assocId = $subSubAssocParentId;
            $subassocId = $subAssocParentId;
            $subsubassocId = $leagueAssocId;
        } elseif ($subAssocParentId > 0) {
            $assocId = $subAssocParentId;
            $subassocId = $leagueAssocId;
        } elseif ($leagueAssocId > 0) {
            $assocId = $leagueAssocId;
        }

        $federationSelect = [];
        $countryAssocSelect = [];
        $leagueSelect = [];
        $countrySubAssocSelect = [];
        $countrySubSubAssocSelect = [];
        $countrySubSubSubAssocSelect = [];
        $projectSelect = [];
        $divisionsSelect = [];

        foreach ($points as $row) {
            $key = (string) $row->name;
            $federationSelect[$key] = $legacyHelper->getFederationSelect($key, (int) $row->id);
            $countryAssocSelect[$key] = ['assocs' => []];
            $leagueSelect[$key] = ['leagues' => []];
            $countrySubAssocSelect[$key] = ['assocs' => []];
            $countrySubSubAssocSelect[$key] = ['subassocs' => []];
            $countrySubSubSubAssocSelect[$key] = ['subsubassocs' => []];
            $projectSelect[$key] = ['projects' => [], 'teams' => []];
            $divisionsSelect[$key] = ['divisions' => []];
        }

        $countryFederation = $legacyHelper->getCountryFederation($countryId) ?: 'NONFED';
        $this->ensureFederationBuckets(
            (string) $countryFederation,
            $countryAssocSelect,
            $leagueSelect,
            $countrySubAssocSelect,
            $countrySubSubAssocSelect,
            $countrySubSubSubAssocSelect,
            $projectSelect,
            $divisionsSelect
        );

        $key = (string) $countryFederation;

        if ($countryId) {
            $countryAssocSelect[$key]['assocs'] = $legacyHelper->getCountryAssocSelect($countryId) ?: [];
            $leagueSelect[$key]['leagues'] = $legacyHelper->getAssocLeagueSelect($countryId, $assocId) ?: [];
        } else {
            $countryAssocSelect[$key]['assocs'] = [HTMLHelper::_('select.option', 0, Text::_('-- Regionalverbände -- '))];
            $leagueSelect[$key]['leagues'] = [HTMLHelper::_('select.option', 0, Text::_('--'))];
        }

        if ($assocId > 0) {
            $countrySubAssocSelect[$key]['assocs'] = $legacyHelper->getCountrySubAssocSelect($assocId) ?: [];
            $leagueSelect[$key]['leagues'] = $legacyHelper->getAssocLeagueSelect($countryId, $assocId) ?: [];
        }

        if ($subassocId > 0) {
            $countrySubSubAssocSelect[$key]['subassocs'] = $legacyHelper->getCountrySubSubAssocSelect($subassocId) ?: [];
            $leagueSelect[$key]['leagues'] = $legacyHelper->getAssocLeagueSelect($countryId, $subassocId) ?: [];
        }

        if ($subsubassocId > 0) {
            $countrySubSubSubAssocSelect[$key]['subsubassocs'] = $legacyHelper->getCountrySubSubAssocSelect($subsubassocId) ?: [];
            $leagueSelect[$key]['leagues'] = $legacyHelper->getAssocLeagueSelect($countryId, $subsubassocId) ?: [];
        }

        if ($leagueId > 0) {
            $projectSelect[$key]['projects'] = $legacyHelper->getProjectSelect($leagueId) ?: [];
        } else {
            $projectSelect[$key]['projects'] = [
                HTMLHelper::_('select.option', 0, Text::_((string) $params->get('text_project_dropdown'))),
            ];
        }

        if ($projectId > 0) {
            $legacyHelper->setProject($projectId, $teamId, $divisionId);
            $divisionsSelect[$key]['divisions'] = $legacyHelper->getDivisionSelect($projectId) ?: [];
            $projectSelect[$key]['teams'] = $legacyHelper->getTeamSelect($projectId) ?: [];
        } else {
            $projectSelect[$key]['teams'] = [
                HTMLHelper::_('select.option', 0, Text::_((string) $params->get('text_teams_dropdown'))),
            ];
        }

        return [
            'helper' => $legacyHelper,
            'points' => $points,
            'tab_points' => $tabPoints,
            'navpoint' => $navpoint,
            'navpoint_label' => $navpointLabel,
            'user_name' => '',
            'project_id' => $projectId,
            'team_id' => $teamId,
            'division_id' => $divisionId,
            'league_id' => $leagueId,
            'country_id' => $countryId,
            'league_assoc_id' => $leagueAssocId,
            'sub_assoc_parent_id' => $subAssocParentId,
            'sub_sub_assoc_parent_id' => $subSubAssocParentId,
            'assoc_id' => $assocId,
            'subassoc_id' => $subassocId,
            'subsubassoc_id' => $subsubassocId,
            'subsubsubassoc_id' => $subsubsubassocId,
            'project' => $project,
            'country_federation' => $countryFederation,
            'federationselect' => $federationSelect,
            'countryassocselect' => $countryAssocSelect,
            'leagueselect' => $leagueSelect,
            'countrysubassocselect' => $countrySubAssocSelect,
            'countrysubsubassocselect' => $countrySubSubAssocSelect,
            'countrysubsubsubassocselect' => $countrySubSubSubAssocSelect,
            'projectselect' => $projectSelect,
            'divisionsselect' => $divisionsSelect,
            'clientConfig' => [
                'moduleId' => (int) ($module->id ?? 0),
                'baseUrl' => rtrim((string) Uri::base(), '/') . '/',
                'federations' => $tabPoints,
                'navpoint' => $navpoint,
                'navpointLabel' => $navpointLabel,
                'showNavLinks' => (bool) $params->get('show_nav_links', 1),
                'loader' => 'modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif',
            ],
        ];
    }

    private function loadLegacyDependencies(): void
    {
        $file = JPATH_SITE . '/modules/mod_sportsmanagement_ajax_top_navigation_menu/helper.php';

        if (is_file($file)) {
            require_once $file;
        }
    }

    private function ensureFederationBuckets(
        string $key,
        array &$countryAssocSelect,
        array &$leagueSelect,
        array &$countrySubAssocSelect,
        array &$countrySubSubAssocSelect,
        array &$countrySubSubSubAssocSelect,
        array &$projectSelect,
        array &$divisionsSelect
    ): void {
        $countryAssocSelect[$key] ??= ['assocs' => []];
        $countryAssocSelect[$key]['assocs'] ??= [];
        $leagueSelect[$key] ??= ['leagues' => []];
        $leagueSelect[$key]['leagues'] ??= [];
        $countrySubAssocSelect[$key] ??= ['assocs' => []];
        $countrySubAssocSelect[$key]['assocs'] ??= [];
        $countrySubSubAssocSelect[$key] ??= ['subassocs' => []];
        $countrySubSubAssocSelect[$key]['subassocs'] ??= [];
        $countrySubSubSubAssocSelect[$key] ??= ['subsubassocs' => []];
        $countrySubSubSubAssocSelect[$key]['subsubassocs'] ??= [];
        $projectSelect[$key] ??= ['projects' => [], 'teams' => []];
        $projectSelect[$key]['projects'] ??= [];
        $projectSelect[$key]['teams'] ??= [];
        $divisionsSelect[$key] ??= ['divisions' => []];
        $divisionsSelect[$key]['divisions'] ??= [];
    }
}
