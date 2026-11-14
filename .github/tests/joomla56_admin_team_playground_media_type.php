<?php
/**
 * Joomla 5/6 backend team and playground image field type compatibility.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Component {
    final class ComponentHelper {
        public static mixed $tool = 'imageselect';

        /**
         * Return a minimal Joomla component parameter registry fixture.
         *
         * @param string $name Joomla component name.
         * @return object Parameter getter for image tool tests.
         */
        public static function getParams(string $name): object {
            return new class {
                /**
                 * Resolve component image tool settings or caller defaults.
                 *
                 * @param string $key Joomla parameter key.
                 * @param mixed $default Fallback value.
                 * @return mixed Selected tool or default.
                 */
                public function get(string $key, mixed $default = null): mixed {
                    return $key === 'cfg_which_media_tool' ? ComponentHelper::$tool : $default;
                }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    final class FakeSportsForm {
        public array $fields = ['picture' => ['type' => 'media']];

        /**
         * Return the requested picture field if defined in the test form.
         *
         * @param string $name Joomla XML field name.
         * @return object|null Form field or null when missing.
         */
        public function getField(string $name): ?object {
            return isset($this->fields[$name]) ? (object) $this->fields[$name] : null;
        }

        /**
         * Store native Joomla form field XML updates.
         *
         * @param string $name Form field identifier.
         * @param string $attribute Form XML attribute.
         * @param string $value Assigned attribute value.
         * @return void
         */
        public function setFieldAttribute(string $name, string $attribute, string $value): void {
            $this->fields[$name][$attribute] = $value;
        }
    }

    class SportsManagementAdminModel {
        public FakeSportsForm $form;
        public bool $fail = false;

        /**
         * Construct a test form in place of the Joomla XML form factory.
         */
        public function __construct() { $this->form = new FakeSportsForm(); }

        /**
         * Simulate Joomla AdminModel form resolution.
         *
         * @param array<string,mixed> $data Existing form data.
         * @param bool $loadData Whether to load saved form data.
         * @return FakeSportsForm|false Form instance or failure.
         */
        public function getForm($data = [], $loadData = true) {
            return $this->fail ? false : $this->form;
        }

        /**
         * Supply optional database metadata for team field length overrides.
         *
         * @return object Minimal Joomla database with no column overrides.
         */
        protected function getDatabase(): object {
            return new class {
                /**
                 * Expose Joomla's fake table prefix.
                 *
                 * @return string Table prefix.
                 */
                public function getPrefix(): string { return 'jos_'; }

                /**
                 * Return no dynamic field width overrides for the team table.
                 *
                 * @param string $table Prefix-expanded team SQL table.
                 * @param bool $types Whether SQL column types are requested.
                 * @return array<string,string> Empty column list.
                 */
                public function getTableColumns(string $table, bool $types = true): array { return []; }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/admin/src/Model/TeamModel.php';
    require dirname(__DIR__, 2) . '/admin/src/Model/PlaygroundModel.php';

    foreach ([
        [\Diddipoeler\Component\SportsManagement\Administrator\Model\TeamModel::class, 'team', 'teams'],
        [\Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundModel::class, 'playground', 'playgrounds'],
    ] as [$class, $source, $folder]) {
        // PlaygroundModel has constructor dependencies unrelated to the
        // picture field: the parent Joomla form contract can be tested alone.
        $model = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $model->form = new \Diddipoeler\Component\SportsManagement\Administrator\Model\FakeSportsForm();

        foreach ([
            ['imageselect', 'imageselect'],
            ['media', 'media'],
            ['', 'media'],
            ['0', 'media'],
            ['unexpected', 'media'],
            [null, 'media'],
        ] as [$input, $expected]) {
            \Joomla\CMS\Component\ComponentHelper::$tool = $input;
            $model->form = new \Diddipoeler\Component\SportsManagement\Administrator\Model\FakeSportsForm();

            $result = $model->getForm();
            if ($result !== $model->form
                || ($result->fields['picture']['type'] ?? '') !== $expected) {
                throw new \RuntimeException('Invalid Joomla ' . $source . ' picture control type.');
            }
        }

        $model->fail = true;
        if ($model->getForm() !== false) {
            throw new \RuntimeException('Failed ' . $source . ' form must not render.');
        }

        $xml = file_get_contents(dirname(__DIR__, 2) . '/admin/forms/' . $source . '.xml');
        if (!is_string($xml)
            || !preg_match('~<field\s+name="picture"[^>]*\btargetfolder="' . $folder . '"~', $xml)) {
            throw new \RuntimeException('Unsafe or missing ' . $source . ' image folder alias.');
        }
    }

    echo "Joomla 5/6 team and playground media field fallback: OK\n";
}
