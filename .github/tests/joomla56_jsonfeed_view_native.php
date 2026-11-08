<?php
/**
 * Test native Joomla 5/6 JSON feed view dispatch and model validation.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\View {
    class HtmlView
    {
        public ?object $model = null;
        public string $layout = 'default';
        public bool $rendered = false;

        /** Return the model attached to the stand-in Joomla view. */
        public function getModel(): ?object { return $this->model; }

        /** Record the layout selected by the real JSON feed view. */
        public function setLayout(string $layout): void { $this->layout = $layout; }

        /** Record that the native view delegated rendering to Joomla. */
        public function display($tpl = null): void { $this->rendered = true; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class JsonfeedModel
    {
        /** Supply a predictable event to the view without Joomla services. */
        public function getGoogleCalendarFeeds(): array
        {
            return [['id' => 'event-1', 'gcid' => 5]];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementSiteApplicationResolver
    {
        public static int $compact = 0;

        /** Return a frontend request stub with the current compact option. */
        public static function resolve(): object
        {
            return new class {
                /** Provide the input values for the JSON feed layout test. */
                public function getInput(): object
                {
                    return new class {
                        /** Read the simulated filtered compact integer. */
                        public function getInt(string $name, int $default = 0): int
                        {
                            return $name === 'compact'
                                ? SportsManagementSiteApplicationResolver::$compact
                                : $default;
                        }
                    };
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/View/Jsonfeed/RawView.php';

    $class = \Diddipoeler\Component\SportsManagement\Site\View\Jsonfeed\RawView::class;
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver::class;

    $missing = new $class();
    try {
        $missing->display();
        throw new \RuntimeException('JSON feed accepted a missing MVC model.');
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() !== 500) {
            throw $exception;
        }
    }

    foreach ([[0, 'default'], [1, 'module'], [7, 'default']] as [$compact, $layout]) {
        $resolver::$compact = $compact;
        $view = new $class();
        $view->model = new \Diddipoeler\Component\SportsManagement\Site\Model\JsonfeedModel();
        $view->display();

        if (!$view->rendered || $view->layout !== $layout
            || $view->events !== [['id' => 'event-1', 'gcid' => 5]]) {
            throw new \RuntimeException('Native JSON feed model or layout resolution regressed.');
        }
    }

    echo "Joomla 5/6 native JSON feed view dispatch passed.\n";
}
