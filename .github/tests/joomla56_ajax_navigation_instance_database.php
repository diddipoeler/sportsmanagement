<?php
/**
 * Regression test for instance-local AJAX navigation database routing.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}
namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_ajax_top_navigation_menu/src/Helper/NavigationDataHelper.php';

    /**
     * Provide mutable request values to simulate another module's input changes.
     */
    final class NavigationTestInput
    {
        public int $selector = 0;

        /**
         * Return an integer request value for the requested field.
         * @param string $name Input field.
         * @param int $default Default value.
         * @return int Simulated input value.
         */
        public function getInt(string $name, int $default = 0): int
        {
            return $name === 'cfg_which_database' ? $this->selector : $default;
        }

        /**
         * Return the default command value.
         * @param string $name Input field.
         * @param string $default Default command.
         * @return string Default command.
         */
        public function getCmd(string $name, string $default = ''): string
        {
            return $default;
        }

        /**
         * Return the simulated raw selector or the supplied default.
         * @param string $name Input field.
         * @param mixed $default Default value.
         * @param string $filter Input filter.
         * @return mixed Simulated raw input.
         */
        public function get(string $name, mixed $default = null, string $filter = 'RAW'): mixed
        {
            return $name === 'cfg_which_database' ? $this->selector : $default;
        }
    }

    /**
     * Supply a shared request without booting Joomla.
     */
    final class NavigationTestApplication implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        /**
         * Retain the mutable input fixture.
         * @param NavigationTestInput $input Shared input.
         */
        public function __construct(private NavigationTestInput $input) {}

        /**
         * Expose the shared input fixture.
         * @return NavigationTestInput Shared request.
         */
        public function getInput(): NavigationTestInput
        {
            return $this->input;
        }
    }

    $class = new ReflectionClass(\Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper\NavigationDataHelper::class);
    $input = new NavigationTestInput();
    $app = new NavigationTestApplication($input);
    $instances = [];
    foreach ([0, 1] as $selector) {
        $helper = $class->newInstanceWithoutConstructor();
        $class->getProperty('_app')->setValue($helper, $app);
        $class->getProperty('selectedDatabase')->setValue($helper, $selector);
        $instances[$selector] = $helper;
    }

    foreach ([0, 1, 0] as $requestSelector) {
        $input->selector = $requestSelector;
        foreach ($instances as $expected => $helper) {
            $base = $class->getMethod('baseRouteParameters')->invoke($helper);
            if ($base['cfg_which_database'] !== $expected
                || $helper->getQueryValues()['cfg_which_database'] !== $expected) {
                throw new RuntimeException('Navigation route database selector leaked across instances.');
            }
        }
    }

    echo "AJAX navigation instance database regression passed.\n";
}
