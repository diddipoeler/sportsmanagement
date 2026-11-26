<?php
/**
 * Verify direct native model access and private editor errors.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Form { class Form {} }
namespace Joomla\CMS\Application {
    class AdministratorApplication
    {
        /** Provide a request state collector. @return object Input fixture. */
        public function getInput(): object
        {
            return new class {
                /** Accept the menu visibility flag. @param string $name Request key. @param mixed $value Setting. @return void */
                public function set(string $name, mixed $value): void {}
            };
        }
    }
}
namespace Joomla\CMS\MVC\View {
    class HtmlView
    {
        public mixed $model = null;
        /** Retrieve the explicitly injected model. @return mixed Model fixture. */
        public function getModel(): mixed { return $this->model; }
    }
}
namespace Joomla\CMS\Language {
    class Text {
        /** Translate a message to its test key. @param string $key Language key. @return string Key. */
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Log {
    class Log {
        public const ERROR = 1;
        public static array $entries = [];
        /** Collect redacted log messages. @param string $message Log text. @param int $level Severity. @param string $category Category. @return void */
        public static function add(string $message, int $level, string $category): void { self::$entries[] = $message; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    class SportsManagementAdministratorApplicationResolver {
        /** Supply the administrator application to the editor. @return \Joomla\CMS\Application\AdministratorApplication Fixture. */
        public static function resolve(): \Joomla\CMS\Application\AdministratorApplication { return new \Joomla\CMS\Application\AdministratorApplication(); }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class EditorFixture {
        public array $errors = ['SQL PRIVATE_EDITOR_SECRET'];
        public mixed $item = null;
        /** Return a native form. @return \Joomla\CMS\Form\Form Fixture. */
        public function getForm(): \Joomla\CMS\Form\Form { return new \Joomla\CMS\Form\Form(); }
        /** Return the configured record. @return mixed Fixture. */
        public function getItem(): mixed { return $this->item; }
        /** Return the test state. @return object State fixture. */
        public function getState(): object { return new \stdClass(); }
        /** Return the model's configured errors. @return array Errors. */
        public function getErrors(): array { return $this->errors; }
    }
    final class SeasonModel extends EditorFixture {}
    final class PredictiongroupModel extends EditorFixture {}
    final class PositionModel extends EditorFixture {}
}
namespace {
    define('_JEXEC', 1);
    foreach (['Season', 'Predictiongroup', 'Position'] as $name) {
        require dirname(__DIR__, 2) . '/admin/src/View/' . $name . '/HtmlView.php';
        $viewClass = 'Diddipoeler\\Component\\SportsManagement\\Administrator\\View\\' . $name . '\\HtmlView';
        $modelClass = 'Diddipoeler\\Component\\SportsManagement\\Administrator\\Model\\' . $name . 'Model';
        $view = new $viewClass();
        $view->model = new $modelClass();
        try {
            $view->display();
            throw new \LogicException('Model errors must stop editor rendering.');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'JERROR_AN_ERROR_HAS_OCCURRED') {
                throw new \LogicException('Internal model error was not redacted.');
            }
        }
        $view->model->errors = [];
        try {
            $view->display();
            throw new \LogicException('Missing record must stop editor rendering.');
        } catch (\RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'form could not be loaded')) {
                throw $e;
            }
        }
        $view->model = new \stdClass();
        try {
            $view->display();
            throw new \LogicException('Wrong model must stop editor rendering.');
        } catch (\RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'Model is unavailable')) {
                throw $e;
            }
        }
    }
    $entries = \Joomla\CMS\Log\Log::$entries;
    if (count($entries) !== 3 || str_contains(implode(' ', $entries), 'PRIVATE_EDITOR_SECRET')) {
        throw new \RuntimeException('Private editor details reached the log.');
    }
    echo "Native season and prediction group editor guards OK\n";
}
