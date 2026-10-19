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

/**
 * Default site controller for the modern Joomla dispatcher.
 *
 * The legacy controller used the sportsmanagement view whenever no explicit
 * view was supplied. Keep that behaviour in the namespaced controller so
 * requests through the Joomla 5/6 dispatcher resolve the same default view.
 */
final class DisplayController extends BaseController
{
    protected $default_view = 'sportsmanagement';

    public function display($cachable = false, $urlparams = [])
    {
        // Joomla only applies the controller default when the view is absent;
        // an explicitly empty view must be handled here as well.
        $rawView = $this->input->get('view', '', 'raw');

        if (!is_string($rawView)) {
            throw new \InvalidArgumentException('Invalid SportsManagement site view.', 400);
        }

        $view = $this->input->getCmd('view', '');

        // Reject values modified by Joomla's command filter, including whitespace.
        // The legacy site entry point applies the same strict validation.
        if ($rawView !== '' && (
            strtolower($view) !== strtolower($rawView)
            || trim($rawView) !== $rawView
        )) {
            throw new \InvalidArgumentException('Invalid SportsManagement site view.', 400);
        }

        if ($view === '') {
            $view = $this->default_view;
        }

        // Use the validated canonical view for the MVC lookup, not the raw input.
        $this->input->set('view', strtolower($view));

        return parent::display($cachable, $urlparams);
    }
}
