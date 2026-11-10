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

    /**
     * Display an administrator view using Joomla's MVC infrastructure.
     *
     * Validate the incoming selector, default to the component control panel
     * for empty requests, and normalise view names for class resolution on
     * case-sensitive hosts before invoking the base controller.
     *
     * @param bool $cachable Allow caching of the rendered view.
     * @param array $urlparams Permitted parameters for cacheable requests.
     * @return mixed Parent controller's render result.
     * @throws \InvalidArgumentException For malformed view selectors.
     */
    public function display($cachable = false, $urlparams = [])
    {
        $input = $this->input;
        $rawView = $input->get('view', '', 'raw');

        if (!is_string($rawView)) {
            throw new \InvalidArgumentException('Invalid SportsManagement administrator view.', 400);
        }

        $view = $input->getCmd('view', '');

        if (!is_string($view)) {
            throw new \InvalidArgumentException('Invalid SportsManagement administrator view.', 400);
        }

        // Reject values changed by the command filter, including whitespace,
        // before passing the view to the Joomla MVC factory.
        if ($rawView !== '' && (
            strtolower($view) !== strtolower($rawView)
            || trim($rawView) !== $rawView
        )) {
            throw new \InvalidArgumentException('Invalid SportsManagement administrator view.', 400);
        }

        // Keep MVC view selectors within the same length limit as the
        // administrator extension-language resolver before class lookup.
        if (strlen($view) > 100) {
            throw new \InvalidArgumentException('SportsManagement administrator view name is too long.', 400);
        }

        if ($view === '') {
            $view = $this->default_view;
        }

        // Joomla view names are case-insensitive; dispatch their canonical form.
        // This matches the site's native DisplayController and avoids
        // case-sensitive class lookup failures on Linux filesystems.
        $input->set('view', strtolower($view));

        // Joomla's parent controller receives only validated view input.
        return parent::display($cachable, $urlparams);
    }
}
