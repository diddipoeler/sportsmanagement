<?php
/**
 * Regression for retaining the authorized frontend match database.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database { interface DatabaseInterface {} }
namespace Joomla\CMS\Language {
    class Text {
        /** Return the translated test key. @param string $key Language key. @return string Key. */
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS {
    class Factory {
        /** Expose only the default connection to the controller. @return object Container fixture. */
        public static function getContainer(): object {
            return new class {
                /** Return the primary database, distinct from the alternate. @param string $key Service name. @return object Connection. */
                public function get(string $key): object { return $GLOBALS['defaultDb']; }
            };
        }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class FormController {
        public object $input;
        public object $app;
        public object $model;
        /** Return the model created by the test MVC factory. @param string $name Model name. @return object Model. */
        public function getModel(string $name): object { return $this->model; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class ResultsAccessModel {
        public static int $checks = 0;
        public static bool $allow = true;
        public static int $lastSelector = -1;
        public static bool $fallback = false;
        public static int $project = 0;
        /** Capture the authorized selector. @param int $selector Database mode. @return void */
        public function setDatabaseSelector(int $selector): void { self::$lastSelector = $selector; }
        /** Record the requested project. @param int $id Project ID. @return void */
        public function setProjectId(int $id): void { self::$project = $id; }
        /** Simulate authorization of the exact selected match. @param array $ids Match IDs. @return bool Access granted. */
        public function canEditMatches(array $ids): bool {
            ++self::$checks;
            return self::$allow && self::$project === 7 && $ids === [41];
        }
        /** Expose the connection on which the permission check was performed. @return \Joomla\Database\DatabaseInterface Connection. */
        public function getSportsManagementDatabase(): \Joomla\Database\DatabaseInterface {
            return self::$lastSelector === 1 && !self::$fallback ? $GLOBALS['externalDb'] : $GLOBALS['defaultDb'];
        }
    }
    class EditmatchModel {
        public ?\Joomla\Database\DatabaseInterface $authorized = null;
        /** Capture the authorized connection injected by the controller. @param \Joomla\Database\DatabaseInterface $db Effective connection. @return void */
        public function useAuthorizedDatabase(\Joomla\Database\DatabaseInterface $db): void { $this->authorized = $db; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    class IndividualMatchWriteService {
        /** Capture the connection supplied to individual-match persistence. @param \Joomla\Database\DatabaseInterface $database Effective connection. */
        public function __construct(public \Joomla\Database\DatabaseInterface $database) {}
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/Controller/EditmatchController.php';
    $GLOBALS['defaultDb'] = new class implements \Joomla\Database\DatabaseInterface {};
    $GLOBALS['externalDb'] = new class implements \Joomla\Database\DatabaseInterface {};
    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\EditmatchController();
    $controller->app = new \stdClass();
    $controller->input = new class {
        /** Supply a conflicting GET database selector. @param string $key Input name. @param mixed $default Fallback. @param string $filter Filter. @return int Request value. */
        public function get(string $key, mixed $default, string $filter): int { return 0; }
        /** Supply the fallback project ID. @param string $key Input name. @param int $default Default. @return int Project ID. */
        public function getInt(string $key, int $default): int { return 7; }
    };
    $controller->model = new \Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel();
    $access = new \ReflectionMethod($controller, 'assertMatchWriteAccess');
    $model = new \ReflectionMethod($controller, 'editMatchModel');
    $service = new \ReflectionMethod($controller, 'individualMatchWriteService');
    $fixture = \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel::class;
    $db = $access->invoke($controller, ['project_id' => 7, 'cfg_which_database' => '0001'], 41);
    if ($db !== $GLOBALS['externalDb']
        || $model->invoke($controller, $db)->authorized !== $db
        || $service->invoke($controller, $db)->database !== $db
        || $fixture::$lastSelector !== 1) {
        throw new \RuntimeException('Write target differs from authorized connection.');
    }
    $before = $fixture::$checks;
    foreach ([2, '1junk', true, [], null, str_repeat('0', 65)] as $invalid) {
        try {
            $access->invoke($controller, ['project_id' => 7, 'cfg_which_database' => $invalid], 41);
            throw new \LogicException('Malformed database selector accepted.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 400) { throw $e; }
        }
    }
    if ($fixture::$checks !== $before) {
        throw new \RuntimeException('Invalid database mode reached authorization.');
    }
    $fixture::$fallback = true;
    try {
        $access->invoke($controller, ['project_id' => 7, 'cfg_which_database' => 1], 41);
        throw new \LogicException('External mode silently fell back to default database.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    $fixture::$allow = false;
    try {
        $access->invoke($controller, ['project_id' => 7, 'cfg_which_database' => 0], 41);
        throw new \LogicException('Unauthorized edit accepted.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    echo "Authorized match database identity OK\n";
}
