<?php
/**
 * Regression tests for Joomla 5/6 component route helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Router {
    class Route
    {
        public static function _(string $url, bool $xhtml = true): string
        {
            return $url;
        }
    }
}
namespace Joomla\CMS\Uri {
    class Uri
    {
        public static function buildQuery(array $parameters): string
        {
            return http_build_query($parameters);
        }
    }
}
namespace {
    define('_JEXEC', 1);
    require __DIR__ . '/../../site/src/Helper/SiteRouteHelper.php';

    use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;

    $url = SiteRouteHelper::view('ranking', [
        'view' => 'unrelated',
        'option' => 'com_other',
        'p' => 42,
        'Itemid' => 0,
    ]);
    parse_str((string) parse_url($url, PHP_URL_QUERY), $params);

    if (($params['option'] ?? null) !== 'com_sportsmanagement'
        || ($params['view'] ?? null) !== 'ranking'
        || ($params['p'] ?? null) !== '42'
        || isset($params['Itemid'])) {
        throw new \RuntimeException('SiteRouteHelper::view must retain its component and view.');
    }

    $url = SiteRouteHelper::view('teaminfo', ['Itemid' => 77, 'tid' => 9]);
    parse_str((string) parse_url($url, PHP_URL_QUERY), $params);

    if (($params['Itemid'] ?? null) !== '77' || ($params['tid'] ?? null) !== '9') {
        throw new \RuntimeException('SiteRouteHelper::view must preserve valid route parameters.');
    }

    echo "SiteRouteHelper routing contract OK.\n";
}
