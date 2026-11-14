<?php
/**
 * Isolated Joomla 5/6 administrator image-import CSRF, ACL and privacy tests.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return Joomla's translated language key as test-safe placeholder.
         *
         * @param string $key Joomla message key.
         * @return string Deterministic visible message.
         */
        public static function _(string $key): string { return $key; }

        /**
         * Render a deterministic translated diagnostic with arguments.
         *
         * @param string $key Joomla language key.
         * @param mixed ...$arguments Translation placeholders.
         * @return string Stable test translation.
         */
        public static function sprintf(string $key, mixed ...$arguments): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Record private error messages in memory without disclosing them.
         *
         * @param string $message Diagnostic recorded by the model.
         * @param int $severity Joomla log severity.
         * @param string $category Log category for image imports.
         * @return void
         */
        public static function add(string $message, int $severity, string $category): void
        {
            self::$entries[] = [$message, $severity, $category];
        }
    }
}

namespace Joomla\CMS\Session {
    final class Session
    {
        public static bool $valid = true;

        /**
         * Simulate a validated or rejected administrator POST CSRF token.
         *
         * @param string $method Joomla HTTP method whose token is checked.
         * @return bool Whether this request has a valid token.
         */
        public static function checkToken(string $method): bool { return self::$valid; }
    }
}

namespace Joomla\Filesystem {
    final class File
    {
        /**
         * Indicate there is no temporary archive to remove after an HTTP error.
         *
         * @param string $path Candidate downloaded ZIP file.
         * @return bool False for this isolated fixture.
         */
        public static function exists(string $path): bool { return false; }

        /**
         * Record the cleanup result without modifying real filesystem data.
         *
         * @param string $path ZIP file to remove.
         * @return bool True for the fake cleanup.
         */
        public static function delete(string $path): bool { return true; }
    }

    final class Folder {}

    final class Path
    {
        /**
         * Return placeholder temporary folder permissions for error handling.
         *
         * @param string $path Directory whose permissions are requested.
         * @return string Simulated permission mode.
         */
        public static function getPermissions(string $path): string { return '0777'; }
    }
}

namespace Joomla\Http {
    final class HttpFactory
    {
        public static int $requests = 0;

        /**
         * Return an HTTP stub that throws a credential-bearing network error.
         *
         * @param array<string,mixed> $options Joomla HTTP options.
         * @param array<int,string> $adapters Supported Joomla HTTP adapters.
         * @return object Client with a simulated failing get() method.
         */
        public static function getHttp(array $options = [], array $adapters = []): object
        {
            return new class {
                /**
                 * Simulate a remote package server returning a secret error.
                 *
                 * @param string $url Requested image package URL.
                 * @return never
                 * @throws \RuntimeException For the private network failure.
                 */
                public function get(string $url): never
                {
                    ++HttpFactory::$requests;
                    throw new \RuntimeException('SQLSTATE password=image-secret token=image-secret');
                }
            };
        }
    }
}

