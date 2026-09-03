<?php
/**
 * Native Joomla 5/6 controller for the Transifex administrator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

final class TransifexController extends BaseController
{
    /**
     * Preserve the historic no-op task used by legacy integrations.
     */
    public function gettransifexinfo(): void
    {
    }
}
