<?php
/**
 * Regression contract for Joomla 5/6 administrator match timeline AJAX writes.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Controller {
    class AdminController
    {
        protected mixed $app = null;

        public function setTestApplication(object $app): void
        {
            $this->app = $app;
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Session {
    class Session
    {
        public static bool $valid = true;

        public static function checkToken(string $type): bool
        {
            return $type === 'post' && self::$valid;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 3;
        public static array $messages = [];

        public static function add(string $message, int $severity, string $category): void
        {
            self::$messages[] = compact('message', 'severity', 'category');
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $source = file_get_contents($root . '/admin/src/Controller/MatchesController.php');

    if (!is_string($source)) {
        throw new \RuntimeException('Missing administrator matches controller.');
    }

    foreach ([
        'removeEvent' => 'core.delete',
        'removeCommentary' => 'core.delete',
        'removeSubst' => 'core.delete',
        'saveevent' => 'core.edit',
        'savecomment' => 'core.edit',
        'savesubst' => 'core.edit',
    ] as $method => $permission) {
        $pattern = '~function\s+' . preg_quote($method, '~')
            . '\(\):\s*void\s*\{\s*'
            . '\$this->assertWritePermission\('
            . preg_quote("'" . $permission . "'", '~')
            . '\);~';

        if (preg_match($pattern, $source) !== 1) {
            throw new \RuntimeException('Missing match timeline CSRF/ACL: ' . $method);
        }
    }

    if (!str_contains($source, "\$this->app->getInput()->post->getInt('event_id')")
        || !str_contains($source, "\$this->app->getInput()->post->getInt('substid')")
        || substr_count($source, '$input = $this->app->getInput()->post;') < 3
    ) {
        throw new \RuntimeException('Match timeline writes must read POST inputs.');
    }

    require_once $root . '/admin/src/Controller/SportsManagementAdminController.php';
    require_once $root . '/admin/src/Controller/MatchesController.php';

    $identity = new class {
        public bool $granted = true;

        public function authorise(string $permission, string $component): bool
        {
            return $this->granted
                && $component === 'com_sportsmanagement'
                && in_array($permission, ['core.edit', 'core.delete'], true);
        }
    };

    $document = new class {
        public string $mime = '';

        public function setMimeEncoding(string $mime): void
        {
            $this->mime = $mime;
        }
    };

    $app = new class($identity, $document) {
        public array $headers = [];
        public int $closed = 0;

        public function __construct(private object $identity, private object $document)
        {
        }

        public function getIdentity(): object
        {
            return $this->identity;
        }

        public function getDocument(): object
        {
            return $this->document;
        }

        public function setHeader(string $name, string $value, bool $replace): void
        {
            $this->headers[$name] = $value;
        }

        public function close(): void
        {
            ++$this->closed;
        }
    };

    $controller = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\MatchesController::class
    ))->newInstanceWithoutConstructor();

    $controller->setTestApplication($app);

    $expectDenied = static function (string $method) use ($controller): void {
        try {
            $controller->{$method}();
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 403) {
                return;
            }
        }

        throw new \RuntimeException('Unauthorized timeline mutation: ' . $method);
    };

    foreach (['removeEvent', 'removeCommentary', 'removeSubst', 'saveevent', 'savecomment', 'savesubst'] as $method) {
        \Joomla\CMS\Session\Session::$valid = false;
        $expectDenied($method);
        \Joomla\CMS\Session\Session::$valid = true;
        $identity->granted = false;
        $expectDenied($method);
        $identity->granted = true;
    }

    $timeline = new \ReflectionMethod($controller, 'timelineAction');
    $privateMessage = 'mysql internal password=secret';
    $failure = $timeline->invoke(
        $controller,
        static function () use ($privateMessage): bool {
            throw new \RuntimeException($privateMessage);
        },
        'delete failed',
        'deleted',
        17
    );

    if ($failure !== '0&delete failed'
        || str_contains($failure, $privateMessage)
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0]['message'], $privateMessage)
    ) {
        throw new \RuntimeException('Timeline write must log failures without exposing internals.');
    }

    if ($timeline->invoke($controller, static fn (): bool => true, 'error', 'done', 17) !== '1&done&17') {
        throw new \RuntimeException('Timeline success response contract changed.');
    }

    ob_start();
    (new \ReflectionMethod($controller, 'jsonClose'))->invoke($controller, "1&Erfolgreich\u{00E4}");
    $json = ob_get_clean();

    if (json_decode($json, true, 512, JSON_THROW_ON_ERROR) !== "1&Erfolgreich\u{00E4}"
        || $app->closed !== 1
        || $document->mime !== 'application/json'
        || ($app->headers['Content-Type'] ?? '') !== 'application/json; charset=utf-8'
    ) {
        throw new \RuntimeException('Timeline JSON must retain a string response and UTF-8 MIME.');
    }

    $prediction = file_get_contents($root . '/site/src/Controller/PredictionentryController.php');

    if (!is_string($prediction)
        || substr_count($prediction, "Log::add(__METHOD__") < 2
        || str_contains($prediction, 'enqueueMessage($e->getMessage()')
        || !str_contains($prediction, "Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_5')")
        || !str_contains($prediction, "Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_3')")
    ) {
        throw new \RuntimeException('Frontend prediction writes must log internal exceptions privately.');
    }

    if (str_contains($source, 'enqueueMessage($e->getMessage()')
        || str_contains($source, '$msg .= $e->getMessage()')

    ) {
        throw new \RuntimeException('Administrator match write errors must not leak exceptions.');
    }

    echo "Joomla 5/6 match timeline POST, ACL, errors and JSON: OK\n";
}
