<?php
/**
 * Joomla 5/6 prediction round administrator write and model guards.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $value): string { return $value; }
        public static function sprintf(string $value, mixed ...$args): string { return $value; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController {
        protected object $app;
        public bool $validToken = true;
        public array $allowed = [];
        public int $modelCalls = 0;
        public mixed $formModel = false;
        public mixed $listModel = false;
        public array $lastConfig = [];
        public ?array $redirect = null;

        public function __construct() {
            $input = new class {
                public array $ids = [];
                public object $post;
                public function __construct() {
                    $this->post = new class {
                        public function getArray(): array { return []; }
                    };
                }
                public function get(string $name, mixed $fallback = null, string $type = 'raw'): mixed {
                    return $name === 'cid' ? $this->ids : $fallback;
                }
            };
            $this->app = new class($input) {
                public int $predictionId = 0;
                public function __construct(private object $input) {}
                public function getInput(): object { return $this->input; }
                public function getUserStateFromRequest(string $state, string $request, int $default, string $type): int {
                    return $this->predictionId;
                }
            };
        }
        public function setIds(array $ids): void { $this->app->getInput()->ids = $ids; }
        public function setPredictionId(int $id): void { $this->app->predictionId = $id; }
        public function getModel($name = '', $prefix = '', $config = []) {
            ++$this->modelCalls;
            $this->lastConfig = $config;
            return $name === 'Predictionrounds' ? $this->listModel : $this->formModel;
        }
        protected function checkToken(): void {
            if (!$this->validToken) { throw new \RuntimeException('Invalid token', 403); }
        }
        protected function assertWritePermission(string $action = 'core.edit'): void {
            if (!in_array($action, $this->allowed, true)) {
                throw new \RuntimeException('Forbidden', 403);
            }
        }
        protected function setRedirect(string $url, mixed $message = '', string $type = 'message'): void {
            $this->redirect = compact('url', 'message', 'type');
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/PredictionroundsController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionroundsController();
    $controller->getModel('Predictionround', 'Administrator', ['sentinel' => true, 'ignore_request' => false]);
    if ($controller->lastConfig !== ['sentinel' => true, 'ignore_request' => true]) {
        throw new \RuntimeException('Prediction rounds lost Joomla MVC options.');
    }

    $ids = new \ReflectionMethod($controller, 'selectedRoundIds');
    if ($ids->invoke(null, [12, '0013', '13', '13junk', -2, true, [], '99999999999999999999']) !== [12, 13]
        || count($ids->invoke(null, range(1, 650))) !== 500) {
        throw new \RuntimeException('Prediction round identifiers were truncated or unbounded.');
    }

    $controller->setIds([12]);
    $controller->setPredictionId(7);
    foreach (['saveshort' => 'core.edit', 'populateFromProjectRounds' => 'core.create'] as $task => $required) {
        $controller->allowed = [];
        $controller->validToken = true;
        $before = $controller->modelCalls;
        try {
            $controller->{$task}();
            throw new \RuntimeException('Unauthorized prediction round write passed.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
        if ($controller->modelCalls !== $before) {
            throw new \RuntimeException('Unauthorized prediction rounds read the model.');
        }

        $controller->allowed = [$required];
        $controller->validToken = false;
        try {
            $controller->{$task}();
            throw new \RuntimeException('Invalid prediction round CSRF token was accepted.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
        if ($controller->modelCalls !== $before) {
            throw new \RuntimeException('Invalid prediction rounds token loaded the model.');
        }
        $controller->validToken = true;
        try {
            $controller->{$task}();
            throw new \RuntimeException('Missing prediction round model was accepted.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 500 || !str_contains($e->getMessage(), 'model is unavailable')) {
                throw $e;
            }
        }
    }

    $controller->allowed = ['core.edit', 'core.create'];
    $controller->formModel = new class {
        public array $saved = [];
        public array $added = [];
        public function saveshort(array $ids, array $form): string {
            $this->saved = $ids;
            return 'saved';
        }
        public function addPredRoundIds(array $ids, int $game, int $project): string {
            $this->added = [$ids, $game, $project];
            return 'added';
        }
    };
    $controller->listModel = new class {
        public function getPredictionProjectIds(int $game): array { return [22]; }
        public function getPredGamesPredictionRoundsIds(int $game): array { return [3]; }
        public function getProjectRoundIds(int $project): array { return [3, 4]; }
    };

    $controller->setIds([12, '0013', '13junk', -4]);
    $controller->saveshort();
    $controller->populateFromProjectRounds();

    if ($controller->formModel->saved !== [12, 13]
        || $controller->formModel->added !== [[4], 7, 22]
        || ($controller->redirect['type'] ?? '') !== 'message') {
        throw new \RuntimeException('Authorized prediction round writes regressed.');
    }

    echo "Joomla 5/6 prediction round write permissions and model guards OK\n";
}
