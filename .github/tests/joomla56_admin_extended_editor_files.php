<?php
/**
 * Verify native Joomla 5/6 XML/PHP editor listings and safe MVC errors.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel
    {
        public array $errors = [];

        /**
         * Expose test model errors through Joomla's normal model API.
         *
         * @return array Current errors for the administrator view.
         */
        public function getErrors(): array { return $this->errors; }
    }
}

namespace Joomla\CMS\MVC\View {
    class HtmlView
    {
        public ?object $model = null;
        public bool $rendered = false;

        /**
         * Return the MVC model attached to the test view.
         *
         * @return object|null Configured view model, if present.
         */
        public function getModel(): ?object { return $this->model; }

        /**
         * Record that Joomla would render the administrator HTML layout.
         *
         * @param string|null $tpl Optional template override.
         * @return void
         */
        public function display($tpl = null): void { $this->rendered = true; }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Provide a stable generic administrator error for assertions.
         *
         * @param string $key Joomla translation lookup key.
         * @return string Harmless translated error.
         */
        public static function _(string $key): string
        {
            return $key === 'JERROR_AN_ERROR_HAS_OCCURRED' ? 'Generic administrator error.' : $key;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 1;
        public static array $records = [];

        /**
         * Retain private file/model error details outside the public view.
         *
         * @param string $message Internal diagnostics.
         * @param int $level Joomla logging priority.
         * @param string $category Component log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$records[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Toolbar {
    class ToolbarHelper
    {
        public static array $calls = [];

        /**
         * Simulate a Joomla administrator view title.
         *
         * @param string $label Translated view label.
         * @param string $icon Administrator toolbar icon name.
         * @return void
         */
        public static function title(string $label, string $icon): void { self::$calls[] = 'title'; }

        /**
         * Simulate Joomla's administrator back-navigation toolbar button.
         *
         * @param string $label Translated back button text.
         * @param string $url Route to the component control panel.
         * @return void
         */
        public static function back(string $label, string $url): void { self::$calls[] = 'back'; }
    }
}

namespace Joomla\CMS\Router {
    class Route
    {
        /**
         * Preserve administrator routes without bootstrapping Joomla.
         *
         * @param string $route Raw control-panel route.
         * @return string Unchanged route.
         */
        public static function _(string $route): string { return $route; }
    }
}

namespace {
    $root = sys_get_temp_dir() . '/sm56-xml-editors-' . bin2hex(random_bytes(6));
    define('JPATH_ADMINISTRATOR', $root);
    define('_JEXEC', 1);
    $dir = $root . '/components/com_sportsmanagement/assets/extended';
    $external = $root . '/private.xml';

    if (!mkdir($dir, 0700, true)) {
        throw new \RuntimeException('Could not create Joomla administrator XML fixture folder.');
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Model/SmextxmleditorsModel.php';
    require_once dirname(__DIR__, 2) . '/admin/src/View/Smextxmleditors/HtmlView.php';

    try {
        foreach (['club.xml', 'Project10.xml', 'Project2.PHP', 'index.html',
            'invalid..xml', '.hidden.xml', 'space name.xml'] as $name) {
            file_put_contents($dir . '/' . $name, 'test');
        }
        file_put_contents($external, 'secret');

        if (function_exists('symlink')) {
            @symlink($external, $dir . '/linked.xml');
        }

        $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SmextxmleditorsModel();
        $items = $model->getXMLFiles();

        if ($items !== ['club.xml', 'Project2.PHP', 'Project10.xml']) {
            throw new \RuntimeException('Unsafe XML editor file leaked to the backend list: ' . json_encode($items));
        }

        $class = \Diddipoeler\Component\SportsManagement\Administrator\View\Smextxmleditors\HtmlView::class;
        $missing = new $class();
        try {
            $missing->display();
            throw new \RuntimeException('XML editor rendered with an incorrect Joomla MVC model.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 500) { throw $e; }
        }

        $view = new $class();
        $view->model = $model;
        $view->display();

        if (!$view->rendered || $view->files !== $items
            || \Joomla\CMS\Toolbar\ToolbarHelper::$calls !== ['title', 'back']) {
            throw new \RuntimeException('Valid XML editor list did not render through Joomla.');
        }

        $model->errors = ['SQLSTATE password=private'];
        $failed = new $class();
        $failed->model = $model;
        try {
            $failed->display();
            throw new \RuntimeException('Private error leaked from administrator editor.');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'Generic administrator error.' || $e->getCode() !== 500
                || $failed->rendered) {
                throw $e;
            }
        }

        if (count(\Joomla\CMS\Log\Log::$records) !== 1
            || !str_contains(\Joomla\CMS\Log\Log::$records[0][0], 'password=private')) {
            throw new \RuntimeException('Administrator XML editor did not log private model details.');
        }
    } finally {
        foreach (new \DirectoryIterator($dir) as $entry) {
            if (!$entry->isDot()) {
                unlink($entry->getPathname());
            }
        }
        rmdir($dir);
        rmdir(dirname($dir));
        rmdir($root . '/components/com_sportsmanagement');
        rmdir($root . '/components');
        unlink($external);
        rmdir($root);
    }

    echo "Joomla 5/6 XML editor safe file listing and MVC view passed.\n";
}
