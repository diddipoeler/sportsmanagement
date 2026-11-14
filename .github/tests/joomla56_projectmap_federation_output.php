<?php
/**
 * Verify safe Joomla 5/6 project-map federation labels and image URLs.
 *
 * Exercises real module markup construction without a Joomla installation.
 * Database-supplied names must not become executable map popup HTML.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return the original test translation key.
         *
         * @param string $key Translated federation or country name.
         * @return string Unmodified test value.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        /**
         * Produce a known-safe internal route for map marker fixtures.
         *
         * @param string $view Component view to link.
         * @param array $params Internal route parameters.
         * @return string Fixed test route.
         */
        public static function view(string $view, array $params = []): string { return '/ranking'; }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once \dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_projectmap/src/Helper/ProjectMapHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper();
    $name = 'UEFA <img src=x onerror=alert(1)> "Federation"';

    $projects = [
        (object) [
            'country_federation' => 7,
            'country_alpha2' => 'DE',
            'federation_name' => $name,
            'federation_picture' => 'javascript:alert(1)',
        ],
        (object) [
            'country_federation' => 7,
            'country_alpha2' => 'FR',
            'federation_name' => $name,
            'federation_picture' => 'images/federations/uefa.png',
        ],
        (object) [
            'country_federation' => 7,
            'country_alpha2' => 'DE',
            'federation_name' => $name,
        ],
    ];

    $regions = $helper->createRegions($projects);
    $label = $regions[7]['name'] ?? '';

    if (!is_string($label)
        || $label !== htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        || str_contains($label, '<img')
        || ($regions[7]['states'] ?? null) !== ['DE', 'FR']
        || ($regions[7]['description'] ?? null) !== '') {
        throw new \RuntimeException('Project-map region leaked untrusted federation HTML or lost region data.');
    }

    $encoded = $helper->toJavascriptObjectBody($regions);

    if (str_contains($encoded, '<img') || !str_contains($encoded, '\\u0026lt;img')) {
        throw new \RuntimeException('Project-map JavaScript options expose unescaped HTML.');
    }

    echo "Joomla 5/6 project-map federation output encoding: OK\n";
}
