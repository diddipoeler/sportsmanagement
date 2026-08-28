<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchEventPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatchResultHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Temporary global-call surface for the historical team-plan layouts.
 *
 * Every implementation delegates to Joomla 5/6-safe helpers or database APIs;
 * no historical site/helpers/html.php code is loaded.
 */
final class TeamplanPresentationFacade
{
    public static ?object $project = null;
    public static array $teams = [];
    public static int $databaseSelector = 0;
    public static int $seasonId = 0;

    /** @var array<int, object|null> */
    private static array $playgroundCache = [];

    public static function showEventsContainerInResults(
        $matchInfo = [],
        $projectevents = [],
        $matchevents = [],
        $substitutions = null,
        $config = [],
        $project = []
    ): string {
        if (!is_object($matchInfo)) {
            return '';
        }

        return MatchEventPresentationHelper::render(
            $matchInfo,
            (array) $projectevents,
            (array) $matchevents,
            (array) $substitutions,
            (array) $config
        );
    }

    public static function getBootstrapModalImage(
        $target = '',
        $picture = '',
        $text = '',
        $pictureheight = '20',
        $url = '',
        $width = '100',
        $height = '200',
        $useJqueryModal = 0,
        $schemaorg = 'itemprop',
        $schemaorgvalue = 'logo'
    ): string {
        return ModalImageHelper::render(
            (string) $target,
            (string) $picture,
            (string) $text,
            max(1, (int) $pictureheight),
            (string) $url,
            (string) $width,
            (string) $height,
            (int) $useJqueryModal,
            (string) $schemaorg,
            (string) $schemaorgvalue
        );
    }

    public static function showMatchTime(&$game, &$config, &$overallconfig, &$project): string
    {
        return MatchTimeHelper::format(
            is_object($game) ? $game : (object) [],
            (array) $config,
            (array) $overallconfig,
            is_object($project) ? $project : null
        );
    }

    public static function getThumbUpDownImg($game, $projectteamId, $attributes = null): string
    {
        if (!is_object($game)) {
            return '';
        }

        return MatchResultHelper::renderOutcomeIcon($game, (int) $projectteamId);
    }

    public static function showDivisonRemark(&$hometeam, &$guestteam, &$config, $divisionId = 0): string
    {
        if (!is_object($hometeam) || !is_object($guestteam)) {
            return '&nbsp;';
        }

        $home = $hometeam;
        $away = $guestteam;
        if (!empty($config['switch_home_guest'])) {
            [$home, $away] = [$away, $home];
        }

        $explicitDivisionId = self::normaliseId($divisionId);
        if ($explicitDivisionId > 0) {
            $division = self::loadDivision($explicitDivisionId);
            if ($division) {
                foreach ([$home, $away] as $team) {
                    $team->division_id = $explicitDivisionId;
                    $team->division_slug = $division->id . ':' . $division->alias;
                    $team->division_name = (string) $division->name;
                    $team->division_shortname = (string) $division->shortname;
                }
            }
        }

        if ((int) ($home->division_id ?? 0) <= 0 || (int) ($away->division_id ?? 0) <= 0) {
            return '&nbsp;';
        }

        $property = 'division_' . (string) ($config['show_division_name'] ?? 'name');
        $spacer = (string) ($config['spacer'] ?? '/');
        $output = self::divisionLabel($home, $property, (array) $config);

        if ((int) $home->division_id !== (int) $away->division_id) {
            $output .= $spacer . self::divisionLabel($away, $property, (array) $config);
        }

        return $output !== '' ? $output : '&nbsp;';
    }

