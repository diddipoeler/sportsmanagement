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

    foreach ([
        '77garbage', '-9', '1e3', '77.5', '999999999999999999999',
        true, 10.5, ['77'], (object) ['id' => 77],
    ] as $invalidItemId) {
        $url = SiteRouteHelper::view('ranking', ['Itemid' => $invalidItemId]);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $params);

        if (isset($params['Itemid'])) {
            throw new \RuntimeException('Invalid menu ID was accepted by SiteRouteHelper.');
        }
    }

    $url = SiteRouteHelper::view('ranking', ['Itemid' => '00077']);
    parse_str((string) parse_url($url, PHP_URL_QUERY), $params);

    if (($params['Itemid'] ?? null) !== '77') {
        throw new \RuntimeException('Valid leading-zero menu ID was not normalized.');
    }


    // Malformed options must never become nested option[] parameters or
    // redirect this component helper to an unexpected component.
    foreach ([
        [],
        ['option' => ['com_content']],
        ['option' => (object) ['name' => 'com_content']],
        ['option' => false],
        ['option' => "com_content\n"],
        ['option' => ''],
    ] as $input) {
        $url = SiteRouteHelper::query($input + ['view' => 'ranking']);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $params);

        if (($params['option'] ?? null) !== 'com_sportsmanagement'
            || ($params['view'] ?? null) !== 'ranking') {
            throw new \RuntimeException('Malformed component option was accepted in a site route.');
        }
    }

    // Intentionally requested routes to another Joomla component remain valid.
    $url = SiteRouteHelper::query(['option' => 'com_content', 'view' => 'article', 'id' => 7]);
    parse_str((string) parse_url($url, PHP_URL_QUERY), $params);

    if (($params['option'] ?? null) !== 'com_content' || ($params['id'] ?? null) !== '7') {
        throw new \RuntimeException('SiteRouteHelper::query lost a valid external component target.');
    }

    echo "SiteRouteHelper routing contract OK.\n";
}
