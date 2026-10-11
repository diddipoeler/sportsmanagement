<?php
/**
 * Joomla 5/6 frontend match AJAX CSRF/ACL and match-slider regression checks.
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
        protected mixed $app = null;
        protected mixed $input = null;

        public function setTestContext(object $app, object $input): void
        {
            $this->app = $app;
            $this->input = $input;
        }
    }
}

namespace Joomla\CMS\Session {
    class Session
    {
        public static bool $valid = true;

        public static function checkToken(string $method): bool
        {
            return $method === 'post' && self::$valid;
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

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $source = file_get_contents($root . '/site/src/Controller/MatchesController.php');

    if (!is_string($source)) {
        throw new \RuntimeException('Frontend timeline controller unavailable.');
    }

    foreach (['saveevent', 'savesubst', 'removeSubst', 'savecomment', 'removeEvent', 'removeCommentary'] as $method) {
        $pattern = '~function\s+' . preg_quote($method, '~')
            . '\(\):\s*void\s*\{\s*\$this->assertWritePermission\(\);~';

        if (preg_match($pattern, $source) !== 1) {
            throw new \RuntimeException('Missing frontend timeline write guard: ' . $method);
        }
    }

    foreach ([
        "\$input = \$this->input->post;",
        "\$this->input->post->getInt('event_id'",
        "\$this->input->post->getInt('substid'",
    ] as $required) {
        if (!str_contains($source, $required)) {
            throw new \RuntimeException('Frontend timeline must parse POST data: ' . $required);
        }
    }

    require_once $root . '/site/src/Controller/MatchesController.php';

    $identity = new class {
        public bool $allowed = true;

        public function authorise(string $action, string $component): bool
        {
            return $action === 'core.edit'
                && $component === 'com_sportsmanagement'
                && $this->allowed;
        }
    };

    $app = new class($identity) {
        public array $headers = [];
        public int $closes = 0;

        public function __construct(private object $identity)
        {
        }

        public function getIdentity(): object
        {
            return $this->identity;
        }

        public function setHeader(string $name, string $value, bool $replace): void
        {
            $this->headers[$name] = $value;
        }

        public function close(): void
        {
            ++$this->closes;
        }
    };

    $input = new class {
        public mixed $post = null;

        public function __construct()
        {
            $this->post = $this;
        }

        public function getInt(string $name): int
        {
            return 4;
        }
    };

    $controller = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Site\Controller\MatchesController::class
    ))->newInstanceWithoutConstructor();
    $controller->setTestContext($app, $input);

    $mustDeny = static function (string $method) use ($controller): void {
        try {
            $controller->{$method}();
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 403) {
                return;
            }
        }

        throw new \RuntimeException('Frontend timeline allowed an unauthorized write: ' . $method);
    };

    foreach (['saveevent', 'savesubst', 'removeSubst', 'savecomment', 'removeEvent', 'removeCommentary'] as $method) {
        \Joomla\CMS\Session\Session::$valid = false;
        $mustDeny($method);
        \Joomla\CMS\Session\Session::$valid = true;
        $identity->allowed = false;
        $mustDeny($method);
        $identity->allowed = true;
    }

    ob_start();
    (new \ReflectionMethod($controller, 'sendLegacyJson'))->invoke($controller, '1&Änderung gespeichert');
    $json = ob_get_clean();

    if (json_decode($json, true, 512, JSON_THROW_ON_ERROR) !== '1&Änderung gespeichert'
        || ($app->headers['Content-Type'] ?? '') !== 'application/json; charset=utf-8'
        || $app->closes !== 1
    ) {
        throw new \RuntimeException('Frontend timeline JSON contract changed.');
    }

    require_once $root . '/modules/mod_sportsmanagement_matchesslider/src/Helper/MatchesSliderHelper.php';

    $slider = new \Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper();
    $ids = new \ReflectionMethod($slider, 'ids');
    $parsed = $ids->invoke(
        $slider,
        ['1,2;3', '03', '4junk', '0', '-3', '99999999999999999999999999', ['5'], '6']
    );

    if ($parsed !== [1, 2, 3, 6]) {
        throw new \RuntimeException('Slider selection accepted malformed numeric IDs: ' . json_encode($parsed));
    }

    $media = new \ReflectionMethod($slider, 'mediaUrl');
    $prefix = 'https://joomla.example/media/';

    foreach ([
        'images/logo.png' => 'https://joomla.example/media/images/logo.png',
        'https://example.org/logo.png' => 'https://example.org/logo.png',
        'http://example.org/logo.png' => 'http://example.org/logo.png',
        '../secrets/logo.png' => '',
        'images/../../private.png' => '',
        '//outside.example/logo.png' => '',
        'javascript:alert(1)' => '',
        'data:image/svg+xml,evil' => '',
        'https://example.org/logo.png' . "\n" . 'fake' => '',
    ] as $path => $expected) {
        if ($media->invoke($slider, $path, $prefix) !== $expected) {
            throw new \RuntimeException('Slider accepted invalid media path: ' . json_encode($path));
        }
    }

    echo "Joomla 5/6 frontend match writes and match-slider inputs: OK\n";
}
