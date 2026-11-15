<?php
/**
 * Joomla 5/6 TrainingsData template regressions for malformed legacy data.
 *
 * Usage: php .github/tests/joomla56_trainingsdata_template_guards.php
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
         * Supply a stable translation string to the isolated module template.
         * @param string $key Requested Joomla language key.
         * @return string Stable untranslated key for fixture assertions.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    /**
     * Provide malformed and valid module settings without Joomla bootstrapping.
     */
    final class TrainingTemplateParams
    {
        /**
         * Construct the fake Joomla Registry settings.
         * @param array<string,mixed> $values Test module parameter values.
         */
        public function __construct(private array $values)
        {
        }

        /**
         * Retrieve a configured setting with its Joomla fallback value.
         * @param string $key Requested module option.
         * @param mixed $default Value for unknown options.
         * @return mixed Configured option or fallback.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }

    /**
     * Render a TrainingsData module template without an installed Joomla site.
     *
     * @param array<string,mixed> $variables Render context from test fixtures.
     * @return string HTML produced by the native module template.
     */
    function renderTrainingsFixture(array $variables): string
    {
        extract($variables, EXTR_SKIP);
        ob_start();

        try {
            require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_trainingsdata/tmpl/default.php';

            return (string) ob_get_clean();
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }
    }

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    $module = (object) ['module' => 'mod_sportsmanagement_trainingsdata', 'id' => 42];
    $html = renderTrainingsFixture([
        'module' => $module,
        'params' => new TrainingTemplateParams([
            'show_training_modul_header' => 1,
            'show_training_note' => 1,
            'moduleclass_sfx' => ['broken'],
            'table_class' => new \stdClass(),
        ]),
        'trainingsdata' => [
            null,
            ['malformed'],
            (object) [
                'dayofweek' => 1,
                'time_start' => '3660',
                'time_end' => -15,
                'place' => '<img src=x onerror=alert(1)>',
                'notes' => '<script>alert(1)</script>',
            ],
            (object) [
                'dayofweek' => ['broken'],
                'time_start' => '99junk',
                'time_end' => '9999999999999999',
                'place' => ['invalid'],
                'notes' => ['bad'],
            ],
        ],
    ]);

    if (substr_count($html, '<tr>') !== 2
        || !str_contains($html, '01:01')
        || substr_count($html, '00:00') < 3
        || str_contains($html, '<img src=x')
        || str_contains($html, '<script>alert(1)</script>')
        || !str_contains($html, '&lt;img src=x onerror=alert(1)&gt;')
        || !str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;')
        || !str_contains($html, 'COM_SPORTSMANAGEMENT_GLOBAL_MONDAY')) {
        throw new \RuntimeException('TrainingsData rendered malformed times, text or legacy data.');
    }

    $empty = renderTrainingsFixture([
        'module' => $module,
        'params' => new TrainingTemplateParams(['show_training_note' => ['bad']]),
        'trainingsdata' => ['invalid row'],
    ]);

    if (!str_contains($empty, 'COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NODATA')) {
        throw new \RuntimeException('TrainingsData did not handle missing valid rows gracefully.');
    }

    restore_error_handler();
    echo "Joomla 5/6 TrainingsData native template guards passed.\n";
}
