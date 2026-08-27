<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Joomla 5/6 matchday navigation used by results-style views.
 */
final class RoundPaginationHelper
{
    public static string $nextlink = '';
    public static string $prevlink = '';

    public static function pagenav($project, $cfgWhichDatabase = 0, $seasonId = 0): string
    {
        if (!$project || (int) ($project->id ?? 0) <= 0) {
            return '';
        }

        if (!class_exists(ResultsDataModel::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php';
        }

        $app = Factory::getApplication();
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $model = new ResultsDataModel();
        $model->setDatabaseSelector((int) $cfgWhichDatabase);
        $rounds = $model->getRounds('ASC', true);

        if (!$rounds) {
            self::$prevlink = '';
            self::$nextlink = '';
            return '';
        }

        $currentRoundId = $input->getInt('r', (int) ($project->current_round ?? 0));
        if ($currentRoundId <= 0) {
            $currentRoundId = (int) ($project->current_round ?? 0);
        }

        $normalised = [];
        foreach ($rounds as $round) {
            $slug = (string) ($round->id ?? '');
            $id = (int) explode(':', $slug, 2)[0];
            if ($id <= 0) {
                continue;
            }
            $normalised[] = (object) [
                'id' => $id,
                'slug' => $slug !== '' ? $slug : (string) $id,
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

        $params = [
            'option' => $option,
            'cfg_which_database' => (int) $cfgWhichDatabase,
            's' => (int) $seasonId,
            'p' => (string) ($project->slug ?? $project->id),
        ];

        foreach (['view', 'layout', 'controller', 'task'] as $name) {
            $value = $input->getCmd($name, '');
            if ($value !== '') {
                $params[$name] = $value;
            }
        }

        $division = $input->getInt('division', 0);
        if ($division > 0) {
            $params['division'] = $division;
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

    public function getnextlink(): string
    {
        return self::$nextlink;
    }

    public function getprevlink(): string
    {
        return self::$prevlink;
    }

    private static function roundUrl(array $params, string $roundSlug, int $division, string $anchor): string
    {
        $params['r'] = $roundSlug;
        $params['division'] = $division;
        $params['mode'] = 0;
        $params['order'] = 0;

        return Route::_('index.php?' . Uri::buildQuery($params)) . $anchor;
    }
}
