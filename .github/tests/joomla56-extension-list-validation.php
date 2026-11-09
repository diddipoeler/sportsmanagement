<?php
/**
 * Standalone Joomla 5/6 extension folder list configuration regression tests.
 *
 * Usage: php .github/tests/joomla56-extension-list-validation.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Form\Field {
    /** Minimal Joomla list field for isolated optional extension tests. */
    class ListField
    {
        protected array $element = [];

        /**
         * Set synthetic XML field configuration without a Joomla bootstrap.
         *
         * @param array<string,string> $settings Configured folder filter values.
         * @return void
         */
        public function __construct(array $settings = [])
        {
            $this->element = $settings;
        }

        /**
         * Return the inherited placeholder selector option.
         *
         * @return array<int,object> One fallback option.
         */
        protected function getOptions(): array
        {
            return [(object) ['value' => '', 'text' => 'Choose']];
        }
    }
}

namespace Joomla\Filesystem {
    /** Standalone folder reader that rejects unexpected provider invocations. */
    final class Folder
    {
        public static int $calls = 0;

        /**
         * Return representative allowed and malformed extension folders.
         *
         * @param string $path Component extension folder path.
         * @param string $filter Joomla regex folder-name filter.
         * @return array<int,mixed> Synthetic folder names.
         */
        public static function folders(string $path, string $filter): array
        {
            ++self::$calls;

            return ['normal_ext', 'bad/folder', '../secret', 'other-ext', 'blocked', ''];
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    $root = sys_get_temp_dir() . '/jsm-extensionlist-' . bin2hex(random_bytes(6));
    $dir = $root . '/components/com_sportsmanagement/extensions';

    if (!mkdir($dir, 0700, true) && !is_dir($dir)) {
        throw new \RuntimeException('Could not initialize extension folder test directory.');
    }

    \define('JPATH_ROOT', $root);
    require_once dirname(__DIR__, 2) . '/admin/src/Field/ExtensionlistField.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionlistField;
    use Joomla\Filesystem\Folder;

    try {
        $method = new \ReflectionMethod(ExtensionlistField::class, 'getOptions');

        $invalid = new ExtensionlistField(['filter' => '[unterminated']);
        $options = $method->invoke($invalid);

        if (count($options) !== 1 || Folder::$calls !== 0) {
            throw new \RuntimeException('Invalid filter reached the Joomla filesystem API.');
        }

        $valid = new ExtensionlistField(['filter' => '^[a-z]', 'exclude' => '^blocked$']);
        $options = $method->invoke($valid);
        $values = array_map(static fn (object $item): string => $item->value, $options);

        if ($values !== ['', 'normal_ext', 'other-ext'] || Folder::$calls !== 1) {
            throw new \RuntimeException('Extension directory choices were not filtered safely.');
        }

        $invalidExclude = new ExtensionlistField(['exclude' => '[unterminated']);
        $options = $method->invoke($invalidExclude);

        if (count($options) !== 4 || Folder::$calls !== 2) {
            throw new \RuntimeException('Invalid exclusion pattern must not suppress all extensions.');
        }
    } finally {
        rmdir($dir);
        rmdir(dirname($dir));
        rmdir(dirname(dirname($dir)));
        rmdir($root);
    }

    echo "Joomla 5/6 extension folder selector regressions passed.\n";
}
