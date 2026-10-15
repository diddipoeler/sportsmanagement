<?php
/**
 * Joomla 5/6 regression for frontend result-write IDs and safe redirect values.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected $app;
        protected $input;
        public array $redirects = [];

        public function __construct($config = [], $factory = null, $app = null, $input = null)
        {
            $this->app = $app;
            $this->input = $input;
        }

        public function setRedirect(string $url, string $message = '', string $type = ''): void
        {
            $this->redirects[] = [$url, $message, $type];
        }

        public function getModel(string $name = ''): object
        {
            return new \Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel();
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $value): string { return $value; }
    }
}

namespace Joomla\CMS\Session {
    class Session
    {
        public static bool $valid = true;
        public static function checkToken(string $method = 'post'): bool
        {
            return $method === 'post' && self::$valid;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    class SiteRouteHelper
    {
        public static array $lastParameters = [];
        public static function view(string $view, array $parameters = []): string
        {
            self::$lastParameters = $parameters;
            return 'index.php?view=' . $view;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class ResultsAccessModel
    {
        public static array $checks = [];
        public int $selector = -1;
        public int $projectId = -1;

        public function setDatabaseSelector(int $selector): void { $this->selector = $selector; }
        public function setProjectId(int $projectId): void { $this->projectId = $projectId; }

        public function canEditMatches(array $ids): bool
        {
            self::$checks[] = [$this->selector, $this->projectId, $ids];
            return true;
        }
    }

    class ResultsEditModel
    {
        public static array $writes = [];
        public int $selector = -1;
        public function setDatabaseSelector(int $selector): void { $this->selector = $selector; }
        public function saveShort(array $data, array $ids): bool
        {
            self::$writes[] = [$this->selector, $data, $ids];
            return true;
        }
    }

    class EditmatchModel
    {
        public static array $writes = [];
        public int $selector = -1;
        public function setDatabaseSelector(int $selector): void { $this->selector = $selector; }
        public function updateReferees(array $data): bool
        {
            self::$writes[] = [$this->selector, $data];
            return true;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Controller/ResultsController.php';

    use Diddipoeler\Component\SportsManagement\Site\Controller\ResultsController;
    use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
    use Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel;
    use Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel;
    use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;

    $input = new class {
        public object $post;
        public function __construct()
        {
            $this->post = new class {
                public array $data = [];
                public mixed $selection = [];
                public function getArray(): array { return $this->data; }
                public function get(string $key, mixed $default = null, string $filter = ''): mixed
                {
                    return $key === 'cid' ? $this->selection : $default;
                }
            };
        }

        public function getCmd(string $key, string $default = ''): string { return $default; }
        public function getInt(string $key, int $default = 0): int { return $default; }
    };

    $app = new class {
        public array $messages = [];
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    $controller = new ResultsController([], null, $app, $input);
    $validator = new \ReflectionMethod(ResultsController::class, 'validatedId');
    foreach ([[12, false, 12], ['12', false, 12], [0, true, 0], ['0', true, 0],
              ['12junk', false, null], ['012', false, null], ['1e3', false, null],
              [[12], false, null], [true, false, null], [-1, true, null],
              ['99999999999999999999999999999', false, null], ['0', false, null]] as [$value, $zero, $expected]) {
        if ($validator->invoke(null, $value, $zero) !== $expected) {
            throw new \RuntimeException('Malformed result-write identifier accepted.');
        }
    }

    $validIds = new \ReflectionMethod(ResultsController::class, 'validMatchIds');
    if ($validIds->invoke(null, ['11', '12', '11']) !== [11, 12]
        || $validIds->invoke(null, ['11junk']) !== []) {
        throw new \RuntimeException('Result checkbox identifiers are not strictly validated.');
    }

    $goodRefereePost = ['id' => '11', 'project_id' => '71', 'cfg_which_database' => '0'];
    foreach ([
        ['id' => ['11']],
        ['id' => '11bad'],
        ['project_id' => '71bad'],
        ['cfg_which_database' => [0]],
        ['cfg_which_database' => '1garbage'],
    ] as $tamper) {
        $input->post->data = array_replace($goodRefereePost, $tamper);
        $beforeChecks = count(ResultsAccessModel::$checks);
        $controller->saveReferees();
        if (count(ResultsAccessModel::$checks) !== $beforeChecks
            || EditmatchModel::$writes !== []
            || end($controller->redirects)[2] !== 'error') {
            throw new \RuntimeException('Invalid referee POST reached an authorized write.');
        }
    }

    $input->post->data = $goodRefereePost;
    $controller->saveReferees();
    if (ResultsAccessModel::$checks[0] !== [0, 71, [11]]
        || EditmatchModel::$writes[0][0] !== 0
        || EditmatchModel::$writes[0][1]['id'] !== 11) {
        throw new \RuntimeException('Valid referee edits were not passed to the native model.');
    }

    $validShortPost = ['p' => '71', 'cfg_which_database' => '0'];
    foreach ([
        [$validShortPost, '11'],
        [array_replace($validShortPost, ['p' => '71garbage']), ['11']],
        [array_replace($validShortPost, ['cfg_which_database' => '0garbage']), ['11']],
    ] as [$data, $selection]) {
        $input->post->data = $data;
        $input->post->selection = $selection;
        $beforeChecks = count(ResultsAccessModel::$checks);
        $controller->saveshort();
        if (count(ResultsAccessModel::$checks) !== $beforeChecks
            || ResultsEditModel::$writes !== []) {
            throw new \RuntimeException('Malformed compact result POST reached the writer.');
        }
    }

    $input->post->data = $validShortPost;
    $input->post->selection = ['11', '12'];
    $controller->saveshort();
    if (end(ResultsAccessModel::$checks) !== [0, 71, [11, 12]]
        || ResultsEditModel::$writes[0][2] !== [11, 12]) {
        throw new \RuntimeException('Valid compact result submission was rejected.');
    }

    $input->post->data = [
        'id' => [11], 'project_id' => 71,
        'cfg_which_database' => 0,
        'layout' => ['evil'],
        's' => ['untrusted'], 'p' => new \stdClass(),
        'division' => ['untrusted'],
    ];
    $controller->saveReferees();
    if (SiteRouteHelper::$lastParameters['s'] !== ''
        || SiteRouteHelper::$lastParameters['p'] !== ''
        || SiteRouteHelper::$lastParameters['division'] !== ''
        || SiteRouteHelper::$lastParameters['layout'] !== 'form') {
        throw new \RuntimeException('Invalid POST redirect parameters were forwarded.');
    }

    \Joomla\CMS\Session\Session::$valid = false;
    try {
        $controller->saveshort();
        throw new \RuntimeException('Invalid CSRF token was accepted.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) {
            throw $error;
        }
    }

    echo "Joomla 5/6 frontend result-write ID and redirect validation OK\n";
}
