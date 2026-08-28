<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Render compact club logos or country flags for Joomla 5/6 site layouts.
 */
final class ClubLogoHelper
{
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