    public static function showMatchPlayground(&$game, $config = []): string
    {
        if (!is_object($game) || (empty($config['show_playground']) && empty($config['show_playground_alert']))) {
            return '';
        }

        $projectTeamId = (int) ($game->projectteam1_id ?? 0);
        $team = self::$teams[$projectTeamId] ?? null;
        $standardPlayground = (int) ($team->standard_playground ?? 0);
        $playgroundId = (int) ($game->playground_id ?? 0);

        if ($playgroundId <= 0) {
            $playgroundId = $standardPlayground;
        }
        if ($playgroundId <= 0 && !empty($team->club_id)) {
            $playgroundId = self::loadClubStandardPlayground((int) $team->club_id);
        }
        if ($playgroundId <= 0) {
            echo '-';
            return '';
        }

        if (empty($config['show_playground']) && !empty($config['show_playground_alert'])
            && $standardPlayground > 0 && $standardPlayground === $playgroundId) {
            echo '-';
            return '';
        }

        $playground = self::loadPlayground($playgroundId);
        if (!$playground) {
            echo '-';
            return '';
        }

        $isAlternative = $standardPlayground > 0 && $standardPlayground !== $playgroundId;
        $name = (($config['show_playground_name'] ?? 'name') === 'name')
            ? (string) ($playground->name ?? '')
            : (string) ($playground->short_name ?? $playground->name ?? '');
        $label = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        if ($isAlternative && (int) ($config['show_playground_alert'] ?? 0) === 1) {
            $label = '<strong class="text-danger">' . $label . '</strong>';
        } elseif ($isAlternative && (int) ($config['show_playground_alert'] ?? 0) === 2) {
            $label = '<strong class="text-danger">'
                . htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEWS'), ENT_QUOTES, 'UTF-8')
                . ':</strong> ' . $label;
        }

        $slug = (string) ($game->playground_slug ?? '');
        if ($slug === '') {
            $slug = (int) $playground->id . ':' . (string) ($playground->alias ?? '');
        }
        $link = SiteRouteHelper::view('playground', [
            'cfg_which_database' => self::$databaseSelector,
            's' => self::$seasonId,
            'p' => (string) ($game->project_slug ?? self::$project->slug ?? ''),
            'pgid' => $slug,
        ]);
        $title = trim(
            (string) ($playground->name ?? '') . ' — '
            . (string) ($playground->address ?? '') . ' '
            . (string) ($playground->zipcode ?? '') . ' '
            . (string) ($playground->city ?? '')
        );

        echo HTMLHelper::link(
            $link,
            $label,
            ['title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8')]
        );

        return '';
    }

    private static function divisionLabel(object $team, string $property, array $config): string
    {
        $label = (string) ($team->{$property} ?? $team->division_name ?? '');
        if ($label === '') {
            return '';
        }
        $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        if (empty($config['show_division_link'])) {
            return $label;
        }

        $project = self::$project;
        $link = SiteRouteHelper::view('ranking', [
            'cfg_which_database' => self::$databaseSelector,
            's' => self::$seasonId,
            'p' => (string) ($project->slug ?? ''),
            'type' => 0,
            'r' => (string) ($project->round_slug ?? ''),
            'from' => 0,
            'to' => 0,
            'division' => (string) ($team->division_slug ?? $team->division_id ?? ''),
        ]);

        return HTMLHelper::link($link, $label);
    }

    private static function loadDivision(int $divisionId): ?object
    {
        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('alias'),
                $db->quoteName('name'),
                $db->quoteName('shortname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('id') . ' = ' . $divisionId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private static function loadClubStandardPlayground(int $clubId): int
    {
        $db = self::database();
        $query = $db->getQuery(true)
            ->select($db->quoteName('standard_playground'))
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->where($db->quoteName('id') . ' = ' . $clubId);
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    private static function loadPlayground(int $playgroundId): ?object
    {
        if (array_key_exists($playgroundId, self::$playgroundCache)) {
            return self::$playgroundCache[$playgroundId];
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground'))
            ->where($db->quoteName('id') . ' = ' . $playgroundId);
        $db->setQuery($query, 0, 1);
        self::$playgroundCache[$playgroundId] = $db->loadObject() ?: null;

        return self::$playgroundCache[$playgroundId];
    }

    private static function normaliseId(mixed $value): int
    {
        if (is_string($value) && str_contains($value, ':')) {
            $value = strstr($value, ':', true);
        }

        return max(0, (int) $value);
    }

    private static function database(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
