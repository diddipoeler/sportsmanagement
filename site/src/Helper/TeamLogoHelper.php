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

/** Resolve and render team/club logos without legacy team or project models. */
final class TeamLogoHelper
{
    /**
     * Render the best available team logo with its configured popup style.
     *
     * Selects a nonempty picture in the caller's preferred size order, then
     * delegates URL validation and safe modal markup to renderPicture().
     * Malformed legacy properties are skipped without PHP 8 cast warnings.
     *
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
     *
     * Restricts the property name to known image columns, verifies the
     * selected local file or remote URL, and falls back to component settings
     * when the record contains no usable image. Invalid placeholders are
     * discarded instead of being cast into visible markup.
     *
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
        $picture = self::scalarPicture($team->{$property} ?? null);

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
     *
     * Uses the same security checks as the person-image URL resolver for
     * external hosts and relative paths. Rejects unsupported schemes before
     * invoking the modal renderer and sanitizes imported alternative text.
     *
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
        // Apply the same URL scheme and encoded traversal checks as player images.
        $url = PersonImageHelper::url($picture);

        if ($url === '') {
            return '';
        }

        return ModalImageHelper::render(
            $target,
            $url,
            self::scalarPicture($team->name ?? null),
            $previewHeight,
            '',
            $modalWidth,
            $modalHeight,
            $mode
        );
    }

    /**
     * Find the first available team picture in preferred priority order.
     *
     * Checks the legacy team and project-team picture fields in the existing
     * configured order. Returns the first nonempty scalar candidate and
     * ignores malformed, oversized or invalidly encoded imported values.
     *
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
            $value = self::scalarPicture($team->{$property} ?? null);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * Resolve the configured fallback logo for an image variant.
     *
     * Selects the corresponding large, medium or small placeholder from
     * Joomla's component registry. Unexpected registry values cannot turn
     * arrays or objects into broken image URLs.
     *
     * @param string $property Team logo property to render.
     * @return string Configured placeholder image path.
     */
    private static function placeholder(string $property): string
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');

        return self::scalarPicture(match ($property) {
            'logo_big' => $params->get('ph_logo_big', ''),
            'logo_middle' => $params->get('ph_logo_medium', ''),
            default => $params->get('ph_logo_small', ''),
        });
    }

    /**
     * Read one optional picture or display-name field without PHP 8 casts.
     *
     * Imported database records and Joomla registry settings may contain
     * arrays, objects, invalid UTF-8 or unexpectedly large values. Accept
     * only short, printable scalar strings before building image markup.
     *
     * @param mixed $value Raw team image path, placeholder or display name.
     * @return string Trimmed string or empty text for invalid input.
     */
    private static function scalarPicture(mixed $value): string
    {
        if (!is_string($value) && !is_int($value)) {
            return '';
        }

        $text = trim((string) $value);

        return strlen($text) <= 4096
            && preg_match('//u', $text) === 1
            && preg_match('/[\\x00-\\x1F\\x7F]/', $text) !== 1
                ? $text
                : '';
    }

    /**
     * Test whether a team picture refers to a valid local file or safe HTTP(S) URL.
     *
     * Normalizes the URL using the common picture resolver and checks
     * local files using canonical paths under JPATH_SITE. Symlinks outside
     * the document root and unsafe remote URLs are treated as unavailable.
     *
     * @param string $picture Configured image URL or path.
     * Rejects encoded traversal, remote credentials and local paths outside
     * Joomla's document root before considering a picture available.
     *
     * @return bool Whether a safe remote URL or readable local image exists.
     */
    private static function pictureExists(string $picture): bool
    {
        $safeUrl = PersonImageHelper::url($picture);

        if ($safeUrl === '') {
            return false;
        }

        if (preg_match('#^https?://#i', $picture) === 1) {
            return true;
        }

        $root = realpath(JPATH_SITE);
        $path = realpath(JPATH_SITE . '/' . ltrim($picture, '/'));

        return $root !== false && $path !== false
            && str_starts_with($path, $root . DIRECTORY_SEPARATOR)
            && is_file($path);
    }
}
