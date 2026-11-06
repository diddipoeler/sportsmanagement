<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/** Resolve and render team/club logos without legacy team or project models. */
final class TeamLogoHelper
{
    /**
     * Render the best available team logo with its configured popup style.
     * @param object $team Team or project-team image record.
     * @param string $target Modal element identifier.
     * @param bool $preferSmall Whether to prefer small logo variants.
     * @param int $previewHeight Preview height in pixels.
     * @param int $modalWidth Popup width in pixels.
     * @param int $modalHeight Popup height in pixels.
     * @param int $mode Image popup mode.
     * @return string Logo markup or empty text.
     */
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

    /**
     * Render one configured team image variant with placeholder fallback.
     * @param object $team Team record with picture fields.
     * @param string $property Allowed logo property name.
     * @param string $target Modal element identifier.
     * @param int $previewHeight Preview height in pixels.
     * @param int $modalWidth Popup width in pixels.
     * @param int $modalHeight Popup height in pixels.
     * @param int $mode Image display mode.
     * @return string Logo markup or empty text.
     */
    public static function renderVariant(
        object $team,
        string $property,
        string $target,
        int $previewHeight = 20,
        int $modalWidth = 900,
        int $modalHeight = 600,
        int $mode = 0
    ): string {
        $property = in_array(
            $property,
            ['logo_small', 'logo_middle', 'logo_big', 'projectteam_picture', 'team_picture'],
            true
        ) ? $property : 'logo_small';
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

    /**
     * Render a selected team picture through the shared modal helper.
     * @param object $team Team record used for alt text.
     * @param string $target Popup identifier.
     * @param string $picture Picture URL or site path.
     * @param int $previewHeight Preview height.
     * @param int $modalWidth Popup width.
     * @param int $modalHeight Popup height.
     * @param int $mode Popup rendering mode.
     * @return string Rendered team image markup.
     */
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

    /**
     * Find the first available team picture in preferred priority order.
     * @param object $team Team or project-team record.
     * @param bool $preferSmall Whether smaller logos take precedence.
     * @return string Picture path or an empty string.
     */
    private static function picture(object $team, bool $preferSmall): string
    {
        $candidates = $preferSmall
            ? ['picture', 'projectteam_picture', 'team_picture', 'logo_small', 'logo_middle', 'logo_big']
            : ['picture', 'projectteam_picture', 'team_picture', 'logo_big', 'logo_middle', 'logo_small'];

        foreach ($candidates as $property) {
            $value = trim((string) ($team->{$property} ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * Resolve the configured fallback logo for an image variant.
     * @param string $property Team logo property to render.
     * @return string Configured placeholder image path.
     */
    private static function placeholder(string $property): string
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');

        return trim((string) match ($property) {
            'logo_big' => $params->get('ph_logo_big', ''),
            'logo_middle' => $params->get('ph_logo_medium', ''),
            default => $params->get('ph_logo_small', ''),
        });
    }

    /**
     * Test whether a team picture is local or a remote HTTP(S) URL.
     * @param string $picture Configured image URL or path.
     * @return bool Whether the path exists or uses HTTP(S).
     */
    private static function pictureExists(string $picture): bool
    {
        if ($picture === '' || preg_match('#^https?://#i', $picture)) {
            return $picture !== '';
        }

        $path = JPATH_SITE . '/' . ltrim(str_replace('\\', '/', $picture), '/');

        return is_file($path);
    }
}
