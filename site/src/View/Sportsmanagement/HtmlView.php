<?php
/**
 * Native Joomla 5/6 frontend SportsManagement root view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Sportsmanagement;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementViewErrorReporter;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Native Joomla 5/6 frontend root view. */
final class HtmlView extends BaseHtmlView
{
    public $item;

    /**
     * Render the native component root view without exposing model failures.
     *
     * Loads the root item from the Joomla model, sends any detailed model
     * errors only to the component log and raises a generic translated
     * failure for the site visitor. The normal Joomla layout runs when
     * no model errors are present.
     *
     * @param string|null $tpl Optional Joomla layout template suffix.
     * @return void
     * @throws \RuntimeException On a reported root-model error.
     */
    public function display($tpl = null)
    {
        $this->item = $this->get('Item');

        SportsManagementViewErrorReporter::assertNoErrors($this->get('Errors'), 'root');

        parent::display($tpl);
    }
}
