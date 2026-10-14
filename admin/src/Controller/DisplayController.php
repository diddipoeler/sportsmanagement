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

        if ($rawView !== '' && $view !== $rawView) {
            throw new \InvalidArgumentException('Invalid SportsManagement administrator view.', 400);
        }

        if (trim($view) === '') {
            $input->set('view', $this->default_view);
        }

        return parent::display($cachable, $urlparams);
    }
}
