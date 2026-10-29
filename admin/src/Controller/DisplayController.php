<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Default administrator controller for the modern Joomla dispatcher.
 */
final class DisplayController extends BaseController
{
    protected $default_view = 'cpanel';

    public function display($cachable = false, $urlparams = [])
    {
        $input = $this->input;
        $rawView = $input->get('view', '', 'raw');

        if (!is_string($rawView)) {
            throw new \InvalidArgumentException('Invalid SportsManagement administrator view.', 400);
        }

        $view = $input->getCmd('view', '');

        // Reject values changed by the command filter, including whitespace,
        // before passing the view to the Joomla MVC factory.
        if ($rawView !== '' && (
            $view !== $rawView
            || trim($rawView) !== $rawView
        )) {
            throw new \InvalidArgumentException('Invalid SportsManagement administrator view.', 400);
        }

        if ($view === '') {
            $view = $this->default_view;
        }

        // Joomla view names are case-insensitive; dispatch their canonical form.
        // This matches the site's native DisplayController and avoids
        // case-sensitive class lookup failures on Linux filesystems.
        $input->set('view', strtolower($view));

        return parent::display($cachable, $urlparams);
    }
}
