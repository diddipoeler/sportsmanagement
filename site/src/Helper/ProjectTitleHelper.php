<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Build project page titles without loading the legacy sportsmanagementHelper.
 *
 * The formatting intentionally preserves the historical page_title_format
 * semantics used by existing frontend template configuration.
 */
final class ProjectTitleHelper
{
    public static function createInfo(string $prefix): object
    {
        return (object) [
            'prefix' => $prefix,
            'clubName' => null,
            'team1Name' => null,
            'team2Name' => null,
            'roundName' => null,
            'personName' => null,
            'playgroundName' => null,
            'projectName' => null,
            'divisionName' => null,
            'leagueName' => null,
            'seasonName' => null,
        ];
    }

    public static function format(object $titleInfo, int|string $format): string
    {
        $parts = [];

        foreach (['personName', 'playgroundName'] as $property) {
            $value = trim((string) ($titleInfo->{$property} ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $team1 = trim((string) ($titleInfo->team1Name ?? ''));
        $team2 = trim((string) ($titleInfo->team2Name ?? ''));
        if ($team1 !== '') {
            $parts[] = $team2 !== '' ? $team1 . ' - ' . $team2 : $team1;
        }

        foreach (['clubName', 'roundName'] as $property) {
            $value = trim((string) ($titleInfo->{$property} ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $projectDivision = trim((string) ($titleInfo->projectName ?? ''));
        $division = trim((string) ($titleInfo->divisionName ?? ''));
        if ($division !== '') {
            $projectDivision .= ($projectDivision !== '' ? ' - ' : ' - ') . $division;
        }

        $league = trim((string) ($titleInfo->leagueName ?? ''));
        $season = trim((string) ($titleInfo->seasonName ?? ''));

        switch ((int) $format) {
            case 0:
                self::append($parts, $projectDivision);
                break;

            case 1:
                self::append($parts, $projectDivision);
                self::append($parts, $league);
                break;

            case 2:
                self::append($parts, $projectDivision);
                self::append($parts, $league);
                self::append($parts, $season);
                break;

            case 3:
                self::append($parts, $projectDivision);
                self::append($parts, $season);
                break;

            case 4:
                self::append($parts, $league);
                break;

            case 5:
                self::append($parts, $league);
                self::append($parts, $season);
                break;

            case 6:
                self::append($parts, $season);
                break;

            case 7:
                break;
        }

        return (string) ($titleInfo->prefix ?? '') . ': ' . implode(' | ', $parts);
    }

    private static function append(array &$parts, string $value): void
    {
        if ($value !== '') {
            $parts[] = $value;
        }
    }
}
