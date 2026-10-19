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
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    public static string $nextlink = '';
    public static string $prevlink = '';

    public static function pagenav($project, $cfgWhichDatabase = 0, $seasonId = 0): string
    {
        // Public static navigation values are shared between render calls.
        // An invalid project must never leave the previous project's links.
        self::$prevlink = '';
        self::$nextlink = '';

        if (!$project || (int) ($project->id ?? 0) <= 0) {
            return '';
        }

        $model = self::createResultsDataModel($project, (int) $cfgWhichDatabase);
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
        $params = self::buildBaseParams($project, (int) $cfgWhichDatabase, (int) $seasonId);

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
     * Build the matchday dropdown used by results edit layouts without relying
     * on sportsmanagementModelProject static state.
     */
    public static function selectNavigation(
        $project,
        int $cfgWhichDatabase = 0,
        int $seasonId = 0,
        string $layout = 'form'
    ): string {
        if (!$project || (int) ($project->id ?? 0) <= 0) {
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

    public function getnextlink(): string
    {
        return self::$nextlink;
    }

    public function getprevlink(): string
    {
        return self::$prevlink;
    }

    /**
     * Joomla round selectors can contain ID:alias values, but leading
     * numbers followed by arbitrary text must never turn into valid links.
     *
     * @return array{id:int,slug:string}|null
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

    private static function createResultsDataModel($project, int $databaseSelector): ResultsDataModel
    {
        if (!class_exists(ResultsDataModel::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php';
        }

        $model = new ResultsDataModel();
        $model->setDatabaseSelector($databaseSelector);
        $model->setProjectId((int) ($project->id ?? 0));
        return $model;
    }

    private static function buildBaseParams($project, int $databaseSelector, int $seasonId): array
    {
        $input = self::siteApplication()->getInput();
        $params = [
            'option' => $input->getCmd('option', 'com_sportsmanagement'),
            'cfg_which_database' => $databaseSelector === 1 ? 1 : 0,
            's' => $seasonId > 0 ? $seasonId : (int) ($project->season_id ?? 0),
            'p' => (string) ($project->slug ?? $project->id),
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

    private static function roundUrl(array $params, string $roundSlug, int $division, string $anchor): string
    {
        $params['r'] = $roundSlug;
        $params['division'] = $division;
        $params['mode'] = $params['mode'] ?? 0;
        $params['order'] = $params['order'] ?? 0;

        return Route::_('index.php?' . Uri::buildQuery($params)) . $anchor;
    }
}
