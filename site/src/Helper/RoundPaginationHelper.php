<?php
/**
 * Joomla 5/6 matchday navigation helper for results-style views.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Joomla 5/6 matchday navigation used by results-style views.
 */
final class RoundPaginationHelper
{
    /**
     * Resolve the active Joomla frontend application for matchday navigation.
     *
     * Uses the shared site application resolver instead of relying on the
     * legacy global application; callers use its request input to preserve
     * the currently selected project, division and menu item.
     *
     * @return CMSApplicationInterface Verified site application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    public static string $nextlink = '';
    public static string $prevlink = '';

    /**
     * Render previous, next, first, last and nearby matchday links.
     *
     * Loads ordered project rounds via the native results data model, selects
     * the current round from Joomla request input and builds SEF links while
     * retaining the database, season and division context. Clears the public
     * link cache before every call, including empty or invalid projects.
     * Rejects malformed project IDs and external database settings before
     * loading a model.
     *
     * @param object|null $project Project record with its ID and current round.
     * @param int|string $cfgWhichDatabase Selected SportsManagement database.
     * @param int|string $seasonId Requested season or the project's season.
     * @return string Escaped HTML navigation, or an empty string without rounds.
     */
    public static function pagenav($project, $cfgWhichDatabase = 0, $seasonId = 0): string
    {
        // Public static navigation values are shared between render calls.
        // An invalid project must never leave the previous project's links.
        self::$prevlink = '';
        self::$nextlink = '';

        $databaseSelector = self::databaseSelector($cfgWhichDatabase);

        if (!is_object($project) || self::positiveProjectId($project->id ?? null) === 0
            || $databaseSelector === null) {
            return '';
        }

        $model = self::createResultsDataModel($project, $databaseSelector);
        $rounds = $model->getRounds('ASC', true);

        if (!$rounds) {
            self::$prevlink = '';
            self::$nextlink = '';
            return '';
        }

        $app = self::siteApplication();
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $currentRoundId = $input->getInt('r', (int) ($project->current_round ?? 0));
        if ($currentRoundId <= 0) {
            $currentRoundId = (int) ($project->current_round ?? 0);
        }

        $normalised = [];
        foreach ($rounds as $round) {
            $value = self::roundIdAndSlug($round->id ?? null);
            if ($value === null) {
                continue;
            }

            $normalised[] = (object) [
                'id' => $value['id'],
                'slug' => $value['slug'],
                'roundcode' => (int) ($round->roundcode ?? 0),
            ];
        }

        if (!$normalised) {
            return '';
        }

        $currentIndex = 0;
        foreach ($normalised as $index => $round) {
            if ($round->id === $currentRoundId) {
                $currentIndex = $index;
                break;
            }
        }

        $current = $normalised[$currentIndex];
        $first = $normalised[0];
        $last = $normalised[array_key_last($normalised)];
        $previous = $normalised[max(0, $currentIndex - 1)];
        $next = $normalised[min(count($normalised) - 1, $currentIndex + 1)];
        $division = $input->getInt('division', 0);
        $params = self::buildBaseParams($project, $databaseSelector, (int) $seasonId);

        $spacer2 = '&nbsp;&nbsp;';
        $spacer4 = '&nbsp;&nbsp;&nbsp;&nbsp;';
        $anchor = '#' . $option . '_top';
        $firstLink = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_START') . $spacer4;
        $previousLink = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PREV');
        $nextLink = $spacer4 . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NEXT');
        $lastLink = $spacer4 . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_END');
        self::$prevlink = '';
        self::$nextlink = '';

        if ($current->id !== $first->id) {
            $previousUrl = self::roundUrl($params, $previous->slug, $division, $anchor);
            self::$prevlink = $previousUrl;
            $previousLink = HTMLHelper::link($previousUrl, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PREV'));

            $firstUrl = self::roundUrl($params, $first->slug, $division, $anchor);
            $firstLink = HTMLHelper::link($firstUrl, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_START')) . $spacer4;
        }

        if ($current->id !== $last->id) {
            $nextUrl = self::roundUrl($params, $next->slug, $division, $anchor);
            self::$nextlink = $nextUrl;
            $nextLink = $spacer4 . HTMLHelper::link($nextUrl, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NEXT'));

            $lastUrl = self::roundUrl($params, $last->slug, $division, $anchor);
            $lastLink = $spacer4 . HTMLHelper::link($lastUrl, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_END'));
        }

        $pageNavigation = '';
        $low = $current->roundcode - 3;
        $high = $current->roundcode + 3;

        foreach ($normalised as $round) {
            if ($round->roundcode < $low || $round->roundcode > $high) {
                continue;
            }

            $pageNumber = str_pad((string) $round->roundcode, 2, '0', STR_PAD_LEFT);
            if ($round->id === $current->id) {
                $pageNavigation .= $spacer4 . $pageNumber;
                continue;
            }

            $url = self::roundUrl($params, $round->slug, $division, $anchor);
            $pageNavigation .= $spacer4 . HTMLHelper::link($url, $pageNumber);
        }

        return '<span class="pageNav">&laquo;'
            . $spacer2 . $firstLink . $previousLink . $pageNavigation
            . $nextLink . $lastLink . $spacer2 . '&raquo;</span>';
    }

    /**
     * Build the matchday dropdown for legacy result-editing layouts.
     *
     * Retrieves round options through the native results model, preserves
     * division, sorting and current menu context in routed option URLs, and
     * selects the option matching the current request round. Returns no
     * markup when the project ID or database selector is invalid, or no usable
     * rounds are available.
     *
     * @param object|null $project Project containing an ID and season metadata.
     * @param int $cfgWhichDatabase Selected SportsManagement database.
     * @param int $seasonId Explicit season or zero for the project season.
     * @param string $layout Joomla results layout to include in option URLs.
     * @return string Joomla select-list markup or an empty string.
     */
    public static function selectNavigation(
        $project,
        int $cfgWhichDatabase = 0,
        int $seasonId = 0,
        string $layout = 'form'
    ): string {
        if (!is_object($project) || self::positiveProjectId($project->id ?? null) === 0
            || self::databaseSelector($cfgWhichDatabase) === null) {
            return '';
        }

        $model = self::createResultsDataModel($project, $cfgWhichDatabase);
        $rounds = $model->getRoundOptions('ASC');
        if (!$rounds) {
            return '';
        }

        $input = self::siteApplication()->getInput();
        $params = self::buildBaseParams($project, $cfgWhichDatabase, $seasonId);
        $params['view'] = 'results';
        $params['layout'] = $layout;
        $params['division'] = $input->getInt('division', 0);
        $params['mode'] = $input->getInt('mode', 0);
        $params['order'] = $input->getInt('order', 0);

        $currentRound = $input->getInt('r', (int) ($project->current_round ?? 0));
        $currentUrl = self::roundUrl($params, (string) max(0, $currentRound), $params['division'], '');
        $options = [];

        foreach ($rounds as $round) {
            $value = self::roundIdAndSlug($round->slug ?? $round->value ?? null);
            if ($value === null) {
                continue;
            }

            $url = self::roundUrl($params, $value['slug'], $params['division'], '');
            $options[] = HTMLHelper::_('select.option', $url, (string) ($round->text ?? $value['slug']));

            if ($value['id'] === $currentRound) {
                $currentUrl = $url;
            }
        }

        if ($options === []) {
            return '';
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            'select-round',
            'onchange="top.location.href=this.options[this.selectedIndex].value;"',
            'value',
            'text',
            $currentUrl
        );
    }

    /**
     * Read the most recently generated next-round link.
     *
     * Exposes the backward-compatible static navigation cache to legacy
     * templates without generating new database queries or URLs.
     *
     * @return string Routed next-round URL, or an empty string at the last round.
     */
    public function getnextlink(): string
    {
        return self::$nextlink;
    }

    /**
     * Read the most recently generated previous-round link.
     *
     * Provides compatibility for legacy result views using the shared
     * navigation cache populated by pagenav().
     *
     * @return string Routed previous-round URL, or an empty string at the first round.
     */
    public function getprevlink(): string
    {
        return self::$prevlink;
    }

    /**
     * Joomla round selectors can contain ID:alias values, but leading
     * numbers followed by arbitrary text must never turn into valid links.
     *
     * Rejects non-numeric or overflowing IDs and malformed aliases before
     * they can be added to generated matchday routes.
     *
     * @param mixed $raw Raw ID or ID:alias value from Joomla round records.
     * @return array{id:int,slug:string}|null Canonical positive ID and slug, or null.
     */
    private static function roundIdAndSlug(mixed $raw): ?array
    {
        if (!is_int($raw) && !is_string($raw)) {
            return null;
        }

        $value = (string) $raw;

        if (!preg_match('/^([0-9]+)(?::([\pL\pN._-]+))?$/Du', $value, $matches)) {
            return null;
        }

        $digits = ltrim($matches[1], '0');
        if ($digits === '') {
            return null;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            return null;
        }

        return [
            'id' => $id,
            'slug' => $id . (isset($matches[2]) ? ':' . $matches[2] : ''),
        ];
    }

    /**
     * Parse a positive project identifier without PHP's loose integer casts.
     *
     * Accepts project IDs stored as integers or zero-padded decimal strings,
     * including older import records. Rejects arrays, objects, booleans,
     * decimal fractions, negative numbers and values exceeding PHP_INT_MAX
     * before querying rounds for a potentially different project.
     *
     * @param mixed $raw Raw project ID from an imported project record.
     * @return int Valid positive ID or zero when invalid.
     */
    private static function positiveProjectId(mixed $raw): int
    {
        if (is_int($raw)) {
            return $raw > 0 ? $raw : 0;
        }

        if (!is_string($raw) || $raw === '' || strlen($raw) > 128
            || preg_match('/^[0-9]+$/D', $raw) !== 1) {
            return 0;
        }

        $digits = ltrim($raw, '0');

        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Validate the legacy SportsManagement database selection.
     *
     * Accepts the two supported database identifiers, either as integers or
     * as zero-padded decimal strings preserved by older module settings.
     * Rejects booleans, arrays, negative numbers, other values and partial
     * numeric casts so invalid input cannot silently change databases.
     *
     * @param mixed $value Database identifier from a legacy view or module.
     * @return int|null Validated Joomla (0) or external (1) selector, or null.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if ($value === 0 || $value === 1) {
            return $value;
        }

        if (!is_string($value) || $value === '' || strlen($value) > 64
            || preg_match('/^0*[01]$/D', $value) !== 1) {
            return null;
        }

        return str_ends_with($value, '1') ? 1 : 0;
    }

    /**
     * Create a native results model scoped to the requested project.
     *
     * Loads the model dependencies when Joomla's legacy class loader has
     * not registered them, then applies the selected database and project ID
     * before the caller requests round records.
     *
     * @param object $project Project record supplying the numeric ID.
     * @param int $databaseSelector SportsManagement database configuration.
     * @return ResultsDataModel Configured Joomla 5/6 results data model.
     */
    private static function createResultsDataModel($project, int $databaseSelector): ResultsDataModel
    {
        if (!class_exists(ResultsDataModel::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php';
        }

        $model = new ResultsDataModel();
        $model->setDatabaseSelector($databaseSelector);
        $model->setProjectId(self::positiveProjectId($project->id ?? null));
        return $model;
    }

    /**
     * Assemble the query parameters shared by all matchday navigation links.
     *
     * Keeps the active project, season, database choice and requested view
     * while copying validated task, division-depth, prediction and menu
     * selectors from Joomla input. Validates the project slug against the
     * project ID and falls back to the canonical numeric ID for corrupt
     * imported slugs. These parameters are passed to SEF routing.
     *
     * @param object $project Current project record with slug and season.
     * @param int $databaseSelector Requested Joomla or external database.
     * @param int $seasonId Explicit season ID or zero to use the project season.
     * @return array<string,int|string> Query parameters for roundUrl().
     */
    private static function buildBaseParams($project, int $databaseSelector, int $seasonId): array
    {
        $input = self::siteApplication()->getInput();
        $projectId = self::positiveProjectId($project->id ?? null);
        $slug = self::roundIdAndSlug($project->slug ?? null);
        $projectSlug = $slug !== null && $slug['id'] === $projectId
            ? $slug['slug']
            : (string) $projectId;

        $params = [
            'option' => $input->getCmd('option', 'com_sportsmanagement'),
            'cfg_which_database' => $databaseSelector === 1 ? 1 : 0,
            's' => $seasonId > 0 ? $seasonId : (int) ($project->season_id ?? 0),
            'p' => $projectSlug,
        ];

        foreach (['view', 'layout', 'controller', 'task'] as $name) {
            $value = $input->getCmd($name, '');
            if ($value !== '') {
                $params[$name] = $value;
            }
        }

        $divisionLevel = $input->getInt('divLevel', 0);
        if ($divisionLevel > 0) {
            $params['divLevel'] = $divisionLevel;
        }

        $predictionId = $input->getInt('prediction_id', 0);
        if ($predictionId > 0) {
            $params['prediction_id'] = $predictionId;
        }

        $itemId = $input->getInt('Itemid', 0);
        if ($itemId > 0) {
            $params['Itemid'] = $itemId;
        }

        return $params;
    }

    /**
     * Generate a Joomla-routed URL for a particular project matchday.
     *
     * Adds the canonical round slug and division to the preserved context,
     * supplies legacy mode and order defaults, and appends the requested
     * page anchor after Joomla's routing step.
     *
     * @param array<string,mixed> $params Existing project and display context.
     * @param string $roundSlug Validated round ID with optional alias.
     * @param int $division Selected project division.
     * @param string $anchor Optional in-page anchor such as the view top.
     * @return string Routed matchday URL with its optional anchor.
     */
    private static function roundUrl(array $params, string $roundSlug, int $division, string $anchor): string
    {
        $params['r'] = $roundSlug;
        $params['division'] = $division;
        $params['mode'] = $params['mode'] ?? 0;
        $params['order'] = $params['order'] ?? 0;

        return Route::_('index.php?' . Uri::buildQuery($params)) . $anchor;
    }
}
