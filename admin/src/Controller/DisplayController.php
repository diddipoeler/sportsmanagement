<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
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
        $input = $this->getApplication()->getInput();
        $input->set('view', $input->getCmd('view', $this->default_view));

        return parent::display($cachable, $urlparams);
    }
}
