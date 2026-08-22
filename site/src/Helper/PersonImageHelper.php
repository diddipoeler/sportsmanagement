<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

/**
 * Person image defaults used by native site views.
 */
final class PersonImageHelper
{
    public static function placeholder(): string
    {
        return (string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_player', '');
    }
}
