<?php
/**
 * Joomla 5/6 team display regression for malformed legacy data fields.
 *
 * Run without Joomla to verify native site helpers do not emit PHP 8
 * array-to-string warnings or accidentally render unsafe name markup.
 *
 * Usage: php .github/tests/joomla56-team-presentation-fields.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/site/src/Helper/TeamPresentationHelper.php';
require_once dirname(__DIR__, 2) . '/site/src/Helper/NamePresentationHelper.php';

use Diddipoeler\Component\SportsManagement\Site\Helper\TeamPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\NamePresentationHelper;

// Catch both ordinary PHP notices and warnings caused by malformed fields.
set_error_handler(
    static function (int $severity, string $message): never {
        throw new ErrorException($message, 0, $severity);
    }
);

try {
    $team = (object) [
        'id' => 0,
        'name' => 'North & <South>',
        'short_name' => ['bad'],
        'middle_name' => new stdClass(),
    ];
    $output = TeamPresentationHelper::formatName(
        $team,
        'team',
        ['team_name_format' => 0],
    );

    if ($output !== '<span style="padding:2px;">North &amp; &lt;South&gt;</span>') {
        throw new RuntimeException('Invalid team short name broke escaped fallback.');
    }

    // Incomplete team imports may contain only the middle or short name.
    // Every configured style should display an available alternative.
    $partialNames = (object) [
        'name' => '',
        'middle_name' => 'Middle Club',
        'short_name' => 'MC',
    ];

    if (TeamPresentationHelper::formatName($partialNames, 'team', ['team_name_format' => 2])
        !== '<span style="padding:2px;">Middle Club</span>') {
        throw new RuntimeException('Missing full name hid an available middle name.');
    }

    $partialNames->middle_name = '';
    if (TeamPresentationHelper::formatName($partialNames, 'team', ['team_name_format' => 1])
        !== '<span style="padding:2px;">MC</span>') {
        throw new RuntimeException('Missing middle name hid an available short name.');
    }

    // A short-only team must not render an empty abbreviation tooltip.
    $shortOnly = (object) ['name' => '', 'short_name' => 'FC'];
    $shortMarkup = TeamPresentationHelper::formatName(
        $shortOnly, 'team', ['team_name_format' => 0]
    );

    if ($shortMarkup !== '<span style="padding:2px;">FC</span>') {
        throw new RuntimeException('Short-only imported team generated an empty abbreviation title.');
    }

    $completeTeam = (object) ['name' => 'Football Club', 'short_name' => 'FC'];
    $abbr = TeamPresentationHelper::formatName(
        $completeTeam, 'team', ['team_name_format' => 0]
    );

    if ($abbr !== '<span style="padding:2px;"><abbr title="Football Club">FC</abbr></span>') {
        throw new RuntimeException('Complete team abbreviation tooltip was lost.');
    }

    // Damaged module settings must not select unintended name formats or
    // trigger PHP 8 array-to-integer conversion warnings.
    foreach ([['invalid'], true, new stdClass(), '2junk', '999999999999999999999999'] as $invalid) {
        $safeFormat = TeamPresentationHelper::formatName(
            (object) ['name' => 'Full Club', 'short_name' => 'FC', 'middle_name' => 'Mid Club'],
            'team',
            ['team_name_format' => $invalid]
        );

        if ($safeFormat !== '<span style="padding:2px;">Full Club</span>') {
            throw new RuntimeException('Malformed name format chose an unintended display style.');
        }
    }

    $choice = new ReflectionMethod(TeamPresentationHelper::class, 'selectedOption');
    foreach ([
        [0, 0], ['0', 0], [1, 1], ['1', 1], [2, 2], ['2', 2],
        [['2'], 0], [true, 0], ['2suffix', 0], ['999999999999999999999999', 0],
    ] as [$value, $expected]) {
        if ($choice->invoke(null, $value, [0, 1, 2], 0) !== $expected) {
            throw new RuntimeException('Malformed team display option was accepted.');
        }
    }

    // Legacy arrays must not accidentally activate favourite highlighting.
    // Explicit Joomla booleans and numeric string toggles remain supported.
    $favourite = (object) [
        'fav_team_text_bold' => '1',
        'fav_team_text_color' => '#123',
        'fav_team_color' => 'navy',
    ];
    $fullTeam = (object) ['name' => 'Town FC'];
    foreach ([['1'], new stdClass(), 'yes', 2] as $badToggle) {
        $plain = TeamPresentationHelper::formatName(
            $fullTeam,
            'team',
            ['highlight_fav' => $badToggle],
            true,
            $favourite
        );

        if ($plain !== '<span style="padding:2px;">Town FC</span>') {
            throw new RuntimeException('Malformed highlight toggle activated CSS unexpectedly.');
        }
    }

    $validHighlight = TeamPresentationHelper::formatName(
        $fullTeam, 'team', ['highlight_fav' => '1'], true, $favourite
    );
    if ($validHighlight !== '<span style="padding:2px;font-weight:bold;color:#123;background-color:navy;">Town FC</span>') {
        throw new RuntimeException('Valid favourite highlight toggle was ignored.');
    }

    $project = (object) [
        'fav_team_text_bold' => ['invalid'],
        'fav_team_text_color' => new stdClass(),
        'fav_team_color' => ['invalid'],
    ];
    $highlighted = TeamPresentationHelper::formatName(
        $team,
        'team',
        ['team_name_format' => 2, 'highlight_fav' => 1],
        true,
        $project
    );

    if ($highlighted !== '<span style="padding:2px;">North &amp; &lt;South&gt;</span>') {
        throw new RuntimeException('Invalid project highlight fields altered markup.');
    }

    // Escaping quotes does not prevent an injected CSS declaration. Reject
    // semicolons, CSS functions and malformed values from legacy projects.
    $project->fav_team_text_color = 'red;position:fixed';
    $project->fav_team_color = 'url(javascript:alert(1))';
    $unsafeColors = TeamPresentationHelper::formatName(
        $team,
        'team',
        ['team_name_format' => 2, 'highlight_fav' => 1],
        true,
        $project
    );

    if ($unsafeColors !== '<span style="padding:2px;">North &amp; &lt;South&gt;</span>') {
        throw new RuntimeException('Unsafe project CSS colors leaked into team display.');
    }

    $project->fav_team_text_color = '#A1b2C3';
    $project->fav_team_color = 'navy';
    $safeColors = TeamPresentationHelper::formatName(
        $team,
        'team',
        ['team_name_format' => 2, 'highlight_fav' => 1],
        true,
        $project
    );

    if ($safeColors !== '<span style="padding:2px;color:#A1b2C3;background-color:navy;">North &amp; &lt;South&gt;</span>') {
        throw new RuntimeException('Safe project highlight colors were lost.');
    }

    // CSS hexadecimal notation permits only 3, 4, 6 or 8 digits.
    // Keep alpha-channel colors while rejecting accidentally permitted
    // five- and seven-digit values and declarations injected into CSS.
    $validateColor = new ReflectionMethod(TeamPresentationHelper::class, 'safeColor');

    foreach ([
        ['#abc', '#abc'],
        ['#abcd', '#abcd'],
        ['#abcdef', '#abcdef'],
        ['#abcdef01', '#abcdef01'],
        ['rebeccapurple', 'rebeccapurple'],
        ['#ab', ''],
        ['#abcde', ''],
        ['#abcdef0', ''],
        ['#abcdef012', ''],
        ['red;background:url(javascript:alert(1))', ''],
        [['not-a-color'], ''],
    ] as [$rawColor, $expectedColor]) {
        if ($validateColor->invoke(null, $rawColor) !== $expectedColor) {
            throw new RuntimeException('Incorrect team highlight CSS color validation: ' . json_encode($rawColor));
        }
    }

    $team->id = 5;
    $team->project_id = 8;
    $team->name = ['invalid'];

    if (TeamPresentationHelper::renderActions($team, []) !== '') {
        throw new RuntimeException('Disabled team actions should not render links.');
    }

    $person = (object) [
        'firstname' => ['invalid'],
        'nickname' => new stdClass(),
        'lastname' => 'Müller & Sons',
    ];

    if (NamePresentationHelper::person($person, 8) !== 'Müller &amp; Sons') {
        throw new RuntimeException('Invalid person fields did not retain HTML-safe surname.');
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 malformed team and person presentation regressions passed.\n";
