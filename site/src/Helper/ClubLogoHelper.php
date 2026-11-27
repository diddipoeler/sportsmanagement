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
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Render compact club logos or country flags for Joomla 5/6 site layouts.
 */
final class ClubLogoHelper
{
    /**
     * Render a small club logo or country flag in a frontend listing.
     *
     * Uses Joomla's flag presentation for country mode, selects the
     * configured placeholder when a club logo is missing and generates
     * compact image markup via Joomla HTMLHelper in logo mode.
     * Ignores malformed placeholder parameter values and avoids invalid
     * HTML height attributes in Joomla 5/6 templates. Refuses unsafe
     * local paths and unsupported remote URL schemes before rendering HTML.
     * Examines raw logo and placeholder text before trimming control bytes.
     * Uses the configured fallback when a nonempty logo fails URL validation.
     *
     * @param string $logoSmall Optional club logo path.
     * @param string $country Country code used when rendering a flag.
     * @param int $type Display mode: 1 for club logo, 2 for country flag.
     * @param bool $withSpace Add a small padding style around the logo.
     * @return string Image or flag HTML, or empty text when unavailable.
     */
    public static function render(
        string $logoSmall = '',
        string $country = '',
        int $type = 1,
        bool $withSpace = false
    ): string {
        if ($type === 2 && trim($country) !== '') {
            return CountryPresentationHelper::flag($country);
        }

        if ($type !== 1) {
            return '';
        }

        // Do not let trim() erase malformed control bytes in either a club
        // record or its component-level placeholder image setting.
        if (preg_match('/[\\x00-\\x1F\\x7F]/', $logoSmall) === 1) {
            return '';
        }

        $logo = trim($logoSmall);
        if ($logo === '' || PersonImageHelper::url($logo) === '') {
            $fallback = ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_small', '');
            $logo = is_string($fallback)
                && preg_match('/[\\x00-\\x1F\\x7F]/', $fallback) !== 1
                    ? trim($fallback)
                    : '';
        }

        // The compact logo path must be subject to the same URL safety
        // checks as other native SportsManagement frontend image helpers.
        // Keep the original path for Joomla HTMLHelper's image URL handling.
        if ($logo === '' || PersonImageHelper::url($logo) === '') {
            return '';
        }

        $attributes = [
            'align' => 'top',
            'border' => 0,
            'width' => 21,
            'style' => 'height:auto;',
        ];

        if ($withSpace) {
            $attributes['style'] .= 'padding:1px;';
        }

        return HTMLHelper::image($logo, '', $attributes);
    }
}
