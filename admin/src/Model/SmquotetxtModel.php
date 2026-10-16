<?php
/**
 * Joomla 5/6 administrator quote source-file editor model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Filesystem\File;

/**
 * Native Joomla 5/6 file-editor model for random-quote module source files.
 */
final class SmquotetxtModel extends AdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');

        return $this->loadForm(
            'com_sportsmanagement.smquotetxt',
            'smquotetxt',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function save($data)
    {
        // Check permissions at the model boundary too: other Joomla
        // controllers and plugins can invoke this model directly.
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));

            return false;
        }

        if (!is_array($data)
            || !is_string($data['filename'] ?? null)
            || !is_string($data['source'] ?? null)
            || strlen($data['source']) > 1048576) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return false;
        }

        $fileName = $this->normaliseFileName($data['filename']);

        if ($fileName === null) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return false;
        }

        $filePath = $this->validatedExistingFile($fileName);

        if ($filePath === null) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return false;
        }

        $source = $data['source'];

        try {
            if (!File::write($filePath, $source)) {
                throw new \RuntimeException('Quote source file could not be written.');
            }
        } catch (\Throwable) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_FILE_WRITE'));

            return false;
        }

        return true;
    }

    protected function loadFormData()
    {
        $data = $this->administratorApplication()->getUserState(
            'com_sportsmanagement.edit.source.data',
            []
        );

        if (empty($data)) {
            $data = $this->getSource();
        }

        return $data;
    }

    public function getSource()
    {
        $fileName = $this->normaliseFileName(
            $this->administratorApplication()->getInput()->getString('file_name')
        );
        $item = new \stdClass();

        if ($fileName === null) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        $filePath = $this->validatedExistingFile($fileName);

        if ($filePath === null || !is_readable($filePath)) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        try {
            $source = file_get_contents($filePath);

            if ($source === false) {
                throw new \RuntimeException('Quote source file could not be read.');
            }
        } catch (\Throwable) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        $item->filename = $fileName;
        $item->source = $source;

        return $item;
    }

    private function getEditorPath(): string
    {
        return JPATH_SITE
            . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes';
    }

    private function normaliseFileName(string $fileName): ?string
    {
        $fileName = trim($fileName);

        if (strlen($fileName) > 255
            || !preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*\.(?:txt|php)$/iD', $fileName)
            || str_contains($fileName, '..')) {
            return null;
        }

        return $fileName;
    }

    /**
     * Never edit a missing path or follow a symlink outside the module.
     * Only the module's existing root-level source files are editable.
     */
    private function validatedExistingFile(string $fileName): ?string
    {
        $directory = realpath($this->getEditorPath());

        if ($directory === false) {
            return null;
        }

        $candidate = $directory . DIRECTORY_SEPARATOR . $fileName;

        if (is_link($candidate) || !is_file($candidate)) {
            return null;
        }

        $resolved = realpath($candidate);

        return $resolved !== false && dirname($resolved) === $directory
            ? $resolved
            : null;
    }

    private function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }
}
