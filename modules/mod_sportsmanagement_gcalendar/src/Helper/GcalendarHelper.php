<?php
namespace Diddipoeler\Module\SportsManagementGcalendar\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class GcalendarHelper
{
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $calendars = $this->getCalendars($params, $app);
        $calendarIds = array_values(array_filter(array_map(
            static fn (object $calendar): int => (int) ($calendar->id ?? 0),
            $calendars
        )));

        $date = new Date('now');
        $dayNames = [];
        $dayNamesShort = [];
        $dayNamesMin = [];
        $monthNames = [];
        $monthNamesShort = [];

        for ($day = 0; $day < 7; $day++) {
            $long = $date->dayToString($day, false);
            $short = $date->dayToString($day, true);
            $dayNames[] = $long;
            $dayNamesShort[] = $short;
            $dayNamesMin[] = mb_substr($short, 0, 2);
        }

        for ($month = 1; $month <= 12; $month++) {
            $monthNames[] = $date->monthToString($month, false);
            $monthNamesShort[] = $date->monthToString($month, true);
        }

        $color = strtoupper(ltrim((string) $params->get('event_color', '135CAE'), '#'));

        if (!preg_match('/^[0-9A-F]{6}$/', $color)) {
            $color = '135CAE';
        }

        $theme = trim((string) $params->get(
            'theme',
            ComponentHelper::getParams('com_sportsmanagement')->get('theme', '')
        ));
        $theme = preg_replace('/[^A-Za-z0-9_-]/', '', $theme) ?: '';

        $compact = (int) $params->get('compact_events', 1);
        $feedUrl = Route::_(
            'index.php?option=com_sportsmanagement&view=jsonfeed&compact=' . $compact
                . '&format=raw&gcids=' . implode(',', $calendarIds),
            false
        );

        $options = [
            'events' => $feedUrl,
            'header' => [
                'left' => 'prev,next ',
                'center' => 'title',
                'right' => '',
            ],
            'defaultView' => 'month',
            'editable' => false,
            'theme' => false,
            'titleFormat' => [
                'month' => $this->convertPhpDateFormat((string) $params->get('titleformat_month', 'M Y')),
            ],
            'firstDay' => max(0, min(6, (int) $params->get('weekstart', 0))),
            'monthNames' => $monthNames,
            'monthNamesShort' => $monthNamesShort,
            'dayNames' => $dayNames,
            'dayNamesShort' => $dayNamesShort,
            'timeFormat' => [
                'month' => $this->convertPhpDateFormat((string) $params->get('timeformat_month', 'g:i a')),
            ],
            'columnFormat' => [
                'month' => 'ddd',
                'week' => 'ddd d',
                'day' => 'dddd d',
            ],
        ];

        $height = (int) $params->get('calendar_height', 0);

        if ($height > 0) {
            $options['contentHeight'] = $height;
        }

        $options['theme'] = $this->registerAssets($theme, (int) $module->id, $color, $app);
        $this->registerCalendarScript((int) $module->id, $options, $app);

