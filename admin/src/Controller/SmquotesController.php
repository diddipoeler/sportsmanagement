<?php
/**
 * Native Joomla 5/6 administrator controller for SportsManagement quotes.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * Native Joomla 5/6 administrator controller for SportsManagement quotes.
 */
final class SmquotesController extends SportsManagementAdminController
{
    /**
     * Open the administrator overview for editable quote text files.
     *
     * Redirects to the quote text editor list using Joomla's routed
     * component URL without modifying the stored quotation records.
     *
     * @return void
     */
    public function edittxt(): void
    {
        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=smquotestxt', false)
        );
    }

    /**
     * Resolve the administrator quote model through Joomla's MVC factory.
     *
     * Preserves caller-provided model configuration but always sets
     * ignore_request to prevent request parameters from silently changing
     * the administrator quote model state.
     *
     * @param string $name Requested quote model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Additional Joomla model settings.
     * @return mixed Resolved model or the parent's failure result.
     */
    public function getModel($name = 'Smquote', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
