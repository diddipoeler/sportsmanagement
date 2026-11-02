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
        $rankingType = max(0, min(2, $input->getInt('type', 0)));

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

        if ((int) $params->get('show_tip_ranking_round', 0) === 1) {
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

        $config = array_merge(
            $rankingModel->getPredictionTemplateConfig('predictionoverall'),
            $this->moduleConfig($params)
        );
        $avatarConfig = $rankingModel->getPredictionTemplateConfig('predictionusers');

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
        $currentMemberId = (int) ($currentMember->id ?? $currentMember->pmID ?? 0);

        if (empty($config['show_all_user'])) {
            $rows = array_filter(
                $rows,
                static fn (mixed $row, int|string $key): bool => (is_array($row)
                    && (int) ($row['predictionsCount'] ?? 0) > 0)
                    || ($currentMemberId > 0 && ctype_digit((string) $key)
                        && (int) $key === $currentMemberId),
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
     * Maps the known boolean-style module switches to integers and applies a
     * bounded result limit, preventing an unbounded ranking view.
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
            $config[$key] = (int) $params->get($key, 0);
        }

        return $config;
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
