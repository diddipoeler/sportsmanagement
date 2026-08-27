<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Date presentation compatibility used while legacy site templates are moved
 * onto the Joomla 5/6 namespaced frontend.
 */
final class DatePresentationHelper
{
    /**
     * Preserve sportsmanagementHelper::convertDate() output without loading the
     * large legacy administrator helper.
     */
    public static function convert(string $date, int $direction = 1): string
    {
        if ($date === '') {
            return '';
        }

        if (strpos($date, '-') === false) {
            if (strlen($date) === 8) {
                return substr($date, 4, 4) . '-'
                    . substr($date, 2, 2) . '-'
                    . substr($date, 0, 2);
            }

            if (strlen($date) === 6) {
                return substr(date('Y'), 0, 2)
                    . substr($date, 4, 2) . '-'
                    . substr($date, 2, 2) . '-'
                    . substr($date, 0, 2);
            }

            return '';
        }

        if ($direction === 1) {
            return substr($date, 8) . '-'
                . substr($date, 5, 2) . '-'
                . substr($date, 0, 4);
        }

        return substr($date, 6, 4) . '-'
            . substr($date, 3, 2) . '-'
            . substr($date, 0, 2);
    }
}
