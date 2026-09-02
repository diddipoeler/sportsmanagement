<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for JL XML exports. */
final class JlxmlexportsController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        $this->showranking();
    }

    public function showranking(): void
    {
    }
}
