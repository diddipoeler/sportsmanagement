<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamplanModel;

/**
 * Narrow compatibility facade for historical teamplan templates.
 *
 * The templates still call the former global sportsmanagementModelProject
 * methods for events, substitutions and club icons. This facade keeps that
 * temporary call surface while routing all data access to TeamplanModel.
 */
final class TeamplanProjectFacade
{
    private static ?TeamplanModel $model = null;

    /** @var array<string, array<int, object>> */
    private static array $eventCache = [];

    /** @var array<int, array<int, object>> */
    private static array $substitutionCache = [];

    public static function setModel(TeamplanModel $model): void
    {
        self::$model = $model;
        self::$eventCache = [];
        self::$substitutionCache = [];
    }

    public static function getMatchEvents($matchId, $showComments = 0, $sortDesc = 0, $databaseSelector = 0): array
    {
        $matchId = (int) $matchId;
        if ($matchId <= 0) {
            return [];
        }

        $cacheKey = $matchId . ':' . (int) ((bool) $showComments) . ':' . (int) ((bool) $sortDesc);
        if (!array_key_exists($cacheKey, self::$eventCache)) {
            self::$eventCache[$cacheKey] = self::model()->getMatchEvents(
                $matchId,
                (bool) $showComments,
                (bool) $sortDesc
            );
        }

        return self::$eventCache[$cacheKey];
    }

    public static function getMatchSubstitutions($matchId, $databaseSelector = 0): array
    {
        $matchId = (int) $matchId;
        if ($matchId <= 0) {
            return [];
        }

        if (!array_key_exists($matchId, self::$substitutionCache)) {
            self::$substitutionCache[$matchId] = self::model()->getMatchSubstitutions($matchId);
        }

        return self::$substitutionCache[$matchId];
    }

    public static function getClubIconHtml(
        &$team,
        $type = 1,
        $withSpace = 0,
        $clubIcon = 'logo_big',
        $databaseSelector = 0,
        $roundCode = 0,
        $modalWidth = '100',
        $modalHeight = '200',
        $useJqueryModal = 0
    ): string {
        $type = (int) $type;

        if ($type === 1) {
            $clubIcon = (string) $clubIcon;
            $picture = (string) ($team->{$clubIcon} ?? '');

            if (!TeamplanHelperFacade::existPicture($picture)) {
                $picture = TeamplanHelperFacade::getDefaultPlaceholder($clubIcon);
                $team->{$clubIcon} = $picture;
            }

            return TeamplanHtmlFacade::getBootstrapModalImage(
                (string) $roundCode . 'team' . (int) ($team->team_id ?? 0),
                $picture,
                (string) ($team->name ?? ''),
                '20',
                '',
                (string) $modalWidth,
                (string) $modalHeight,
                (int) $useJqueryModal
            );
        }

        if ($type === 2 && isset($team->country)) {
            return TeamplanCountriesFacade::getCountryFlag($team->country);
        }

        return '';
    }

    private static function model(): TeamplanModel
    {
        if (!self::$model instanceof TeamplanModel) {
            throw new \RuntimeException('Teamplan project facade requires TeamplanModel.', 500);
        }

        return self::$model;
    }
}
