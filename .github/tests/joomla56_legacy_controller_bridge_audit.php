<?php
/**
 * Joomla 5/6 legacy administrator controller bridge audit.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$controllerDirectory = dirname(__DIR__, 2) . '/admin/controllers';
$checked = 0;
$errors = [];

$validatedBridges = [
    'sportsmanagement.php', 'sportsmanagements.php',
    'smextxmleditor.php', 'smextxmleditors.php',
    'smquote.php', 'smquotes.php',
    'jlextassociation.php', 'jlextassociations.php',
    'jlextcountry.php', 'jlextcountries.php',
    'jlextfederation.php', 'jlextfederations.php',
];

foreach ($validatedBridges as $name) {
    $file = $controllerDirectory . '/' . $name;
    $source = file_get_contents($file);

    if ($source === false) {
        $errors[] = "$name: unreadable";
        continue;
    }

    // The administrator compatibility bridges in this regression set must keep their metadata.
    foreach (['@version', '@author', '@copyright', '@license'] as $tag) {
        if (!str_contains(substr($source, 0, 800), $tag)) {
            $errors[] = "$name: missing $tag";
        }
    }

    ++$checked;
    if (str_contains($source, "JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/")) {
        $errors[] = "$name: absolute native controller path remains";
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "Audited $checked administrator compatibility bridges.\n";