namespace Joomla\Archive {
    final class Archive {}
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    final class PicturesTable
    {
        /**
         * Provide a Joomla table stub without opening a real connection.
         *
         * @param object $database Test database service.
         */
        public function __construct(object $database) {}
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        private string $error = '';

        /**
         * Resolve the fake Joomla administrator service used by the import.
         *
         * @return object In-memory Joomla administrator application fixture.
         */
        protected function administratorApplication(): object { return $GLOBALS['smImageImportApp']; }

        /**
         * Return an inert database dependency for the model.
         *
         * @return object Fake Joomla database service.
         */
        protected function getDatabase(): object { return new \stdClass(); }

        /**
         * Store a model failure without requiring Joomla MVC internals.
         *
         * @param string $message Human-readable failure message.
         * @return void
         */
        public function setError(string $message): void { $this->error = $message; }

        /**
         * Read the recorded model error for the image import controller.
         *
         * @return string Model error message or empty string.
         */
        public function getError(): string { return $this->error; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController
    {
        protected object $app;
        public int $modelCalls = 0;
        public string $redirect = '';

        /**
         * Inject the test Joomla administrator application into the controller.
         *
         * @param object $app Simulated Joomla administrator application.
         */
        public function __construct(object $app) { $this->app = $app; }

        /**
         * Return the fake import model after recording a Joomla MVC lookup.
         *
         * @param string $name Requested model name.
         * @param string $prefix Administrator MVC model namespace.
         * @param array<string,mixed> $config Joomla model creation settings.
         * @return object Test image import model.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            ++$this->modelCalls;
            return $GLOBALS['smImageImportModel'];
        }

        /**
         * Capture the administrator redirect after an import attempt.
         *
         * @param string $url Component import list route.
         * @return void
         */
        public function setRedirect(string $url): void { $this->redirect = $url; }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $site = sys_get_temp_dir() . '/sm-image-import-' . bin2hex(random_bytes(6));
    mkdir($site . '/tmp', 0777, true);
    define('JPATH_SITE', $site);

    $app = new class {
        public ?object $identity = null;
        public array $postData = [];
        public array $messages = [];

        /**
         * Return the current simulated Joomla administrator identity.
         *
         * @return object|null Allowed user, denied user or absent identity.
         */
        public function getIdentity(): ?object { return $this->identity; }

        /**
         * Provide Joomla's POST getArray() form input interface.
         *
         * @return object Input fixture with a nested post object.
         */
        public function getInput(): object
        {
            return new class($this) {
                public object $post;

                /**
                 * Create a nested post object backed by the application values.
                 *
                 * @param object $app Shared request fixture.
                 */
                public function __construct(object $app)
                {
                    $this->post = new class($app) {
                        /**
                         * Retain the current posted form values by reference.
                         *
                         * @param object $app Application fixture.
                         */
                        public function __construct(private object $app) {}

                        /**
                         * Return raw POST data as Joomla's getArray() does.
                         *
                         * @return array<string,mixed> Current submitted fields.
                         */
                        public function getArray(): array { return $this->app->postData; }
                    };
                }
            };
        }

        /**
         * Capture a message shown in the Joomla administrator interface.
         *
         * @param string $message Text displayed in the backend.
         * @param string $type Notice severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    $GLOBALS['smImageImportApp'] = $app;
    require $root . '/admin/src/Model/SmimageimportModel.php';
    require $root . '/admin/src/Controller/SmimageimportsController.php';

    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SmimageimportModel();
    $GLOBALS['smImageImportModel'] = $model;
    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\SmimageimportsController($app);

    try {
        $parse = new \ReflectionMethod($model, 'selectedIds');
        $ids = $parse->invoke(null, [
            [1], true, '7junk', '0007', 7, '0000', -1, 13,
            '9223372036854775808',
        ]);

        if ($ids !== [7, 13]) {
            throw new \RuntimeException('Invalid image record IDs were coerced into a selection.');
        }

        \Joomla\CMS\Session\Session::$valid = false;
        try {
            $controller->import();
            throw new \RuntimeException('An invalid CSRF token permitted an image import.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) { throw $error; }
        }

        \Joomla\CMS\Session\Session::$valid = true;

        foreach ([null, new class {
            /**
             * Deny a Joomla component creation permission in this fixture.
             *
             * @param string $action Joomla ACL action.
             * @param string $asset Joomla extension permission target.
             * @return bool Denied by this test identity.
             */
            public function authorise(string $action, string $asset): bool { return false; }
        }] as $identity) {
            $app->identity = $identity;
            try {
                $controller->import();
                throw new \RuntimeException('A missing or denied identity imported an image.');
            } catch (\RuntimeException $error) {
                if ($error->getCode() !== 403) { throw $error; }
            }
        }

        if ($controller->modelCalls !== 0 || \Joomla\Http\HttpFactory::$requests !== 0) {
            throw new \RuntimeException('Unauthorised image import reached the MVC model or network.');
        }

        $app->identity = new class {
            /**
             * Allow only the requested SportsManagement image-create action.
             *
             * @param string $action Joomla ACL action.
             * @param string $asset Joomla permission target.
             * @return bool Whether the expected permission was requested.
             */
            public function authorise(string $action, string $asset): bool
            {
                return $action === 'core.create' && $asset === 'com_sportsmanagement';
            }
        };

        $app->postData = [
            'cid' => [7],
            'picture' => [7 => 'Logo'],
            'folder' => [7 => 'teams'],
            'directory' => [7 => 'logos'],
            'file' => [7 => ['nested.zip']],
        ];

        set_error_handler(static function (int $severity, string $message): never {
            throw new \RuntimeException('Image form generated PHP warning: ' . $message);
        });

        try {
            if ($model->import() !== false || \Joomla\Http\HttpFactory::$requests !== 0) {
                throw new \RuntimeException('Nested archive file value initiated a download.');
            }
        } finally {
            restore_error_handler();
        }

        $app->postData['file'][7] = 'logo.zip';
        $result = $model->import();

        if ($result !== false
            || $model->getError() !== 'JERROR_AN_ERROR_HAS_OCCURRED'
            || \Joomla\Http\HttpFactory::$requests !== 1
            || count(\Joomla\CMS\Log\Log::$entries) !== 1
            || !str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'RuntimeException')
            || str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'image-secret')) {
            throw new \RuntimeException('Image download failure leaked secret diagnostics.');
        }

        $controller->import();

        if ($controller->redirect !== 'index.php?option=com_sportsmanagement&view=smimageimports'
            || \Joomla\Http\HttpFactory::$requests !== 2
            || count(\Joomla\CMS\Log\Log::$entries) !== 2) {
            throw new \RuntimeException('Image import controller failed to handle a model error.');
        }

        foreach ($app->messages as [$message, $type]) {
            if (str_contains($message, 'image-secret')
                || str_contains($message, 'SQLSTATE')) {
                throw new \RuntimeException('Image import exposed internal HTTP or SQL details.');
            }
        }

        echo "Joomla 5/6 image import selection, CSRF, ACL and private diagnostics: OK\n";
    } finally {
        rmdir($site . '/tmp');
        rmdir($site);
    }
}