        return [
            'calendars' => $calendars,
            'calendarIds' => $calendarIds,
            'dayNamesMin' => $dayNamesMin,
        ];
    }

    private function getCalendars(Registry $params, CMSApplicationInterface $app): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_gcalendar'));

        $calendarIds = $params->get('calendarids', []);

        if (!is_array($calendarIds)) {
            $calendarIds = preg_split('/\s*,\s*/', (string) $calendarIds, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $calendarIds = array_values(array_unique(array_filter(array_map('intval', $calendarIds))));

        if ($calendarIds) {
            $query->where($db->quoteName('id') . ' IN (' . implode(',', $calendarIds) . ')');
        }

        $user = $app->getIdentity();

        if ($user && !$user->authorise('core.admin', 'com_sportsmanagement')) {
            $levels = array_values(array_unique(array_filter(array_map(
                'intval',
                $user->getAuthorisedViewLevels()
            ))));

            if ($levels) {
                $query->where($db->quoteName('access') . ' IN (' . implode(',', $levels) . ')');
            } else {
                $query->where('1 = 0');
            }
        }

        $query->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    private function registerAssets(
        string $theme,
        int $moduleId,
        string $color,
        CMSApplicationInterface $app
    ): bool {
        $document = $app->getDocument();
        $wa = $document->getWebAssetManager();
        $root = rtrim(Uri::root(), '/') . '/';
        $componentLibraries = $root . 'components/com_sportsmanagement/libraries/';
        $moduleRoot = $root . 'modules/mod_sportsmanagement_gcalendar/';

        $wa->registerAndUseStyle(
            'mod_sportsmanagement_gcalendar.fullcalendar',
            $componentLibraries . 'fullcalendar/fullcalendar.css'
        );
        $wa->registerAndUseStyle(
            'mod_sportsmanagement_gcalendar.module',
            $moduleRoot . 'tmpl/gcalendar.css'
        );

        $scriptDependencies = ['jquery'];
        $themeEnabled = false;
        $themeCss = JPATH_SITE . '/components/com_sportsmanagement/libraries/jquery/themes/'
            . $theme . '/jquery-ui.custom.css';
        $jqueryUi = JPATH_SITE . '/components/com_sportsmanagement/libraries/jquery/ui/jquery-ui.custom.min.js';

        if ($theme !== '' && is_file($themeCss) && is_file($jqueryUi)) {
            $wa->registerAndUseScript(
                'mod_sportsmanagement_gcalendar.jqueryui',
                $componentLibraries . 'jquery/ui/jquery-ui.custom.min.js',
                [],
                [],
                ['jquery']
            );
            $wa->registerAndUseStyle(
                'mod_sportsmanagement_gcalendar.theme.' . strtolower($theme),
                $componentLibraries . 'jquery/themes/' . rawurlencode($theme) . '/jquery-ui.custom.css'
            );
            $scriptDependencies[] = 'mod_sportsmanagement_gcalendar.jqueryui';
            $themeEnabled = true;
        }

        $wa->registerAndUseScript(
            'mod_sportsmanagement_gcalendar.fullcalendar',
            $componentLibraries . 'fullcalendar/fullcalendar.min.js',
            [],
            [],
            $scriptDependencies
        );

        $fadedColor = $this->fadeColor($color);
        $selector = '#gcalendar_module_' . $moduleId;
        $cssClass = '.gcal-module_event_gccal_' . $moduleId;
        $wa->addInlineStyle(
            $cssClass . ',' . $cssClass . ' a,' . $cssClass . ' div {'
                . 'background-color:' . $fadedColor . ' !important;'
                . 'border-color:#' . $color . ';'
                . 'color:' . $fadedColor . ';}'
                . '.fc-header-center{vertical-align:middle !important;}'
                . $selector . ' .fc-state-default span,' . $selector . ' .ui-state-default{padding:0 !important;}'
        );

        return $themeEnabled;
    }

    private function registerCalendarScript(int $moduleId, array $options, CMSApplicationInterface $app): void
    {
        $optionsJson = json_encode(
            $options,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );

        if ($optionsJson === false) {
            return;
        }

        $calendarSelector = '#gcalendar_module_' . $moduleId;
        $loadingSelector = $calendarSelector . '_loading';
        $script = <<<JS
(() => {
    const initialise = () => {
        const jq = window.jQuery;

        if (!jq || !jq.fn || typeof jq.fn.fullCalendar !== 'function') {
            return;
        }

        const calendar = jq('{$calendarSelector}');

        if (!calendar.length) {
            return;
        }

        const options = {$optionsJson};
        options.eventRender = (event, element) => {
            if (event.description) {
                const text = jq('<div>').html(String(event.description)).text();
                element.attr('title', text);
            }
        };
        options.loading = (loading) => {
            jq('{$loadingSelector}').toggle(Boolean(loading));
        };

        calendar.fullCalendar(options);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
JS;

        $app->getDocument()->getWebAssetManager()->addInlineScript(
            $script,
            ['position' => 'after'],
            [],
            ['mod_sportsmanagement_gcalendar.fullcalendar']
        );
    }

    private function fadeColor(string $color): string
    {
        $red = hexdec(substr($color, 0, 2));
        $green = hexdec(substr($color, 2, 2));
        $blue = hexdec(substr($color, 4, 2));

        $red = (int) round(($red + 4 * 255) / 5);
        $green = (int) round(($green + 4 * 255) / 5);
        $blue = (int) round(($blue + 4 * 255) / 5);

        return sprintf('#%02X%02X%02X', $red, $green, $blue);
    }

    private function convertPhpDateFormat(string $format): string
    {
        $map = [
            'd' => 'dd', 'D' => 'ddd', 'j' => 'd', 'l' => 'dddd',
            'S' => 'S', 'F' => 'MMMM', 'm' => 'MM', 'M' => 'MMM', 'n' => 'M',
            'o' => 'yyyy', 'Y' => 'yyyy', 'y' => 'yy',
            'a' => 'tt', 'A' => 'TT', 'g' => 'h', 'G' => 'H', 'h' => 'hh', 'H' => 'HH',
            'i' => 'mm', 's' => 'ss', 'c' => 'u',
            'N' => '', 'w' => '', 'z' => '', 'W' => '', 't' => '', 'L' => '',
            'B' => '', 'u' => '', 'e' => '', 'I' => '', 'O' => '', 'P' => '', 'T' => '',
            'Z' => '', 'r' => '', 'U' => '',
        ];

        $result = '';
        $escaped = false;

        foreach (str_split($format) as $character) {
            if ($escaped) {
                $result .= $character;
                $escaped = false;
                continue;
            }

            if ($character === '\\') {
                $escaped = true;
                continue;
            }

            $result .= $map[$character] ?? $character;
        }

        return $result;
    }
}
