<?php
/**
 * Verify safe Joomla 5/6 event-type icon form-field configuration.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public static mixed $form = null;

        /**
         * Supply the configured Joomla form without bootstrapping Joomla.
         *
         * @param array $data Current edit-form values.
         * @param bool $loadData Whether Joomla would load the stored record.
         * @return mixed Fake form instance or false.
         */
        public function getForm($data = [], $loadData = true)
        {
            return self::$form;
        }
    }
}

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        public static mixed $mediaType = 'media';
        public static mixed $defaultIcon = '';

        /**
         * Return fake component settings for the event-type form.
         *
         * @param string $component Requested Joomla component.
         * @return object Registry stand-in for two icon parameters.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Read the simulated component media and icon settings.
                 *
                 * @param string $key Requested settings key.
                 * @param mixed $default Fallback value.
                 * @return mixed Configured Joomla component value.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return match ($key) {
                        'cfg_which_media_tool' => ComponentHelper::$mediaType,
                        'ph_icon' => ComponentHelper::$defaultIcon,
                        default => $default,
                    };
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/EventtypeModel.php';

    class EventtypeForm
    {
        public array $attributes = [];

        /**
         * Capture the field configuration Joomla would apply to its form.
         *
         * @param string $name Target form field.
         * @param string $attribute Property name on the icon field.
         * @param string $value New field property value.
         * @return void
         */
        public function setFieldAttribute(string $name, string $attribute, string $value): void
        {
            $this->attributes[$name][$attribute] = $value;
        }
    }

    $base = \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class;
    $settings = \Joomla\CMS\Component\ComponentHelper::class;
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\EventtypeModel();

    $base::$form = false;
    if ($model->getForm() !== false) {
        throw new \RuntimeException('An unavailable Joomla form was not rejected.');
    }

    foreach ([
        ['media', 'media'],
        [' custom_media ', 'custom_media'],
        [['media'], 'media'],
        [new \stdClass(), 'media'],
        ['0003', 'media'],
        ['../../../wrong', 'media'],
        ['file' . "\n" . 'injection', 'media'],
        [str_repeat('a', 65), 'media'],
        [true, 'media'],
    ] as [$configured, $expected]) {
        $settings::$mediaType = $configured;
        $settings::$defaultIcon = 'images/events/sport.png';
        $form = new EventtypeForm();
        $base::$form = $form;

        if ($model->getForm() !== $form
            || ($form->attributes['icon']['type'] ?? null) !== $expected
            || ($form->attributes['icon']['directory'] ?? null) !== 'com_sportsmanagement/database/events'
            || ($form->attributes['icon']['default'] ?? null) !== 'images/events/sport.png') {
            throw new \RuntimeException('Malformed Joomla event icon media setting was accepted.');
        }
    }

    $settings::$defaultIcon = ['legacy-array'];
    $form = new EventtypeForm();
    $base::$form = $form;

    if ($model->getForm() !== $form || ($form->attributes['icon']['default'] ?? null) !== '') {
        throw new \RuntimeException('Invalid default icon was cast to a string.');
    }

    echo "Joomla 5/6 event-type media field validation passed.\n";
}
