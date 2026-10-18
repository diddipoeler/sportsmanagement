<?php
/**
 * Joomla 5/6 administrator image destination and symlink regression tests.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

define('_JEXEC', 1);

$base = sys_get_temp_dir() . '/jsm-image-target-' . getmypid();
$images = $base . '/images/com_sportsmanagement/database';
$outside = $base . '/external';

if (!@mkdir($images, 0700, true) || !@mkdir($outside, 0700, true)) {
    throw new RuntimeException('Unable to prepare isolated image destination test.');
}

define('JPATH_ROOT', $base);

try {
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/RemoteImageDownloadHelper.php';

    $helper = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\RemoteImageDownloadHelper();
    $check = new ReflectionMethod($helper, 'isAllowedTargetPath');

    foreach ([
        [$images . '/clubs/club.png', true],
        [$images . '/photo.JPG', true],
        [$images . '/photo.webp', true],
        [$images . '/../escape.png', false],
        [$images . '/clubs/../../escape.png', false],
        [$outside . '/escape.png', false],
        [$images . '/photo.svg', false],
        [$images . '/photo.php', false],
        [$images . '/sub//photo.jpg', false],
    ] as [$path, $expected]) {
        if ($check->invoke($helper, $path) !== $expected) {
            throw new RuntimeException('Invalid image target path validation: ' . $path);
        }
    }

    if (!symlink($outside, $images . '/linked')) {
        throw new RuntimeException('Unable to create a test directory symlink.');
    }

    if (!symlink($outside . '/image.jpg', $images . '/linked-image.jpg')) {
        throw new RuntimeException('Unable to create a test file symlink.');
    }

    foreach ([$images . '/linked/image.jpg', $images . '/linked-image.jpg'] as $path) {
        if ($check->invoke($helper, $path)) {
            throw new RuntimeException('Administrator image download accepted an escaping symlink.');
        }
    }

    echo "Joomla 5/6 image download destination checks passed.\n";
} finally {
    @unlink($images . '/linked-image.jpg');
    @unlink($images . '/linked');
    @rmdir($images);
    @rmdir(dirname($images));
    @rmdir(dirname(dirname($images)));
    @rmdir($outside);
    @rmdir($base);
}
