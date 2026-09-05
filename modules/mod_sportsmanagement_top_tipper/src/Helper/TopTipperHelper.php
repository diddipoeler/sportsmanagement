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
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $predictionGameId = $this->extractId($params->get('pg', 0));
        $databaseSelector = max(0, (int) $params->get('cfg_which_database', 0));

        if ($predictionGameId <= 0) {
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
        $rows = $rankingData['allRows'] ?? [];
        $currentMember = $rankingModel->getPredictionMember();
        $currentMemberId = (int) ($currentMember->id ?? $currentMember->pmID ?? 0);

        if (empty($config['show_all_user'])) {
            $rows = array_filter(
                $rows,
                static fn (array $row, int|string $key): bool => (int) ($row['predictionsCount'] ?? 0) > 0
                    || (int) $key === $currentMemberId,
                ARRAY_FILTER_USE_BOTH
            );
        }

        $limit = max(1, (int) ($config['limit'] ?? 5));
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

    public function memberUrl(int $predictionGameId, int $memberId, int $databaseSelector = 0): string
    {
        return $this->route('predictionusers', [
            'prediction_id' => $predictionGameId,
            'cfg_which_database' => $databaseSelector,
            'uid' => $memberId,
        ]);
    }

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

    private function moduleConfig(Registry $params): array
    {
        $keys = [
            'show_project_name', 'show_project_name_selector', 'show_rankingnav', 'show_all_user',
            'show_user_icon', 'show_user_link', 'show_tip_details', 'show_tip_ranking',
            'show_tip_ranking_text', 'show_tip_ranking_round', 'show_tip_link_ranking_round',
            'show_average_points', 'show_count_tips', 'show_count_joker', 'show_count_topptips',
            'show_count_difftips', 'show_count_tendtipps', 'show_debug_modus',
        ];
        $config = ['limit' => max(1, (int) $params->get('limit', 5))];

        foreach ($keys as $key) {
            $config[$key] = (int) $params->get($key, 0);
        }

        return $config;
    }

    private function route(string $view, array $parameters): string
    {
        return SiteRouteHelper::view($view, $parameters);
    }

    private function extractId(mixed $value): int
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        $text = trim((string) $value);

        return $text === '' ? 0 : max(0, (int) strtok($text, ':'));
    }

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
