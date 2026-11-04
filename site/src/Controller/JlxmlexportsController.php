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

    /**
     * Display the frontend XML export view through the Joomla MVC controller.
     *
     * Forwards the cache flag and permitted URL parameters to Joomla's
     * parent display method; the view and model provide the export content.
     *
     * @param bool $cachable Whether rendering may be cached.
     * @param array $urlparams Permitted parameters for cacheable output.
     * @return mixed Result returned by the parent controller.
     */
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }

    /**
     * Handle the historical showranking task for XML exports.
     *
     * Selects the jlxmlexports view explicitly and invokes the parent
     * display method, preserving existing frontend links and task aliases.
     *
     * @return void
     */
    public function showranking(): void
    {
        $this->input->set('view', 'jlxmlexports');
        parent::display();
    }
}
