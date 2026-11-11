<?php
/**
 * Joomla 5/6 Matches native club links and team logo safety regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store the module parameters used by logo and link test cases.
         *
         * @param array<string,mixed> $values Registry options for this fixture.
         * @return void
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Return a simulated module option or its configured default.
         *
         * @param string $key Parameter name.
         * @param mixed $default Value when the setting is absent.
         * @return mixed Fixture setting or default value.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Return the stable subdirectory installation URL used in media tests.
         *
         * @return string Example Joomla site root including a subdirectory.
         */
        public static function root(): string
        {
            return 'https://site.example.test/subdir/';
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_matches/connectors/native/FormatTrait.php';
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_matches/connectors/native/LinkTrait.php';

    final class MatchesMediaHarness
    {
        use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeFormatTrait;
        use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeLinkTrait;
    }

    $helper = new MatchesMediaHarness();
    $source = new \ReflectionMethod($helper, 'imageSource');
    $website = new \ReflectionMethod($helper, 'externalWebsiteUrl');

    foreach ([
        ['images/team logo.png', 'https://site.example.test/subdir/images/team%20logo.png'],
        ['/images/team.png', 'https://site.example.test/subdir/images/team.png'],
        ['https://cdn.example.test/logo.png', 'https://cdn.example.test/logo.png'],
        ['http://cdn.example.test/logo.png', 'http://cdn.example.test/logo.png'],
        ['', ''],
        ['javascript:alert(1)', ''],
        ['data:image/svg+xml,test', ''],
        ['file:///etc/passwd', ''],
        ['//attacker.example.test/logo.png', ''],
        ['../private.png', ''],
        ['images/../private.png', ''],
        ['images/./logo.png', ''],
        ['images/%2e%2e/private.png', ''],
        ['images\wrong.png', ''],
        ['images/logo.png?secret=x', ''],
        ['https://user:password@cdn.example.test/logo.png', ''],
        ["https://cdn.example.test/\nlogo.png", ''],
    ] as [$input, $expected]) {
        $actual = $source->invoke($helper, $input);

        if ($actual !== $expected) {
            throw new \RuntimeException('Unexpected match image source for ' . json_encode($input));
        }
    }

    foreach ([
        ['example.org/team', 'https://example.org/team'],
        ['https://example.org/team', 'https://example.org/team'],
        ['http://example.org', 'http://example.org'],
        ['https://user:secret@example.org/team', ''],
        ['https://user@example.org/team', ''],
        ['javascript:alert(1)', ''],
        ['ftp://example.org/logo.png', ''],
        ['//example.org/team', ''],
        ["https://example.org/\nteam", ''],
    ] as [$input, $expected]) {
        if ($website->invoke($helper, $input) !== $expected) {
            throw new \RuntimeException('Unsafe public club website link: ' . json_encode($input));
        }
    }

    $team = new \ReflectionMethod($helper, 'team');
    $match = (object) [
        'project_slug' => '11:league',
        'team1_id' => 5,
        'club1_id' => 6,
        'team1_name' => 'Home Team',
        'team1_short_name' => 'Home',
        'team1_picture' => 'javascript:alert(1)',
        'club1_big' => 'https://user:pass@cdn.example.test/logo.png',
        'club1_website' => 'https://user:secret@example.org/club',
    ];
    $params = new \Joomla\Registry\Registry([
        'show_picture' => 1,
        'picture_type' => 'team_picture',
        'link_teams' => 1,
        'link_team_www' => 1,
    ]);

    $unsafe = $team->invoke($helper, $match, true, $params);
    if ($unsafe['logo'] !== null || $unsafe['links'] !== [] || $unsafe['name'] !== 'Home') {
        throw new \RuntimeException('Unsafe team image or external website leaked into match rendering');
    }

    $match->team1_picture = 'images/home team.png';
    $match->club1_website = 'example.org/club';
    $valid = $team->invoke($helper, $match, true, $params);

    if ($valid['logo']['src'] !== 'https://site.example.test/subdir/images/home%20team.png'
        || $valid['links'] !== [[
            'label' => 'WWW',
            'url' => 'https://example.org/club',
            'external' => true,
        ]]
    ) {
        throw new \RuntimeException('Valid match team logo or club website link disappeared');
    }

    // A partially imported team can omit optional image and name columns.
    // Render the fixture without PHP 8 undefined-property warnings and
    // never create internal routes containing team/club ID zero.
    $missing = (object) ['project_slug' => '11:league'];
    $minimalParams = new \Joomla\Registry\Registry([
        'show_picture' => 1,
        'picture_type' => 'team_picture',
        'link_teams' => 1,
        'link_team_plan' => 1,
        'link_team_club' => 1,
    ]);
    $missingTeam = $team->invoke($helper, $missing, true, $minimalParams);

    if ($missingTeam !== ['name' => '', 'logo' => null, 'links' => []]) {
        throw new \RuntimeException('Missing team columns unexpectedly produced links or images.');
    }

    $minimalClub = (object) [
        'project_slug' => '11:league',
        'team1_name' => 'Club only',
        'club1_id' => 6,
    ];
    $clubParams = new \Joomla\Registry\Registry([
        'show_picture' => 1,
        'picture_type' => 'club_big',
        'link_teams' => 1,
        'link_team_plan' => 1,
        'link_team_club' => 0,
    ]);
    $noTeamLinks = $team->invoke($helper, $minimalClub, true, $clubParams);

    if ($noTeamLinks['name'] !== 'Club only'
        || $noTeamLinks['logo'] !== null
        || $noTeamLinks['links'] !== []) {
        throw new \RuntimeException('Missing team ID generated an invalid internal route.');
    }

    // Older match imports may omit optional result columns entirely. Promote
    // PHP warnings to failures to ensure the native formatter stays safe.
    $resultMethod = new \ReflectionMethod($helper, 'result');
    $resultParams = new \Joomla\Registry\Registry(['part_result' => 1]);
    $incompleteRow = ['cancel' => false];
    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $resultMethod->invokeArgs($helper, [&$incompleteRow, (object) [], $resultParams]);

        if ($incompleteRow['result'] !== '-:-' || $incompleteRow['partresults'] !== '') {
            throw new \RuntimeException('Missing match scores changed the placeholder result contract.');
        }

        $cancelledRow = ['cancel' => true];
        $resultMethod->invokeArgs($helper, [&$cancelledRow, (object) [], $resultParams]);

        if ($cancelledRow['result'] !== '' || $cancelledRow['partresults'] !== '') {
            throw new \RuntimeException('Missing cancellation reason broke match display.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Matches team image and external website URL regressions OK\n";
}
