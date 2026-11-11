<?php
/**
 * Joomla 5/6 regression checks for imported administrator zero-date values.
 *
 * The Joomla date factory is stubbed to test the date helper independently
 * from a complete CMS installation.
 *
 * Usage: php .github/tests/joomla56-admin-zero-timestamp.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS {
    final class Factory
    {
        /**
         * Return a fixture implementing Joomla's date timestamp contract.
         *
         * @param int $timestamp Parsed Unix timestamp.
         * @return object Timestamp-bearing fixture.
         */
        public static function getDate(int $timestamp): object
        {
            return new class ($timestamp) {
                /**
                 * Store the exact timestamp provided by the tested helper.
                 *
                 * @param int $timestamp Parsed timestamp.
                 */
                public function __construct(private int $timestamp)
                {
                }

                /**
                 * Expose the fixture's Unix timestamp without adjustments.
                 *
                 * @return int The original Unix timestamp.
                 */
                public function getTimestamp(): int
                {
                    return $this->timestamp;
                }
            };
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/SportsManagementDateHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper::class;

    foreach (['', '0000-00-00', '0000-00-00 00:00:00', '0000-00-00 15:30:00', '0000-00-00 23:59:59'] as $value) {
        $start = time();
        $timestamp = $helper::getTimestamp($value);

        if ($timestamp < $start - 1 || $timestamp > time() + 1) {
            throw new \RuntimeException('Zero-date value was not normalised to now: ' . $value);
        }
    }

    if ($helper::getTimestamp('not-a-date') !== 0) {
        throw new \RuntimeException('Invalid administrator timestamp was accepted.');
    }

    echo "Joomla 5/6 administrator zero-date regressions passed.\n";
}
