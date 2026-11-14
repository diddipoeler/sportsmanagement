<?php
/**
 * Joomla 5/6 player and league edit form image selector regression tests.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Form {
    final class Form {
        public static array $paths = [];

        /**
         * Record a form XML lookup directory as Joomla would.
         *
         * @param string $path Directory with component form XML.
         * @return void
         */
        public static function addFormPath(string $path): void { self::$paths[] = $path; }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper {
        public static mixed $mediaTool = 'imageselect';

        /**
         * Return the same registry interface as Joomla component parameters.
         *
         * @param string $component Component extension name.
         * @return object Test parameters.
         */
        public static function getParams(string $component): object {
            return new class {
                /**
                 * Return the media tool and configured person placeholder.
                 *
                 * @param string $key Component configuration key.
                 * @param mixed $default Fallback when not configured.
                 * @return mixed Configured or default setting.
                 */
                public function get(string $key, mixed $default = null): mixed {
                    return match ($key) {
                        'cfg_which_media_tool' => ComponentHelper::$mediaTool,
                        'ph_player' => 'images/com_sportsmanagement/database/persons/example.png',
                        default => $default
                    };
                }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    final class FakePlayerForm {
        public array $fields = ['picture' => ['type' => 'media']];

        /**
         * Locate the picture form field without loading a Joomla installation.
         *
         * @param string $name Requested Joomla form field.
         * @return object|null Existing form field or null.
         */
        public function getField(string $name): ?object {
            return isset($this->fields[$name]) ? (object) $this->fields[$name] : null;
        }

        /**
         * Persist native form field attribute changes in the test double.
         *
         * @param string $name Joomla form field name.
         * @param string $attribute XML form attribute.
         * @param string $value New attribute value.
         * @return void
         */
        public function setFieldAttribute(string $name, string $attribute, string $value): void {
            $this->fields[$name][$attribute] = $value;
        }
    }

    class SportsManagementAdminModel {
        public FakePlayerForm $form;
        public bool $failFormLoad = false;

        /**
         * Create a fake administrator form on model construction.
         */
        public function __construct() { $this->form = new FakePlayerForm(); }

        /**
         * Model Joomla loadForm for the expected player XML form.
         *
         * @param string $name Namespaced form name.
         * @param string $source XML source name.
         * @param array<string,mixed> $options Joomla form loading options.
         * @return FakePlayerForm|false The form or a simulated failure.
         */
        protected function loadForm(string $name, string $source, array $options): FakePlayerForm|false {
            if ($name !== 'com_sportsmanagement.player' || $source !== 'player'
                || ($options['control'] ?? '') !== 'jform') {
                throw new \RuntimeException('Player form source changed unexpectedly.');
            }
            return $this->failFormLoad ? false : $this->form;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_ADMINISTRATOR', '/joomla/administrator');

    require dirname(__DIR__, 2) . '/admin/src/Model/PlayerModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\PlayerModel();

    foreach ([
        ['imageselect', 'imageselect'],
        ['media', 'media'],
        ['', 'media'],
        ['0', 'media'],
        [null, 'media'],
        ['unsafe', 'media'],
    ] as [$config, $expected]) {
        \Joomla\CMS\Component\ComponentHelper::$mediaTool = $config;
        $model->form = new \Diddipoeler\Component\SportsManagement\Administrator\Model\FakePlayerForm();
        $result = $model->getForm();

        if ($result !== $model->form
            || ($result->fields['picture']['type'] ?? '') !== $expected
            || ($result->fields['picture']['targetfolder'] ?? '') !== 'persons'
            || ($result->fields['picture']['directory'] ?? '') !== 'com_sportsmanagement/database/persons'
            || ($result->fields['picture']['default'] ?? '') !== 'images/com_sportsmanagement/database/persons/example.png') {
            throw new \RuntimeException('The player editor did not configure a valid image field.');
        }
    }

    $model->failFormLoad = true;
    if ($model->getForm() !== false) {
        throw new \RuntimeException('Failed player XML form loading must return false.');
    }

    foreach (['player' => 'persons', 'league' => 'leagues'] as $file => $target) {
        $xml = file_get_contents(dirname(__DIR__, 2) . '/admin/forms/' . $file . '.xml');

        if (!is_string($xml) || !preg_match(
            '~<field\s+name="picture"[^>]*\btargetfolder="' . preg_quote($target, '~') . '"~',
            $xml
        )) {
            throw new \RuntimeException('Missing valid picture targetfolder in ' . $file . '.xml');
        }
    }

    echo "Joomla 5/6 player/league editor picture type and folder regression: OK\n";
}
