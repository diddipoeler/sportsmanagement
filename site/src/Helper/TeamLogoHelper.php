<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

/** Resolve and render team/club logos without legacy team or project models. */
final class TeamLogoHelper
{
    public static function render(
        object $team,
        string $target,
        bool $preferSmall = true,
        int $previewHeight = 20,
        int $modalWidth = 900,
        int $modalHeight = 600,
        int $mode = 0
    ): string {
        $picture = self::picture($team, $preferSmall);

        if ($picture === '') {
            return '';
        }

        $base = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
            ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
            : Uri::root();
        $url = preg_match('#^https?://#i', $picture)
            ? $picture
            : rtrim($base, '/') . '/' . ltrim($picture, '/');

        return ModalImageHelper::render(
            $target,
            $url,
            (string) ($team->name ?? ''),
            $previewHeight,
            '',
            $modalWidth,
            $modalHeight,
            $mode
        );
    }

    private static function picture(object $team, bool $preferSmall): string
    {
        $candidates = $preferSmall
            ? ['picture', 'logo_small', 'logo_middle', 'logo_big']
            : ['picture', 'logo_big', 'logo_middle', 'logo_small'];

        foreach ($candidates as $property) {
            $value = trim((string) ($team->{$property} ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
