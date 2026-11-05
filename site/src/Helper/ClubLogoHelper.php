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

        $logo = trim($logoSmall);
        if ($logo === '') {
            $logo = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_small', ''));
        }

        if ($logo === '') {
            return '';
        }

        $attributes = [
            'align' => 'top',
            'border' => 0,
            'width' => 21,
            'height' => 'auto',
        ];

        if ($withSpace) {
            $attributes['style'] = 'padding:1px;';
        }

        return HTMLHelper::image($logo, '', $attributes);
    }
}
