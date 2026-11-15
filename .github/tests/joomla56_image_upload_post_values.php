<?php
/**
 * Verify Joomla 5/6 administrator image form selectors reject nested values.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController
    {
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Controller/ImagehandlerController.php';

    $type = \Diddipoeler\Component\SportsManagement\Administrator\Controller\ImagehandlerController::class;
    $text = new \ReflectionMethod($type, 'postedText');
    $identifier = new \ReflectionMethod($type, 'postedPositiveId');

    /**
     * Report an unexpected image selector without masking PHP warnings.
     *
     * @param mixed $input Untrusted image upload POST value.
     * @param string $expected Expected safely normalised string.
     * @return void
     */
    function assertImagePostedText(mixed $input, string $expected): void
    {
        global $text;

        if ($text->invoke(null, $input) !== $expected) {
            throw new \RuntimeException('Unexpected image field text for ' . get_debug_type($input));
        }
    }

    /**
     * Report an unsafe project/match image-directory selector.
     *
     * @param mixed $input Untrusted image directory ID.
     * @param int $expected Expected positive record ID or zero.
     * @return void
     */
    function assertImagePostedId(mixed $input, int $expected): void
    {
        global $identifier;

        if ($identifier->invoke(null, $input) !== $expected) {
            throw new \RuntimeException('Unexpected image directory ID for ' . get_debug_type($input));
        }
    }

    set_error_handler(
        /**
         * Treat PHP warnings from malformed form fields as failures.
         *
         * @param int $severity PHP diagnostic category.
         * @param string $message PHP diagnostic.
         * @return never Always throws an exception.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        assertImagePostedText('projectimages', 'projectimages');
        assertImagePostedText('some_field', 'some_field');
        assertImagePostedText(['projectimages'], '');
        assertImagePostedText(new \stdClass(), '');
        assertImagePostedText(7, '');
        assertImagePostedText(str_repeat('x', 4097), '');

        assertImagePostedId(12, 12);
        assertImagePostedId('0012', 12);
        assertImagePostedId('0', 0);
        assertImagePostedId('-12', 0);
        assertImagePostedId('+12', 0);
        assertImagePostedId('12junk', 0);
        assertImagePostedId(['12'], 0);
        assertImagePostedId(new \stdClass(), 0);
        assertImagePostedId(true, 0);
        assertImagePostedId('9999999999999999999999999', 0);
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 administrator image form values: OK\n";
}
