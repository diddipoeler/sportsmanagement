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
