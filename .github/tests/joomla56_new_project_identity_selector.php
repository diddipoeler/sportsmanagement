<?php
/**
 * Joomla 5/6 New Project module authorization and selector validation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_new_project/src/Helper/NewProjectHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper();
    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public ?object $identity = null;
        public function getIdentity(): ?object { return $this->identity; }
    };
    $params = new \Joomla\Registry\Registry(['new_project_article' => 1, 'mycategory' => 3]);

    if ($helper->canCreateArticles($params, $app)) {
        throw new \RuntimeException('No identity was permitted to create articles.');
    }

    $app->identity = new class {
        public bool $allowed = true;
        public function authorise(string $action, string $asset): bool
        {
            return $this->allowed && (
                ($action === 'core.manage' && $asset === 'com_sportsmanagement')
                || ($action === 'core.create' && $asset === 'com_content.category.3')
            );
        }
    };

    if (!$helper->canCreateArticles($params, $app)) {
        throw new \RuntimeException('Valid article permissions were rejected.');
    }

    $app->identity->allowed = false;
    if ($helper->canCreateArticles($params, $app)) {
        throw new \RuntimeException('Unauthorised visitor gained article creation permission.');
    }

    $app->identity->allowed = true;

    // Malformed settings must never turn on the article writer or grant
    // access to an unintended Joomla content category.
    foreach ([true, ['1'], 9, '1junk', '01', false] as $flag) {
        if ($helper->canCreateArticles(new \Joomla\Registry\Registry([
            'new_project_article' => $flag, 'mycategory' => 3,
        ]), $app)) {
            throw new \RuntimeException('Malformed New Project article switch granted write permission.');
        }
    }

    foreach ([true, ['3'], 3.5, '3junk', 0, '-3', PHP_INT_MAX . '0'] as $category) {
        if ($helper->canCreateArticles(new \Joomla\Registry\Registry([
            'new_project_article' => 1, 'mycategory' => $category,
        ]), $app)) {
            throw new \RuntimeException('Malformed New Project category granted write permission.');
        }
    }

    if (!$helper->canCreateArticles(new \Joomla\Registry\Registry([
        'new_project_article' => '1', 'mycategory' => '0003',
    ]), $app)) {
        throw new \RuntimeException('Valid legacy New Project settings were rejected.');
    }

    $selector = new \ReflectionMethod($helper, 'databaseSelector');
    foreach ([
        [0, 0], [5, 5], ['0', 0], ['0005', 5],
        [-1, null], ['-1', null], ['5junk', null], [true, null],
        [['5'], null], [' 5', null], ['5.0', null],
        ['9999999999999999999999999999', null],
    ] as [$raw, $expected]) {
        if ($selector->invoke($helper, $raw) !== $expected) {
            throw new \RuntimeException('Malformed database selector was accepted.');
        }

        if ($expected === null) {
            $output = $helper->getData(new \Joomla\Registry\Registry([
                'cfg_which_database' => $raw,
            ]), $app);

            if ($output !== []) {
                throw new \RuntimeException('Invalid selector queried or rendered module data.');
            }
        }
    }

    echo "New Project permission and database selector guards OK\n";
}
