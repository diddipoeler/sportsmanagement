<?php
/**
 * Regression for Joomla 5/6 club editor configurable image folder fields.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static string $mediaTool = 'imageselect';

        /**
         * Return fake component options for the club image tool.
         *
         * @param string $extension Joomla component whose settings are read.
         * @return object Registry-like parameters getter.
         */
        public static function getParams(string $extension): object
        {
            return new class {
                /**
                 * Read a configured media field type and preserve all other defaults.
                 *
                 * @param string $name Joomla component option key.
                 * @param mixed $default Default value when option is absent.
                 * @return mixed Image tool setting or passed default.
                 */
                public function get(string $name, mixed $default = null): mixed
                {
                    return $name === 'cfg_which_media_tool'
                        ? ComponentHelper::$mediaTool
                        : $default;
                }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    final class ClubTestForm
    {
        public array $fields = [
            'logo_big' => ['type' => 'media'],
            'logo_middle' => ['type' => 'hidden'],
            'logo_small' => ['type' => 'hidden'],
            'trikot_home' => ['type' => 'media'],
            'trikot_away' => ['type' => 'media'],
        ];

        /**
         * Locate an existing Joomla club logo or shirt form field.
         *
         * @param string $name Club form field name.
         * @return object|null Present field or null.
         */
        public function getField(string $name): ?object
        {
            return isset($this->fields[$name]) ? (object) $this->fields[$name] : null;
        }

        /**
         * Record changes to the field XML attributes made by ClubModel.
         *
         * @param string $field Club form field name.
         * @param string $attribute XML field attribute to update.
         * @param string $value New field attribute value.
         * @return bool Whether the field exists and was modified.
         */
        public function setFieldAttribute(string $field, string $attribute, string $value): bool
        {
            if (!isset($this->fields[$field])) {
                return false;
            }

            $this->fields[$field][$attribute] = $value;
            return true;
        }

        /**
         * Supply the existing club identifier without touching a database.
         *
         * @param string $field Requested Joomla form field.
         * @return int Zero for new club fixture.
         */
        public function getValue(string $field): int { return 0; }
    }

    class SportsManagementAdminModel
    {
        public ClubTestForm $clubForm;

        /**
         * Create a lightweight Joomla club model form fixture.
         */
        public function __construct()
        {
            $this->clubForm = new ClubTestForm();
        }

        /**
         * Provide the component's existing form to the native ClubModel.
         *
         * @param array<string,mixed> $data Form values requested by Joomla.
         * @param bool $loadData Whether to load existing database data.
         * @return ClubTestForm Mutable club edit form.
         */
        public function getForm($data = [], $loadData = true)
        {
            return $this->clubForm;
        }

        /**
         * Return a fake Joomla database used for optional field-length detection.
         *
         * @return object Database exposing prefix and column metadata.
         */
        protected function getDatabase(): object
        {
            return new class {
                /**
                 * Supply the Joomla table prefix for harmless metadata reads.
                 *
                 * @return string Test site database table prefix.
                 */
                public function getPrefix(): string { return 'jos_'; }

                /**
                 * Return no database column overrides for the club edit form.
                 *
                 * @param string $name Requested Joomla prefixed table name.
                 * @param bool $typeOnly Whether to return column SQL types.
                 * @return array<string,string> Empty test column mapping.
                 */
                public function getTableColumns(string $name, bool $typeOnly = true): array { return []; }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);

    require dirname(__DIR__, 2) . '/admin/src/Model/ClubModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\ClubModel();
    $expected = [
        'logo_big' => ['clubs_large', 'clubs/large'],
        'logo_middle' => ['clubs_medium', 'clubs/medium'],
        'logo_small' => ['clubs_small', 'clubs/small'],
        'trikot_home' => ['clubkits_home', 'clubs/trikot_home'],
        'trikot_away' => ['clubkits_away', 'clubs/trikot_away'],
    ];

    foreach (['imageselect', 'media'] as $mediaTool) {
        \Joomla\CMS\Component\ComponentHelper::$mediaTool = $mediaTool;
        $model->clubForm = new \Diddipoeler\Component\SportsManagement\Administrator\Model\ClubTestForm();
        $form = $model->getForm();

        foreach ($expected as $name => [$target, $folder]) {
            $attributes = $form->fields[$name] ?? [];
            if (($attributes['type'] ?? null) !== $mediaTool
                || ($attributes['targetfolder'] ?? null) !== $target
                || ($attributes['directory'] ?? null)
                    !== 'images/com_sportsmanagement/database/' . $folder) {
                throw new \RuntimeException(
                    'Club editor missing valid ' . $mediaTool . ' target folder for ' . $name
                );
            }
        }
    }

    // The XML form must define safe targets even before dynamic model changes.
    $xml = file_get_contents(dirname(__DIR__, 2) . '/admin/forms/club.xml');
    if ($xml === false) {
        throw new \RuntimeException('Club XML form cannot be checked.');
    }
    foreach ($expected as $field => [$target, $folder]) {
        $pattern = '~<field\s+name="' . preg_quote($field, '~')
            . '"[^>]*\btargetfolder="' . preg_quote($target, '~') . '"~';
        if (preg_match($pattern, $xml) !== 1) {
            throw new \RuntimeException('Club XML form omitted folder alias for ' . $field);
        }
    }

    echo "Joomla 5/6 club edit image selector has valid logo/trikot folder types: OK\n";
}
