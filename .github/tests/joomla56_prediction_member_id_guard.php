<?php
/**
 * Validate frontend prediction-member selectors on Joomla 5/6.
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
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Controller/PredictionusersController.php';

    $controller = new \ReflectionMethod(
        \Diddipoeler\Component\SportsManagement\Site\Controller\PredictionusersController::class,
        'memberSelectionId'
    );

    /**
     * Assert that a posted member identifier is accepted or rejected correctly.
     *
     * @param mixed $input Untrusted member selection sent by the site form.
     * @param int $expected Valid member ID or preselected fallback.
     * @return void
     */
    function assertMemberSelection(mixed $input, int $expected): void
    {
        global $controller;

        $result = $controller->invoke(null, $input, 17);

        if ($result !== $expected) {
            throw new \RuntimeException(
                'Unexpected member selection for ' . var_export($input, true)
                . ': expected ' . $expected . ', received ' . $result
            );
        }
    }

    assertMemberSelection(9, 9);
    assertMemberSelection('0009', 9);
    assertMemberSelection('0017', 17);
    assertMemberSelection('000', 17);
    assertMemberSelection(null, 17);
    assertMemberSelection(true, 17);
    assertMemberSelection(['9'], 17);
    assertMemberSelection('-9', 17);
    assertMemberSelection('+9', 17);
    assertMemberSelection('9junk', 17);
    assertMemberSelection('9.5', 17);
    assertMemberSelection('999999999999999999999999999', 17);

    echo "Joomla 5/6 prediction member selector guards: OK\n";
}
