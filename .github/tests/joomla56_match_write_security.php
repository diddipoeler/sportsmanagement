<?php
/**
 * Regression checks for Joomla 5/6 match write actions, JSON and module links.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

$root = dirname(__DIR__, 2);
$siteSource = file_get_contents($root . '/site/src/Controller/EditmatchController.php');
$adminSource = file_get_contents($root . '/admin/src/Controller/AjaxController.php');

if (!is_string($siteSource) || !is_string($adminSource)) {
    throw new RuntimeException('Unable to read the Joomla 5/6 controller sources.');
}

foreach ([
    'savestats',
    'saveReferees',
    'saverosterbillard',
    'saveroster',
    'saveshort',
    'saveIndividualShort',
] as $method) {
    $pattern = '~function\s+' . preg_quote($method, '~')
        . '\(\):\s*void\s*\{\s*\$this->checkToken\(\);~';

    if (preg_match($pattern, $siteSource) !== 1) {
        throw new RuntimeException('Missing POST token validation in editmatch::' . $method);
    }
}

foreach ([
    "setHeader('Content-Type', 'application/json; charset=utf-8', true)",
    'JSON_INVALID_UTF8_SUBSTITUTE',
    'JSON_THROW_ON_ERROR',
] as $required) {
    if (!str_contains($adminSource, $required)) {
        throw new RuntimeException('Missing administrator AJAX JSON guard: ' . $required);
    }
}

// The native module trait needs Uri::root() for relative image paths.
class JsmTestUri
{
    public static function root(): string
    {
        return 'https://joomla.example/site/';
    }
}

class_alias(JsmTestUri::class, 'Joomla\CMS\Uri\Uri');

require_once $root . '/modules/mod_sportsmanagement_matches/connectors/native/LinkTrait.php';
require_once $root . '/modules/mod_sportsmanagement_matches/connectors/native/FormatTrait.php';

$helper = new class {
    use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeLinkTrait;
    use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeFormatTrait;

    public function normalizeWebsite(string $value): string
    {
        return $this->externalWebsiteUrl($value);
    }

    public function normalizeImage(string $value): string
    {
        return $this->imageSource($value);
    }
};

foreach ([
    'https://example.org/teams' => 'https://example.org/teams',
    'http://example.org' => 'http://example.org',
    'example.org' => 'https://example.org',
    'javascript:alert(1)' => '',
    'data:text/html,unsafe' => '',
    '//example.org' => '',
    'https://example.org/' . "\n" . 'Injected' => '',
] as $provided => $expected) {
    $actual = $helper->normalizeWebsite($provided);

    if ($actual !== $expected) {
        throw new RuntimeException('Unsafe or incorrect module website normalization for ' . json_encode($provided));
    }
}

foreach ([
    'https://example.org/logo.png' => 'https://example.org/logo.png',
    'http://example.org/logo.png' => 'http://example.org/logo.png',
    'images/clubs/logo.png' => 'https://joomla.example/site/images/clubs/logo.png',
    '/images/clubs/logo.png' => 'https://joomla.example/site/images/clubs/logo.png',
    'javascript:alert(1)' => '',
    'data:image/svg+xml,unsafe' => '',
    'ftp://example.org/logo.png' => '',
    '//example.org/logo.png' => '',
    '../private/logo.png' => '',
    'images/../../private/logo.png' => '',
    'https://example.org/' . "\n" . 'logo.png' => '',
] as $provided => $expected) {
    $actual = $helper->normalizeImage($provided);

    if ($actual !== $expected) {
        throw new RuntimeException('Unsafe or incorrect module image normalization for ' . json_encode($provided));
    }
}

$moduleSource = file_get_contents($root . '/modules/mod_sportsmanagement_matches/connectors/native/FormatTrait.php');
if (!is_string($moduleSource) || !str_contains($moduleSource, "preg_match('/^[a-z]{2}$/', $alpha2)")) {
    throw new RuntimeException('The matches module must validate country flag codes.');
}

if (!str_contains($adminSource, 'catch (\Throwable $error)')
    || !str_contains($adminSource, "Log::add(__METHOD__")
    || !str_contains($adminSource, "'success' => false")
) {
    throw new RuntimeException('Administrator AJAX errors must be logged and returned as JSON.');
}

echo "Joomla 5/6 match write guards, admin JSON and module links: OK\n";
