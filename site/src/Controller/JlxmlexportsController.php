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
    protected $default_view = 'jlxmlexports';

    /** Render the XML export view, which delegates to the native model. */
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }

    /** Preserve the historic task alias without swallowing the export view. */
    public function showranking(): void
    {
        $this->input->set('view', 'jlxmlexports');
        parent::display();
    }
}
