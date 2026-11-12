<?php
/**
 * Regression checks for Joomla 5/6 routing, extension-language and module data guards.
 *
 * Run from repository root: php .github/tests/joomla56_routing_language_navigation_guards.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Router {
    /** Lightweight stand-in for the Joomla router in a standalone regression test. */
    final class Route
    {
        /**
         * Return the generated URL unchanged for deterministic input assertions.
         *
         * @param string $url Raw query URL passed by the component.
         * @param bool $xhtml Whether Joomla would encode ampersands.
         * @return string URL that can be inspected by the regression assertions.
         */
        public static function _(string $url, bool $xhtml = false): string
        {
            return $url;
        }
    }
}

namespace Joomla\CMS\Uri {
    /** Lightweight stand-in for Joomla's query encoder. */
    final class Uri
    {
        /**
         * Encode all supplied URL parameters as a deterministic query string.
         *
         * @param array $parameters Component and view query parameters.
         * @return string Encoded URL query parameters.
         */
        public static function buildQuery(array $parameters): string
        {
            return http_build_query($parameters);
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    $root = \dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/SiteRouteHelper.php';

    $routeHelper = \Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper::class;

    foreach ([['option' => []], ['option' => 'com_contact?task=edit'], ['option' => ''], ['option' => 123]] as $invalid) {
        $rejected = false;

        try {
            $routeHelper::query($invalid);
        } catch (\InvalidArgumentException) {
            $rejected = true;
        }

        if (!$rejected) {
            throw new \RuntimeException('Unsafe component option was accepted.');
        }
    }

    if (!\str_contains($routeHelper::query(['option' => 'com_contact', 'view' => 'contact']), 'option=com_contact')) {
        throw new \RuntimeException('Valid cross-component routes were rejected.');
    }

    if (!\str_contains($routeHelper::view('ranking'), 'option=com_sportsmanagement')) {
        throw new \RuntimeException('Valid SportsManagement routes were rejected.');
    }

    $site = \sys_get_temp_dir() . '/sportsmanagement-extension-guard-' . \bin2hex(\random_bytes(8));
    $extensions = $site . '/components/com_sportsmanagement/extensions';
    \mkdir($extensions . '/valid', 0777, true);
    \mkdir($site . '/outside', 0777, true);

    \define('JPATH_SITE', $site);
    require_once $root . '/admin/src/Helper/ExtensionLanguageHelper.php';

    $languageHelper = \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtensionLanguageHelper::class;

    try {
        if ($languageHelper::forView('valid') !== ['valid']) {
            throw new \RuntimeException('Valid extension language path was rejected.');
        }

        if ($languageHelper::forView('../outside') !== []) {
            throw new \RuntimeException('Traversal input was accepted.');
        }

        if (\function_exists('symlink') && @\symlink($site . '/outside', $extensions . '/outside-link')) {
            if ($languageHelper::forView('outside-link') !== []) {
                throw new \RuntimeException('Symlink outside extension root was accepted.');
            }
        }
    } finally {
        if (\is_link($extensions . '/outside-link')) {
            \unlink($extensions . '/outside-link');
        }

        \rmdir($extensions . '/valid');
        \rmdir($site . '/outside');
        \rmdir($extensions);
        \rmdir(\dirname($extensions));
        \rmdir(\dirname(\dirname($extensions)));
        \rmdir($site);
    }

    $dispatcher = \file_get_contents(
        $root . '/modules/mod_sportsmanagement_navigation_menu/src/Dispatcher/Dispatcher.php'
    );

    if ($dispatcher === false || !\str_contains($dispatcher, "'helper', 'seasonselect', 'leagueselect', 'projectselect'")
        || !\str_contains($dispatcher, 'array_key_exists($key, $payload)')
        || \str_contains($dispatcher, 'foreach ($payload as $key => $value)')) {
        throw new \RuntimeException('Navigation dispatcher must whitelist template payload keys.');
    }

    echo "Joomla 5/6 routing, administrator extension paths and module payload guards: OK\n";
}
