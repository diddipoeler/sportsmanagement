<?php
/**
 * Verify reused round-robin schedules and instance-specific Club Icons assets.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Dispatcher {
    abstract class AbstractModuleDispatcher
    {
        public function __construct(private array $data, private object $app) {}
        protected function getLayoutData(): array|false { return $this->data; }
        public function getApplication(): object { return $this->app; }
    }
}

namespace Joomla\CMS\Helper {
    interface HelperFactoryAwareInterface {}
    trait HelperFactoryAwareTrait
    {
        public function getHelperFactory(): object
        {
            return new class {
                public function getHelper(string $name): object
                {
                    return new class {
                        public function getData(...$args): array
                        {
                            return ['teams' => [1 => ['name' => 'Team']]];
                        }
                    };
                }
            };
        }
    }
}

namespace Joomla\CMS\Document {
    class HtmlDocument
    {
        public function __construct(private object $assets) {}
        public function getWebAssetManager(): object { return $this->assets; }
    }
}

namespace {
    use Diddipoeler\Module\SportsManagementClubicons\Site\Dispatcher\Dispatcher;
    use Joomla\CMS\Document\HtmlDocument;

    define('_JEXEC', 1);
    define('JPATH_SITE', dirname(__DIR__, 2));
    require JPATH_SITE . '/admin/helpers/RRobin.class.php';
    require JPATH_SITE . '/modules/mod_sportsmanagement_clubicons/src/Dispatcher/Dispatcher.php';

    /**
     * Fail the standalone regression with a specific diagnostic when a contract is broken.
     *
     * @param bool $condition Whether the observed result matches the contract.
     * @param string $message Failure description.
     * @return void
     */
    function verify(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $generator = new \RRobin();
    verify($generator->create(range(1, 8)), 'Eight-team schedule failed');
    verify(count($generator->getSchedule()) === 7, 'Eight-team schedule must have seven rounds');
    verify($generator->create(range(1, 4)), 'Four-team schedule failed');
    $schedule = $generator->getSchedule();
    verify(count($schedule) === 3, 'Reused generator retained old rounds');
    verify((int) $generator->tot_games === 6, 'Four-team game count is wrong');
    $pairs = [];
    foreach ($schedule as $round) {
        verify(count($round) === 2, 'Four-team round must have two games');
        foreach ($round as $pair) {
            sort($pair);
            $pairs[] = implode('-', $pair);
        }
    }
    verify(count(array_unique($pairs)) === 6, 'Repeated or missing pairing');
    verify(!$generator->create([1, 2]), 'Invalid two-team input accepted');
    verify($generator->getSchedule() === $schedule, 'Invalid input replaced the previous schedule');

    $assets = new class {
        public array $styles = [];
        public array $inline = [];
        public int $uses = 0;
        public function assetExists(string $type, string $name): bool { return isset($this->styles[$name]); }
        public function registerStyle(string $name, string $uri, array $options): void { $this->styles[$name] = $uri; }
        public function useStyle(string $name): void { ++$this->uses; }
        public function addInlineStyle(string $css): void { $this->inline[] = $css; }
    };
    $document = new HtmlDocument($assets);
    $app = new class($document) {
        public function __construct(private HtmlDocument $document) {}
        public function isClient(string $name): bool { return $name === 'site'; }
        public function getLanguage(): object
        {
            return new class {
                public function load(...$args): void {}
            };
        }
        public function getDocument(): HtmlDocument { return $this->document; }
    };

    foreach ([[11, 30, 10], [12, 80, 50]] as [$id, $height, $zoom]) {
        $params = new class($height, $zoom) {
            public function __construct(private int $height, private int $zoom) {}
            public function get(string $name, mixed $default = null): mixed
            {
                return match ($name) {
                    'picture_height' => $this->height,
                    'max_width_after_mouse_over' => $this->zoom,
                    default => $default,
                };
            }
            public function set(string $name, mixed $value): void {}
        };
        $dispatcher = new Dispatcher(['module' => (object) ['id' => $id], 'params' => $params], $app);
        (new \ReflectionMethod($dispatcher, 'getLayoutData'))->invoke($dispatcher);
    }

    verify(count($assets->styles) === 1 && $assets->uses === 2, 'Shared stylesheet must register once and activate twice');
    verify(count($assets->inline) === 2, 'Each module needs its own inline rule');
    verify(str_contains($assets->inline[0], '#clubicons11 .img-zoom{')
        && str_contains($assets->inline[0], '#clubicons11 .img-zoom:hover{')
        && str_contains($assets->inline[0], 'height:30px')
        && str_contains($assets->inline[0], 'scale(1.1)')
        && !str_contains($assets->inline[0], '#clubicons12'), 'First module settings or selector are incorrect');
    verify(str_contains($assets->inline[1], '#clubicons12 .img-zoom{')
        && str_contains($assets->inline[1], '#clubicons12 .img-zoom:hover{')
        && str_contains($assets->inline[1], 'height:80px')
        && str_contains($assets->inline[1], 'scale(1.5)')
        && !str_contains($assets->inline[1], '#clubicons11'), 'Second module settings or selector are incorrect');

    echo "Round-robin reuse and Club Icons instance regressions OK\n";
}
