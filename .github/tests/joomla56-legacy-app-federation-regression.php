<?php
/**
 * Regression checks for Joomla 5/6 legacy application access and federation IDs.
 *
 * Verifies that the compatibility interfaces delegate to native Joomla
 * application resolvers and that legacy module rows cannot trigger PHP 8
 * conversion warnings or silently create a federation identifier.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    // Only stub dependency names: the tested legacy facade needs no database.
    class SportsManagementDatabaseResolver {}
    class SportsManagementSiteApplicationResolver {}
}

namespace Diddipoeler\Module\SportsManagementActSeason\Site\Helper {
    class ActSeasonHelper {}
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_SITE', dirname(__DIR__, 2));

    $root = dirname(__DIR__, 2);
    $site = (string) file_get_contents($root . '/site/models/sportsmanagement.php');
    $admin = (string) file_get_contents($root . '/admin/helpers/sportsmanagement.php');
    $module = (string) file_get_contents($root . '/modules/mod_sportsmanagement_act_season/helper.php');

    foreach (['site' => $site, 'admin' => $admin, 'modules' => $module] as $area => $source) {
        if (preg_match('~\A<\?php\s*/\*\*(.*?)\*/~s', $source, $match) !== 1) {
            throw new \RuntimeException($area . ': missing file-level PHPDoc');
        }

        foreach (['@version', '@author', '@copyright', '@license'] as $tag) {
            if (preg_match('/^\s*\*\s*' . preg_quote($tag, '/') . '\s+\S+/m', $match[1]) !== 1) {
                throw new \RuntimeException($area . ': missing metadata ' . $tag);
            }
        }
    }

    if (str_contains($site, 'return Factory::getApplication();')
        || !str_contains($site, 'SportsManagementSiteApplicationResolver::resolve()')
        || str_contains($admin, 'return Factory::getApplication();')
        || !str_contains($admin, 'SportsManagementAdministratorApplicationResolver::resolve()')
        || preg_match('/->input\b/', $admin) !== 0
        || !str_contains($admin, '->getInput()')) {
        throw new \RuntimeException('Legacy Joomla application or input access regression.');
    }

    require_once $root . '/modules/mod_sportsmanagement_act_season/helper.php';
    $rows = [
        (object) ['federation' => '0007', 'federation_name' => 'Regional'],
        (object) ['federation' => 7, 'federation_name' => 'Duplicate'],
        (object) ['federation' => 3, 'federation_name' => ['not a name']],
        (object) ['federation' => '0009', 'federation_name' => null],
        (object) ['federation' => true, 'federation_name' => 'invalid bool'],
        (object) ['federation' => ['1'], 'federation_name' => 'invalid array'],
        (object) ['federation' => '7trailing', 'federation_name' => 'invalid suffix'],
        (object) ['federation' => PHP_INT_MAX . '0', 'federation_name' => 'overflow'],
        (object) ['federation' => 0, 'federation_name' => 'invalid zero'],
        (object) ['federation' => -2, 'federation_name' => 'invalid negative'],
        (object) ['federation' => new \stdClass(), 'federation_name' => 'invalid object'],
        ['federation' => 10, 'federation_name' => 'not an object row'],
    ];

    // Treat notices as errors so malformed stored values are not silently cast.
    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $federations = \modJSMActSeasonHelper::getDataFederation($rows);
    } finally {
        restore_error_handler();
    }

    if (array_keys($federations) !== [7, 3, 9]
        || $federations[7]->name !== 'Regional'
        || $federations[3]->name !== '3'
        || $federations[9]->name !== '9') {
        throw new \RuntimeException('Invalid or duplicate federation rows were accepted.');
    }

    $id = new \ReflectionMethod(\modJSMActSeasonHelper::class, 'positiveFederationId');

    foreach ([
        ['0004', 4], [5, 5], ['0', 0], [false, 0],
        [['3'], 0], ['-2', 0], ['2xyz', 0],
        [PHP_INT_MAX . '0', 0], [PHP_INT_MAX, PHP_INT_MAX],
    ] as [$raw, $expected]) {
        if ($id->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Invalid federation ID accepted by the module.');
        }
    }

    echo "Legacy Joomla 5/6 application and federation checks passed.\n";
}
