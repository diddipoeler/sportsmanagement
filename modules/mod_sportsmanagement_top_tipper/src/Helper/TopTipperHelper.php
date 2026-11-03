<?php
/**
 * Native Joomla 5/6 data helper for the SportsManagement Top Tipper module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTopTipper\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrankingModel;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

final class TopTipperHelper
{
    /**
     * Prepare the prediction league ranking data used by the Top Tipper module.
     *
     * Resolves the configured game and database, reads the active project and
     * round filters, asks the component model for ranking results, and applies
     * the module's visibility and result-limit settings without database writes.
     * Invalid settings yield a complete empty template context.
     *
     * @param Registry $params Top Tipper module settings.
     * @param object $module Joomla module instance.
     * @param CMSApplicationInterface $app Active site application.
     * @return array<string, mixed> Ranking data, navigation and template options.
     */
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $predictionGameId = $this->extractId($params->get('pg', 0));
        $databaseSelector = self::databaseSelector($params->get('cfg_which_database', 0));

        // Invalid module settings must not select a different database.
        if ($predictionGameId <= 0 || $databaseSelector === null) {
            return $this->emptyData();
        }

        $input = $app->getInput();
        $requestedProject = $this->extractId($input->get('pj', 0, 'raw'));
        $requestedRound = $this->extractId($input->get('r', 0, 'raw'));
        $requestedFrom = $this->extractId($input->get('from', 0, 'raw'));
        $requestedTo = $this->extractId($input->get('to', 0, 'raw'));
        $rankingType = self::rankingType($input->get('type', 0, 'raw'));

        $discoveryModel = $this->createRankingModel($app, [
            'prediction_id' => $predictionGameId,
            'cfg_which_database' => $databaseSelector,
            'pj' => $requestedProject,
            'r' => $requestedRound,
        ], $databaseSelector);

        if (!$discoveryModel) {
            return $this->emptyData();
        }

        $game = $discoveryModel->getPredictionGame();
        $projects = $discoveryModel->getPredictionProjects();
        $projectId = $discoveryModel->getProjectId();

        if (!$game || $projectId <= 0) {
            return $this->emptyData();
        }

        $currentRound = $discoveryModel->getProjectCurrentRoundId($projectId);
        $roundOptions = $discoveryModel->getRoundOptions();
        $roundOptions = is_array($roundOptions) ? array_values(array_filter($roundOptions, 'is_object')) : [];
        $roundIds = array_values(array_filter(array_map(
            fn (object $option): int => $this->extractId($option->value ?? 0),
            $roundOptions
        )));
        $firstRound = $roundIds[0] ?? $currentRound;
        $lastRound = $roundIds ? (int) end($roundIds) : $currentRound;

        if (self::enabledSwitch($params->get('show_tip_ranking_round', 0))) {
            $roundId = $currentRound;
            $fromRound = $currentRound;
            $toRound = $currentRound;
        } else {
            $roundId = $requestedRound > 0 ? $requestedRound : $currentRound;
            $fromRound = $requestedFrom > 0 ? $requestedFrom : $firstRound;
            $toRound = $requestedTo > 0 ? $requestedTo : ($currentRound > 0 ? $currentRound : $lastRound);
        }

        $rankingModel = $this->createRankingModel($app, [
            'prediction_id' => $predictionGameId,
            'cfg_which_database' => $databaseSelector,
            'pj' => $projectId,
            'r' => $roundId,
            'from' => $fromRound,
            'to' => $toRound,
            'type' => $rankingType,
            'page' => 1,
        ], $databaseSelector);

        if (!$rankingModel) {
            return $this->emptyData();
        }

        $templateConfig = $rankingModel->getPredictionTemplateConfig('predictionoverall');
        $templateConfig = is_array($templateConfig) ? $templateConfig : [];
        $config = array_merge($templateConfig, $this->moduleConfig($params));
        $avatarConfig = $rankingModel->getPredictionTemplateConfig('predictionusers');
        $avatarConfig = is_array($avatarConfig) ? $avatarConfig : [];

        // The module applies its own small result limit. Ask the component model
        // for the complete computed ranking, then filter/slice without any writes.
        $rankingConfig = $config;
        $rankingConfig['show_all_user'] = 1;
        $rankingData = $rankingModel->getRankingData($rankingConfig, $avatarConfig);
        $rankingData = is_array($rankingData) ? $rankingData : [];
        $rows = is_array($rankingData['allRows'] ?? null) ? $rankingData['allRows'] : [];
        // Ranking templates expect associative row records, never arbitrary helper values.
        $rows = array_filter($rows, 'is_array');
        $currentMember = $rankingModel->getPredictionMember();
        $currentMemberId = is_object($currentMember)
            ? $this->extractId($currentMember->id ?? $currentMember->pmID ?? 0)
            : 0;

        if (empty($config['show_all_user'])) {
            $rows = array_filter(
                $rows,
                fn (mixed $row, int|string $key): bool => (
                    (is_int($row['predictionsCount'] ?? null)
                        && $row['predictionsCount'] > 0)
                    || (is_string($row['predictionsCount'] ?? null)
                        && ctype_digit($row['predictionsCount'])
                        && $row['predictionsCount'] !== '0'
                        && trim($row['predictionsCount'], '0') !== '')
                    || ($currentMemberId > 0 && $this->extractId($key) === $currentMemberId)
                ),
                ARRAY_FILTER_USE_BOTH
            );
        }

        $limit = self::boundedLimit($config['limit'] ?? 5);
        $rows = array_slice($rows, 0, $limit, true);
        $rankingProject = $rankingModel->getRankingProject();

        return [
            'predictionGame' => $game,
            'predictionProjects' => $projects,
            'predictionProject' => $rankingProject,
            'projectId' => $projectId,
            'roundId' => $roundId,
            'fromRound' => $fromRound,
            'toRound' => $toRound,
            'roundOptions' => $roundOptions,
            'rankingType' => $rankingType,
            'rankingRows' => $rows,
            'currentMemberId' => $currentMemberId,
            'topTipperConfig' => $config,
            'databaseSelector' => $databaseSelector,
            'rankingUrl' => $this->route('predictionranking', [
                'prediction_id' => $predictionGameId,
                'cfg_which_database' => $databaseSelector,
                'pj' => $projectId,
                'r' => $roundId,
                'from' => $fromRound,
                'to' => $toRound,
                'type' => $rankingType,
            ]),
            'resultsUrl' => $this->route('results', [
                'cfg_which_database' => $databaseSelector,
                'p' => $projectId,
                'r' => $roundId,
            ]),
            'typeOptions' => [
                (object) ['value' => 0, 'text' => Text::_('COM_SPORTSMANAGEMENT_RANKING_FULL_RANKING')],
                (object) ['value' => 1, 'text' => Text::_('COM_SPORTSMANAGEMENT_RANKING_FIRST_HALF_RANKING')],
                (object) ['value' => 2, 'text' => Text::_('COM_SPORTSMANAGEMENT_RANKING_SECOND_HALF_RANKING')],
            ],
        ];
    }

    /**
     * Resolve a local member avatar to a safe public Joomla media URL.
     *
     * Validates the supplied relative filename before using the filesystem;
     * checks that the resolved file stays within the Joomla installation,
     * and only returns a URL for an existing regular image file.
     *
     * @param mixed $avatarPath Untrusted avatar filename from ranking data.
     * @return string Public image URL, or empty string when unsafe or missing.
     */
    public static function avatarUrl(mixed $avatarPath): string
    {
        $path = self::safeAvatarPath($avatarPath);

        if ($path === '' || !defined('JPATH_ROOT')) {
            return '';
        }

        $root = realpath(JPATH_ROOT);
        $file = realpath(JPATH_ROOT . '/' . $path);

        if ($root === false || $file === false
            || !str_starts_with($file, rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
            || !is_file($file)) {
            return '';
        }

        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));

        return rtrim((string) Uri::root(), '/') . '/' . $encodedPath;
    }

    /**
     * Validate a relative avatar image filename without filesystem access.
     *
     * Rejects non-string values, absolute paths, traversal segments, encoded
     * path tricks, URL parameters, backslashes and control characters. Allows
     * regular image formats in nested Joomla media folders, including Unicode
     * and spaces in legacy filenames.
     *
     * @param mixed $value Untrusted avatar path.
     * @return string Safe relative image path, or empty string for invalid data.
     */
    private static function safeAvatarPath(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $path = trim($value);

        if ($path === '' || strlen($path) > 1024
            || str_starts_with($path, '/')
            || str_contains($path, '\\')
            || str_contains($path, ':')
            || str_contains($path, '%')
            || strpbrk($path, '?#') !== false
            || preg_match('/[\\x00-\\x1F\\x7F]/', $path) === 1
            || preg_match('/\\.(?:png|jpe?g|gif|webp)$/iD', $path) !== 1) {
            return '';
        }

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return '';
            }
        }

        return $path;
    }

    /**
     * Build a route to the prediction members page for one league member.
     *
     * @param int $predictionGameId Prediction game identifier.
     * @param int $memberId Prediction member identifier.
     * @param int $databaseSelector Database configuration index.
     * @return string Joomla-routed member URL.
     */
    public function memberUrl(int $predictionGameId, int $memberId, int $databaseSelector = 0): string
    {
        return $this->route('predictionusers', [
            'prediction_id' => $predictionGameId,
            'cfg_which_database' => $databaseSelector,
            'uid' => $memberId,
        ]);
    }

    /**
     * Build a route to a member's predictions for a project and round.
     *
     * @param int $predictionGameId Prediction game identifier.
     * @param int $memberId Prediction member identifier.
     * @param int $projectId Project identifier.
     * @param int $roundId Project round identifier.
     * @param int $databaseSelector Database configuration index.
     * @return string Joomla-routed prediction results URL.
     */
    public function detailsUrl(
        int $predictionGameId,
        int $memberId,
        int $projectId,
        int $roundId,
        int $databaseSelector = 0
    ): string {
        return $this->route('predictionresults', [
            'prediction_id' => $predictionGameId,
            'cfg_which_database' => $databaseSelector,
            'uid' => $memberId,
            'pj' => $projectId,
            'r' => $roundId,
        ]);
    }

    /**
     * Instantiate a Joomla prediction ranking model with temporary request data.
     *
     * Replaces relevant request parameters only while the MVC factory builds
     * the model and restores every original value in a finally block, including
     * when model creation throws. Returns null for an unexpected model type.
     *
     * @param CMSApplicationInterface $app Active Joomla site application.
     * @param array $context Temporary model input values.
     * @param int $databaseSelector Database selector forwarded to the model.
     * @return PredictionrankingModel|null Resolved model or null.
     */
    private function createRankingModel(
        CMSApplicationInterface $app,
        array $context,
        int $databaseSelector
    ): ?PredictionrankingModel {
        $input = $app->getInput();
        $original = [];

        foreach ($context as $key => $value) {
            $original[$key] = $input->get($key, null, 'raw');
            $input->set($key, $value);
        }

        try {
            $component = $app->bootComponent('com_sportsmanagement');
            $model = $component->getMVCFactory()->createModel(
                'Predictionranking',
                'Site',
                ['ignore_request' => true]
            );
        } finally {
            foreach ($original as $key => $value) {
                $input->set($key, $value);
            }
        }

        if (!$model instanceof PredictionrankingModel) {
            return null;
        }

        $model->setDatabaseSelector($databaseSelector);

        return $model;
    }

    /**
     * Convert allowed Top Tipper display settings into ranking model options.
     *
     * Maps the known boolean-style switches to strict 0/1 integer options,
     * rejecting malformed values, and applies a bounded result limit so the
     * ranking view cannot render an unbounded user list.
     *
     * @param Registry $params Module configuration.
     * @return array<string, int> Sanitised ranking options.
     */
    private function moduleConfig(Registry $params): array
    {
        $keys = [
            'show_project_name', 'show_project_name_selector', 'show_rankingnav', 'show_all_user',
            'show_user_icon', 'show_user_link', 'show_tip_details', 'show_tip_ranking',
            'show_tip_ranking_text', 'show_tip_ranking_round', 'show_tip_link_ranking_round',
            'show_average_points', 'show_count_tips', 'show_count_joker', 'show_count_topptips',
            'show_count_difftips', 'show_count_tendtipps', 'show_debug_modus',
        ];
        $config = ['limit' => self::boundedLimit($params->get('limit', 5))];

        foreach ($keys as $key) {
            $raw = $params->get($key, 0);
            // Module switches are boolean flags; do not coerce malformed arrays
            // or partial numeric strings into enabled options under PHP 8.
            $config[$key] = self::enabledSwitch($raw) ? 1 : 0;
        }

        return $config;
    }

    /**
     * Interpret a Joomla module option as an enabled 0/1 switch.
     *
     * Only the literal integer 1 or string "1" enables the option.
     * Rejecting booleans, arrays and partially numeric strings avoids PHP 8
     * coercion and keeps the round-selection branch consistent with the
     * configuration passed to the prediction ranking model.
     *
     * @param mixed $value Untrusted module option.
     * @return bool True when this switch is explicitly enabled.
     */
    private static function enabledSwitch(mixed $value): bool
    {
        return $value === 1 || $value === '1';
    }

    /**
     * Validate the requested Top Tipper ranking period.
     *
     * The component understands exactly three ranking modes: full season,
     * first half and second half. Invalid types, partially numeric strings
     * and out-of-range values fall back to full-season ranking rather than
     * relying on Joomla integer casts.
     *
     * @param mixed $value Raw ranking type request parameter.
     * @return int Valid ranking mode 0, 1 or 2.
     */
    private static function rankingType(mixed $value): int
    {
        if ($value === 0 || $value === 1 || $value === 2) {
            return $value;
        }

        if (is_string($value) && preg_match('/^0*[012]$/D', $value) === 1) {
            return (int) $value;
        }

        return 0;
    }

    /**
     * Parse the selected prediction database as a non-negative integer.
     *
     * Accepts decimal strings with leading zeroes, but rejects malformed
     * values and integer overflows instead of selecting another database.
     *
     * @param mixed $value Raw database selector from module configuration.
     * @return int|null Database ID or null for invalid input.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || !preg_match('/^[0-9]+$/D', $value)) {
            return null;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        $maximum = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($maximum)
            || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)) {
            return null;
        }

        return (int) $digits;
    }

    /**
     * Bound the number of users rendered in the Top Tipper ranking.
     *
     * Accepts integer settings and decimal strings, including zero-padded
     * values. Malformed settings default to five and valid limits are clamped
     * between one and one hundred to avoid unbounded result rendering.
     *
     * @param mixed $value Raw configured ranking limit.
     * @return int Safe limit between 1 and 100.
     */
    private static function boundedLimit(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 5;
        }

        // Legacy module parameters can contain leading zeroes.
        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? 5 : min(100, max(1, $number));
    }

    /**
     * Produce a Joomla-routed URL for a SportsManagement prediction view.
     *
     * Delegates view-specific SEF URL construction to the component route
     * helper rather than manually assembling query strings.
     *
     * @param string $view Target component view name.
     * @param array<string, mixed> $parameters View query parameters.
     * @return string URL for navigation to the requested view.
     */
    private function route(string $view, array $parameters): string
    {
        return SiteRouteHelper::view($view, $parameters);
    }

    /**
     * Extract the first valid positive numeric ID from request or module data.
     *
     * Accepts Joomla ID:alias identifiers and array-valued module selectors,
     * but rejects partial numbers, unsupported types and integer overflows.
     *
     * @param mixed $value Raw value or list of configured identifiers.
     * @return int Positive identifier, or zero for invalid input.
     */
    private function extractId(mixed $value): int
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $text = trim((string) $value);

        // Accept Joomla's ID:alias format, but never interpret a malformed
        // prefix ("12garbage", floats, booleans or overflowing values) as an ID.
        if (!preg_match('/^([0-9]+)(?::[A-Za-z0-9_-]+)?$/D', $text, $match)) {
            return 0;
        }

        $digits = ltrim($match[1], '0');

        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    /**
     * Create a consistent empty prediction-ranking template context.
     *
     * Supplies the same layout keys as a successful ranking result with
     * empty collections, safe IDs and blank URLs when no valid game or
     * project is available, so templates can render without type warnings.
     *
     * @return array<string, mixed> Empty ranking data and navigation values.
     */
    private function emptyData(): array
    {
        return [
            'predictionGame' => null,
            'predictionProjects' => [],
            'predictionProject' => null,
            'projectId' => 0,
            'roundId' => 0,
            'fromRound' => 0,
            'toRound' => 0,
            'roundOptions' => [],
            'rankingType' => 0,
            'rankingRows' => [],
            'currentMemberId' => 0,
            'topTipperConfig' => [],
            'databaseSelector' => 0,
            'rankingUrl' => '',
            'resultsUrl' => '',
            'typeOptions' => [],
        ];
    }
}
