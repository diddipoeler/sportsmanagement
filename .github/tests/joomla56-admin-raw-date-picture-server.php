<?php
/**
 * Runtime checks for raw administrator dates and configured picture-server URLs.
 *
 * Run: php .github/tests/joomla56-admin-raw-date-picture-server.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Date {
    /** Minimal Joomla date stand-in for administrator timezone conversion. */
    class Date extends \DateTime
    {
        /**
         * Parse a valid ISO date into an easily inspected DateTime.
         *
         * @param string $value Stored SQL date.
         */
        public function __construct(string $value)
        {
            parent::__construct($value, new \DateTimeZone('UTC'));
        }
    }
}

namespace Joomla\CMS\Uri {
    /** Provide Joomla's site root without loading the full CMS. */
    final class Uri
    {
        /**
         * Return the fixture root for rejected remote picture-server values.
         *
         * @return string Safe local root URL.
         */
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
    use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;

    \define('_JEXEC', 1);
    \define('COM_SPORTSMANAGEMENT_PICTURE_SERVER', "https://cdn.example.test/media/\n");

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Helper/SportsManagementDateHelper.php';
    require_once $root . '/site/src/Helper/PersonImageHelper.php';

    foreach (["2026-11-17\n", "\t2026-11-17", "2026-11-17\0"] as $badDate) {
        if (SportsManagementDateHelper::toSqlDate($badDate) !== $badDate) {
            throw new \RuntimeException('Raw form date was normalized despite control bytes.');
        }

        $match = (object) ['match_date' => $badDate, 'timezone' => 'UTC'];
        SportsManagementDateHelper::convertMatchDateToTimezone($match);

        if ($match->match_date !== null) {
            throw new \RuntimeException('Raw match date with controls was accepted.');
        }
    }

    $badTimezone = (object) [
        'match_date' => '2026-11-17 12:30:00',
        'timezone' => "Europe/Berlin\n",
    ];
    SportsManagementDateHelper::convertMatchDateToTimezone($badTimezone);

    if ($badTimezone->match_date !== null) {
        throw new \RuntimeException('Stored timezone with control bytes was accepted.');
    }

    $valid = (object) [
        'match_date' => '2026-11-17 12:30:00',
        'timezone' => 'Europe/Berlin',
    ];
    SportsManagementDateHelper::convertMatchDateToTimezone($valid);

    if (!$valid->match_date instanceof \DateTimeInterface
        || $valid->match_date->getTimezone()->getName() !== 'Europe/Berlin') {
        throw new \RuntimeException('Valid stored match date or timezone was rejected.');
    }

    if (SportsManagementDateHelper::toSqlDate('17.11.2026') !== '2026-11-17') {
        throw new \RuntimeException('Valid localized administrator date was rejected.');
    }

    if (PersonImageHelper::url('images/players/safe.png')
        !== 'https://example.test/images/players/safe.png') {
        throw new \RuntimeException('Control-bearing configured picture host was not rejected.');
    }

    echo "Joomla 5/6 administrator raw dates and picture-server regressions passed.\n";
}
