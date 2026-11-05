<?php
/**
 * Native Joomla 5/6 administrator controller for executing local SportsManagement update scripts.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

final class UpdateController extends BaseController
{
    /**
     * Display the administrator update overview using Joomla MVC.
     * Selects the updates view and delegates rendering to BaseController.
     * @param bool $cachable Whether the view may be cached.
     * @param array $urlparams Permitted URL parameters for cached rendering.
     * @return mixed Parent Joomla controller display result.
     */
    public function display($cachable = false, $urlparams = [])
    {
        $this->input->set('view', 'updates');

        return parent::display($cachable, $urlparams);
    }

    /**
     * Execute a local component update PHP file selected by the administrator.
     * Validates the Joomla GET token and requires component core.admin
     * permission before resolving the update script path. Accepts only
     * restricted local PHP paths and delegates execution to the update model.
     * @return void
     * @throws \RuntimeException If administrator rights are missing.
     */
    public function save(): void
    {
        $this->checkToken('get');

        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $input = $this->input;
        $fileName = $this->normaliseFileName((string) $input->get('file_name', '', 'raw'));

        if ($fileName === null) {
            echo Text::_('Update file not found!');

            return;
        }

        $filePath = $this->buildUpdatePath($fileName);

        if ($filePath === null || !is_file($filePath)) {
            echo Text::_('Update file not found!');

            return;
        }

        $model = $this->getModel('Updates', 'Administrator', ['ignore_request' => true]);

        if (!is_object($model)) {
            echo Text::_('Update file not found!');

            return;
        }

        echo Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_FROM_FILE',
            '<b>' . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') . '</b>'
        );
        $model->loadUpdateFile($filePath, $fileName);
    }

    /**
     * Resolve the update model through the administrator MVC factory.
     * @param string $name Requested model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Additional model factory configuration.
     * @return mixed Joomla model or parent failure result.
     */
    public function getModel($name = 'Updates', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Validate an update-script file name from the request.
     * Normalises slash separators, limits scripts to one or two path
     * components and requires a PHP extension, rejecting traversal.
     * @param string $fileName User-selected update script path.
     * @return string|null Safe relative filename or null on invalid input.
     */
    private function normaliseFileName(string $fileName): ?string
    {
        $normalised = str_replace('\\', '/', trim($fileName));
        $parts = array_values(array_filter(explode('/', $normalised), static fn (string $part): bool => $part !== ''));

        if (!$parts || count($parts) > 2) {
            return null;
        }

        foreach ($parts as $part) {
            if ($part === '.' || $part === '..' || basename($part) !== $part) {
                return null;
            }
        }

        if (strtolower(pathinfo($parts[array_key_last($parts)], PATHINFO_EXTENSION)) !== 'php') {
            return null;
        }

        return implode('/', $parts);
    }

    /**
     * Resolve an already-validated update filename to an allowed path.
     * Maps component update filenames to assets/updates and two-part
     * extension updates to their administrator install directory.
     * @param string $fileName Validated relative update script path.
     * @return string|null Absolute update file path when supported.
     */
    private function buildUpdatePath(string $fileName): ?string
    {
        $parts = explode('/', $fileName);

        if (count($parts) === 1) {
            return JPATH_ADMINISTRATOR
                . DIRECTORY_SEPARATOR . 'components'
                . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
                . DIRECTORY_SEPARATOR . 'assets'
                . DIRECTORY_SEPARATOR . 'updates'
                . DIRECTORY_SEPARATOR . $parts[0];
        }

        if (count($parts) === 2) {
            return JPATH_SITE
                . DIRECTORY_SEPARATOR . 'components'
                . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
                . DIRECTORY_SEPARATOR . 'extensions'
                . DIRECTORY_SEPARATOR . $parts[0]
                . DIRECTORY_SEPARATOR . 'admin'
                . DIRECTORY_SEPARATOR . 'install'
                . DIRECTORY_SEPARATOR . $parts[1];
        }

        return null;
    }
}
