<?php
/**
 * Joomla 5/6 regression: legacy site controller tasks must render native views.
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
        protected $input;
        protected $default_view = '';

        /** @var array<int, array<string, mixed>> */
        public array $renderCalls = [];

        public function __construct(object $input)
        {
            $this->input = $input;
        }

        public function display($cachable = false, $urlparams = [])
        {
            $view = $this->input->getCmd('view', '');

            $this->renderCalls[] = [
                'view' => $view !== '' ? $view : $this->default_view,
                'cachable' => $cachable,
                'urlparams' => $urlparams,
            ];

            return $this;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Controller/EventsrankingController.php';
    require_once $root . '/site/src/Controller/JlxmlexportsController.php';
    require_once $root . '/site/controllers/eventsranking.php';
    require_once $root . '/site/controllers/jlxmlexports.php';

    $input = new class {
        public array $values = [];

        public function getCmd(string $key, string $default = ''): string
        {
            return (string) ($this->values[$key] ?? $default);
        }

        public function set(string $key, mixed $value): void
        {
            $this->values[$key] = $value;
        }
    };

    $eventsClass = \Diddipoeler\Component\SportsManagement\Site\Controller\EventsrankingController::class;
    $exportsClass = \Diddipoeler\Component\SportsManagement\Site\Controller\JlxmlexportsController::class;

    foreach ([
        'sportsmanagementControllerEventsRanking' => $eventsClass,
        'sportsmanagementControllerjlxmlexports' => $exportsClass,
    ] as $legacy => $native) {
        if (!class_exists($legacy) || !is_a($legacy, $native, true)) {
            throw new \RuntimeException('Legacy Joomla controller alias is missing: ' . $legacy);
        }
    }

    $input->set('view', 'eventsranking');
    $events = new $eventsClass($input);
    $result = $events->display(true, ['layout' => 'form']);
    if ($result !== $events || $events->renderCalls !== [[
        'view' => 'eventsranking',
        'cachable' => true,
        'urlparams' => ['layout' => 'form'],
    ]]) {
        throw new \RuntimeException('Events ranking display swallowed Joomla MVC rendering.');
    }

    $input->set('view', 'jlxmlexports');
    $exports = new $exportsClass($input);
    $result = $exports->display(false, ['format' => 'html']);
    if ($result !== $exports || $exports->renderCalls !== [[
        'view' => 'jlxmlexports',
        'cachable' => false,
        'urlparams' => ['format' => 'html'],
    ]]) {
        throw new \RuntimeException('XML exports display swallowed Joomla MVC rendering.');
    }

    // Old menu and task links invoke jlxmlexports.showranking without a view.
    $exports->renderCalls = [];
    $input->set('view', '');
    $exports->showranking();
    if ($input->getCmd('view') !== 'jlxmlexports'
        || $exports->renderCalls !== [[
            'view' => 'jlxmlexports',
            'cachable' => false,
            'urlparams' => [],
        ]]) {
        throw new \RuntimeException('Legacy XML export task did not select and render its native view.');
    }

    echo "Joomla 5/6 native site view rendering and legacy controller tasks OK\n";
}
