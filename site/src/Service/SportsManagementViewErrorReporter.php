<?php
/**
 * Private Joomla 5/6 frontend MVC model error reporting and safe messages.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Prevent frontend MVC view exceptions from disclosing database diagnostics.
 *
 * Model errors can contain private SQL strings, connection details or paths.
 * These details belong in Joomla's server log, not in public error pages.
 */
final class SportsManagementViewErrorReporter
{
    /**
     * Reject a Joomla model error collection with a generic public exception.
     *
     * Logs each detailed error in the component category before throwing
     * Joomla's translated generic server error. Scalars and exceptions are
     * and Traversable error lists are supported. Unexpected array/object
     * values are logged by their type
     * instead of triggering an array-to-string or object conversion warning.
     *
     * @param mixed $errors Model error collection or falsy empty result.
     * @param string $view Short view name for server-side log diagnostics.
     * @return void
     * @throws \RuntimeException When at least one model error exists.
     */
    public static function assertNoErrors(mixed $errors, string $view): void
    {
        if (!$errors) {
            return;
        }

        // A Throwable is one model error, not an array of its private
        // properties. Iterators should be read as their yielded errors.
        $items = $errors instanceof \Throwable
            ? [$errors]
            : ($errors instanceof \Traversable ? $errors : (array) $errors);

        foreach ($items as $error) {
            if ($error instanceof \Throwable) {
                $detail = $error->getMessage();
            } elseif (is_scalar($error)) {
                $detail = (string) $error;
            } else {
                $detail = get_debug_type($error);
            }

            Log::add(
                'SportsManagement ' . $view . ' view model error: ' . $detail,
                Log::ERROR,
                'com_sportsmanagement'
            );
        }

        throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 500);
    }
}
