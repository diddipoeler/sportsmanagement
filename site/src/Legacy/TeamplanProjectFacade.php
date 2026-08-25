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

    public static function setModel(TeamplanModel $model): void
    {
        self::$model = $model;
    }

    public static function getMatchEvents($matchId, $showComments = 0, $sortDesc = 0, $databaseSelector = 0): array
    {
        return self::model()->getMatchEvents(
            (int) $matchId,
            (bool) $showComments,
            (bool) $sortDesc
        );
    }

    public static function getMatchSubstitutions($matchId, $databaseSelector = 0): array
    {
        return self::model()->getMatchSubstitutions((int) $matchId);
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

            if (!\sportsmanagementHelper::existPicture($picture)) {
                $picture = (string) \sportsmanagementHelper::getDefaultPlaceholder($clubIcon);
                $team->{$clubIcon} = $picture;
            }

            return (string) \sportsmanagementHelperHtml::getBootstrapModalImage(
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

        if ($type === 2 && isset($team->country) && class_exists('JSMCountries')) {
            return (string) \JSMCountries::getCountryFlag($team->country);
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
