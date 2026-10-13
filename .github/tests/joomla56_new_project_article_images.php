<?php
/**
 * Regression tests for image URLs in Joomla New Project articles.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://site.example.test/subdir/';
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_new_project/src/Helper/NewProjectHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper();
    $media = new \ReflectionMethod($helper, 'mediaUrl');

    foreach ([
        ['images/club logo.png', 'https://site.example.test/subdir/images/club%20logo.png'],
        ['/images/logo.png', 'https://site.example.test/subdir/images/logo.png'],
        ['https://cdn.example.test/logo.png', 'https://cdn.example.test/logo.png'],
        ['http://cdn.example.test/logo.png', 'http://cdn.example.test/logo.png'],
        ['', ''],
        ['javascript:alert(1)', ''],
        ['data:image/svg+xml,<svg>', ''],
        ['file:///etc/passwd', ''],
        ['//attacker.example.test/image.png', ''],
        ['../../secret.png', ''],
        ['images/../private.png', ''],
        ['images/./logo.png', ''],
        ['images\secret.png', ''],
        ['images/%2e%2e/private.png', ''],
        ['images/logo.png?redirect=other', ''],
        ['https://user:secret@cdn.example.test/logo.png', ''],
        ["https://cdn.example.test/\nlogo.png", ''],
    ] as [$input, $expected]) {
        $actual = $media->invoke($helper, $input);

        if ($actual !== $expected) {
            throw new \RuntimeException('Unexpected project image URL for ' . json_encode($input));
        }
    }

    $intro = new \ReflectionMethod($helper, 'articleIntro');
    $project = (object) [
        'project_url' => 'index.php?option=com_sportsmanagement&view=resultsranking',
        'name' => '<script>alert(1)</script>',
        'league_name' => 'A&B league',
        'league_picture' => 'javascript:alert(1)',
        'project_picture' => '../private.png',
    ];

    $html = $intro->invoke($helper, $project);

    if (str_contains($html, '<img')
        || str_contains($html, '<script>')
        || !str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;')
        || !str_contains($html, 'A&amp;B league')
        || !str_contains($html, '&amp;view=resultsranking')
    ) {
        throw new \RuntimeException('Project article emitted unsafe markup or empty images');
    }

    $project->name = 'Local Project';
    $project->league_picture = 'images/league logo.png';
    $project->project_picture = 'https://cdn.example.test/project.png';

    $html = $intro->invoke($helper, $project);
    if (!str_contains($html, 'src="https://site.example.test/subdir/images/league%20logo.png"')
        || !str_contains($html, 'src="https://cdn.example.test/project.png"')
        || substr_count($html, '<img ') !== 2
    ) {
        throw new \RuntimeException('Valid New Project article image sources were removed');
    }

    echo "New Project article image URL regression cases OK\n";
}
