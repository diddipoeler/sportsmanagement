<?php
/**
 * Joomla 5/6 helper for the SportsManagement AJAX top navigation module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class AjaxTopNavigationHelper
{
    /**
     * Assemble safe frontend navigation selections for the AJAX module.
     *
     * Validates the database selector before the legacy navigation helper
     * resolves a connection, discards malformed federation records and
     * prepares the initial project, league and team dropdowns plus client
     * configuration. Rejects non-site application contexts.
     *
     * @param Registry $params Joomla module navigation configuration.
     * @param object $module Current Joomla module record.
     * @param CMSApplicationInterface $app Joomla site application.
     * @return array<string, mixed> Navigation selections and client settings.
     * @throws \\RuntimeException When called outside the site application.
     */
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $this->loadNavigationDataHelper();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement AJAX Top Navigation requires the Joomla site application.', 500);
        }

        $input = $app->getInput();
        $componentSelector = ComponentHelper::getParams('com_sportsmanagement')
            ->get('cfg_which_database', 0);
        $configuredSelector = $params->get('cfg_which_database', $componentSelector);
        $requestedSelector = $input->get('cfg_which_database', null, 'RAW');
        $databaseSelector = self::databaseSelector($requestedSelector ?? $configuredSelector);

        if ($databaseSelector === null) {
            // Never let malformed request or configuration data select an
            // unintended external database in NavigationDataHelper.
            return [];
        }

        // The legacy data helper still reads the Joomla Input object.
        // Set the already validated selector before constructing it.
        $input->set('cfg_which_database', $databaseSelector);

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $legacyHelper = new NavigationDataHelper($params, $app, $database);
        $points = self::federationRows($legacyHelper->getFederations());
        $tabPoints = [];
        $navpoint = [];
        $navpointLabel = [];
        $teamNavpoint = [];
        $teamNavpointLabel = [];

        for ($i = 1; $i < 18; ++$i) {
            $navpoint[] = self::textSetting($params->get('navpoint' . $i, ''));
            $navpointLabel[] = self::textSetting($params->get('navpoint_label' . $i, ''));
        }

        for ($i = 17; $i < 23; ++$i) {
            $teamNavpoint[] = self::textSetting($params->get('navpointct' . $i, ''));
            $teamNavpointLabel[] = self::textSetting($params->get('navpointct_label' . $i, ''));
        }

        foreach ($points as $row) {
            $tabPoints[] = (string) $row->name;
        }

        $projectId = max(0, $input->getInt('p', 0));
        $teamId = max(0, $input->getInt('tid', 0));
        $divisionId = max(0, $input->getInt('division', 0));
         $legacyHelper->setProject($projectId, $teamId, $divisionId);
        $leagueId = max(0, (int) $legacyHelper->getLeagueId());
        $countryId = $legacyHelper->getProjectCountry($projectId);
        $leagueAssocId = max(0, (int) $legacyHelper->getLeagueAssocId());
        $subAssocParentId = (int) $legacyHelper->getAssocParentId($leagueAssocId);
        $subSubAssocParentId = (int) $legacyHelper->getAssocParentId($subAssocParentId);
        $project = $legacyHelper->getProject();

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
            $key = $row->name;
            $federationSelect[$key] = $legacyHelper->getFederationSelect($key, self::databaseSelector($row->id));
            $countryAssocSelect[$key] = ['assocs' => []];
            $leagueSelect[$key] = ['leagues' => []];
            $countrySubAssocSelect[$key] = ['assocs' => []];
            $countrySubSubAssocSelect[$key] = ['subassocs' => []];
            $countrySubSubSubAssocSelect[$key] = ['subsubassocs' => []];
            $projectSelect[$key] = ['projects' => [], 'teams' => []];
            $divisionsSelect[$key] = ['divisions' => []];
        }

        $countryFederation = self::textSetting($legacyHelper->getCountryFederation($countryId));
        $countryFederation = $countryFederation !== '' ? $countryFederation : 'NONFED';
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
                HTMLHelper::_('select.option', 0, Text::_(self::textSetting($params->get('text_project_dropdown')))),
            ];
        }

        if ($projectId > 0) {
            $legacyHelper->setProject($projectId, $teamId, $divisionId);
            $divisionsSelect[$key]['divisions'] = $legacyHelper->getDivisionSelect($projectId) ?: [];
            $projectSelect[$key]['teams'] = $legacyHelper->getTeamSelect($projectId) ?: [];
        } else {
            $projectSelect[$key]['teams'] = [
                HTMLHelper::_('select.option', 0, Text::_(self::textSetting($params->get('text_teams_dropdown')))),
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
                'moduleId' => self::databaseSelector($module->id ?? 0) ?? 0,
                'baseUrl' => rtrim((string) Uri::root(), '/') . '/',
                'ajaxUrl' => rtrim((string) Uri::root(), '/') . '/index.php',
                'cfgWhichDatabase' => $databaseSelector,
                'itemId' => max(0, $input->getInt('Itemid', 0)),
                'federations' => $tabPoints,
                'projectType' => self::textSetting($project->project_type ?? ''),
                'navpoint' => $navpoint,
                'navpointLabel' => $navpointLabel,
                'teamNavpoint' => $teamNavpoint,
                'teamNavpointLabel' => $teamNavpointLabel,
                'showNavLinks' => (bool) $params->get('show_nav_links', 1),
                'alltimePoints' => self::textSetting($params->get('show_alltimetable_points', '3,1,0')),
                'showAlltimeNavLinks' => (bool) $params->get('show_alltimetable_nav_links', 0),
                'alltimeText' => self::textSetting($params->get('show_alltimetable_text', '')),
                'showTournamentNavLinks' => (bool) $params->get('show_tournament_nav_links', 0),
                'tournamentText' => self::textSetting($params->get('show_tournament_text', '')),
                'loader' => 'modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif',
            ],
        ];
    }

    /**
     * Validate a Joomla database selector without lossy integer coercion.
     *
     * Zero and zero-padded decimal values are allowed. Arrays, booleans,
     * partial numeric strings, negative numbers and overflows are rejected.
     *
     * @param mixed $value Raw request or module database setting.
     * @return int|null Valid nonnegative selector, or null when malformed.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (!is_int($value) && (!is_string($value) || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return null;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? null : $number;
    }

    /**
     * Convert a text module setting without PHP 8 array-to-string warnings.
     *
     * @param mixed $value Untrusted legacy module or database field.
     * @return string Value when it is text, or an empty string otherwise.
     */
    private static function textSetting(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Keep only valid federation objects returned by the legacy data helper.
     *
     * Federation names are used as navigation keys and positive IDs select
     * dependent records. Malformed rows must not create bogus selections.
     *
     * @param mixed $rows Raw federation query result.
     * @return array<int, object> Valid federation records with safe IDs/names.
     */
    private static function federationRows(mixed $rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $result = [];

        foreach ($rows as $row) {
            if (!is_object($row) || !is_string($row->name ?? null)
                || trim($row->name) === '') {
                continue;
            }

            $id = self::databaseSelector($row->id ?? null);

            if ($id === null || $id <= 0) {
                continue;
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * Load the companion navigation database helper when available.
     *
     * Uses the module's native helper file and avoids repeated includes.
     *
     * @return void
     */
    private function loadNavigationDataHelper(): void
    {
        $file = __DIR__ . '/NavigationDataHelper.php';

        if (is_file($file)) {
            require_once $file;
        }
    }

    /**
     * Initialise all cascading dropdown arrays for a federation key.
     *
     * Keeps already-populated arrays intact while adding missing association,
     * league, project, team and division buckets required by the template.
     *
     * @param string $key Federation identifier used as an array key.
     * @param array<string, mixed> $countryAssocSelect Country associations.
     * @param array<string, mixed> $leagueSelect League selection buckets.
     * @param array<string, mixed> $countrySubAssocSelect Subassociations.
     * @param array<string, mixed> $countrySubSubAssocSelect Lower associations.
     * @param array<string, mixed> $countrySubSubSubAssocSelect Lowest associations.
     * @param array<string, mixed> $projectSelect Project and team buckets.
     * @param array<string, mixed> $divisionsSelect Division buckets.
     * @return void
     */
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
