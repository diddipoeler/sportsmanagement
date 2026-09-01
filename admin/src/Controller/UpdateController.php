<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native controller for executing local SportsManagement update scripts. */
final class UpdateController extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $this->input->set('view', 'updates');

        return parent::display($cachable, $urlparams);
    }

    public function save(): void
    {
        $this->checkToken('get');

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

        if ($model === false) {
            echo Text::_('Update file not found!');

            return;
        }

        echo Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_FROM_FILE',
            '<b>' . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') . '</b>'
        );
        $model->loadUpdateFile($filePath, $fileName);
    }

    public function getModel($name = 'Updates', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

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
