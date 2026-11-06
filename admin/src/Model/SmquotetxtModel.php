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
    /**
     * Load the administrator quote source file editor form.
     * @param array $data Existing form values.
     * @param bool $loadData Whether Joomla should load stored values.
     * @return mixed Joomla quote editor form.
     */
    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');

        return $this->loadForm(
            'com_sportsmanagement.smquotetxt',
            'smquotetxt',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    /**
     * Write an authorised change to an existing quote source file.
     * Requires component administrator rights, bounds source length,
     * validates the filename and refuses symlink/escape paths.
     * @param mixed $data Submitted filename and source text.
     * @return bool Whether the source file was saved.
     */
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

    /**
     * Load the quote editor's pending input or existing source.
     * @return mixed Current quote editor form data.
     */
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

    /**
     * Read an existing validated quote-module source file.
     * @return object Source file descriptor or empty object on failure.
     */
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

    /**
     * Resolve the installed module folder for quote source editing.
     * @return string Quote module root directory.
     */
    private function getEditorPath(): string
    {
        return JPATH_SITE
            . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes';
    }

    /**
     * Validate a quote source filename against the supported extensions.
     * @param string $fileName Submitted source filename.
     * @return string|null Safe filename or null when invalid.
     */
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
     * Resolve only existing, regular quote-module source files.
     *
     * Rejects symlink paths, missing files and paths outside the module
     * root so an administrator cannot edit arbitrary system files.
     *
     * @param string $fileName Validated root-level source filename.
     * @return string|null Existing real file path or null.
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

    /**
     * Resolve the active Joomla administrator application.
     * @return AdministratorApplication Current administrator runtime.
     */
    private function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }
}
