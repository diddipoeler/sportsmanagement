<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
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

        return self::renderPicture(
            $team,
            $target,
            $picture,
            $previewHeight,
            $modalWidth,
            $modalHeight,
            $mode
        );
    }

    public static function renderVariant(
        object $team,
        string $property,
        string $target,
        int $previewHeight = 20,
        int $modalWidth = 900,
        int $modalHeight = 600,
        int $mode = 0
    ): string {
        $property = in_array($property, ['logo_small', 'logo_middle', 'logo_big'], true)
            ? $property
            : 'logo_small';
        $picture = trim((string) ($team->{$property} ?? ''));

        if ($picture === '' || !self::pictureExists($picture)) {
            $picture = self::placeholder($property);
        }

        if ($picture === '') {
            return '';
        }

        return self::renderPicture(
            $team,
            $target,
            $picture,
            $previewHeight,
            $modalWidth,
            $modalHeight,
            $mode
        );
    }

    private static function renderPicture(
        object $team,
        string $target,
        string $picture,
        int $previewHeight,
        int $modalWidth,
        int $modalHeight,
        int $mode
    ): string {
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

    private static function placeholder(string $property): string
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');

        return trim((string) match ($property) {
            'logo_big' => $params->get('ph_logo_big', ''),
            'logo_middle' => $params->get('ph_logo_medium', ''),
            default => $params->get('ph_logo_small', ''),
        });
    }

    private static function pictureExists(string $picture): bool
    {
        if ($picture === '' || preg_match('#^https?://#i', $picture)) {
            return $picture !== '';
        }

        $path = JPATH_SITE . '/' . ltrim(str_replace('\\', '/', $picture), '/');

        return is_file($path);
    }
}
