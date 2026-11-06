<?php
/**
 * Joomla 5/6 administrator extended XML/PHP file editor model.
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
 * Native Joomla 5/6 file-editor model for SportsManagement extended XML/PHP files.
 */
final class SmextxmleditorModel extends AdminModel
{
    private const MAX_SOURCE_BYTES = 1048576;

    /**
     * Load the extended XML/PHP source editor form.
     * @param array $data Optional form values.
     * @param bool $loadData Whether stored values should be loaded.
     * @return mixed Joomla source-editor form.
     */
    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');

        return $this->loadForm(
            'com_sportsmanagement.smextxmleditor',
            'smextxmleditor',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    /**
     * Save an existing authorised extended source file.
     * Requires component administrator permission, a bounded source size
     * and a writable regular file inside the configured editor folder.
     * @param mixed $data Submitted filename and source contents.
     * @return bool Whether the write succeeded.
     */
    public function save($data)
    {
        $this->assertEditorPermission();

        if (!is_array($data) || !is_string($data['filename'] ?? null)
            || !is_string($data['source'] ?? null)
            || strlen($data['source']) > self::MAX_SOURCE_BYTES) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_FILE_WRITE'));

            return false;
        }

        $filePath = $this->existingEditorFile($data['filename'], true);

        if ($filePath === null) {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));

            return false;
        }

        try {
            if (!File::write($filePath, $data['source'])) {
                throw new \RuntimeException('Extended source file could not be written.');
            }
        } catch (\Throwable) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_FILE_WRITE'));

            return false;
        }

        return true;
    }

    /**
     * Load pending editor form state or the existing source file.
     * @return mixed Current source editor data.
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
     * Read an existing extended source file for administrator editing.
     * @return object Source filename and text or empty object on failure.
     */
    public function getSource()
    {
        $this->assertEditorPermission();

        $fileName = $this->administratorApplication()->getInput()->getString('file_name');
        $filePath = $this->existingEditorFile($fileName);
        $item = new \stdClass();

        if ($filePath === null || !is_readable($filePath)
            || filesize($filePath) > self::MAX_SOURCE_BYTES) {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        try {
            $source = file_get_contents($filePath);

            if (!is_string($source) || strlen($source) > self::MAX_SOURCE_BYTES) {
                throw new \RuntimeException('Extended source file could not be read.');
            }
        } catch (\Throwable) {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        $item->filename = basename($filePath);
        $item->source = $source;

        return $item;
    }

    /**
     * Require component administrator permission for source editing.
     * @return void
     * @throws \RuntimeException When edit access is denied.
     */
    private function assertEditorPermission(): void
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Resolve a validated existing file directly below the editor folder.
     * Rejects missing, symlinked, out-of-folder and unwritable files.
     * @param mixed $fileName Requested source file name.
     * @param bool $forWrite Whether the source must be writable.
     * @return string|null Trusted real path or null when invalid.
     */
    private function existingEditorFile(mixed $fileName, bool $forWrite = false): ?string
    {
        if (!is_string($fileName)) {
            return null;
        }

        $fileName = $this->normaliseFileName($fileName);

        if ($fileName === null) {
            return null;
        }

        $root = realpath($this->getEditorPath());

        if ($root === false) {
            return null;
        }

        $candidate = $root . DIRECTORY_SEPARATOR . $fileName;

        // Never create arbitrary .php files or follow symlinks into other
        // directories; both can otherwise lead to executable code writes.
        if (is_link($candidate) || !is_file($candidate)) {
            return null;
        }

        $resolved = realpath($candidate);

        if ($resolved === false || dirname($resolved) !== $root
            || ($forWrite && !is_writable($resolved))) {
            return null;
        }

        return $resolved;
    }

    /**
     * Resolve the component extended-editor directory.
     * @return string Absolute editor directory path.
     */
    private function getEditorPath(): string
    {
        return JPATH_ADMINISTRATOR
            . DIRECTORY_SEPARATOR . 'components'
            . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
            . DIRECTORY_SEPARATOR . 'assets'
            . DIRECTORY_SEPARATOR . 'extended';
    }

    /**
     * Resolve the current Joomla administrator application.
     * @return AdministratorApplication Current administrator runtime.
     */
    private function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Validate a top-level XML/PHP editor filename.
     * Rejects traversal and unsupported characters or extensions.
     * @param string $fileName Requested source filename.
     * @return string|null Valid filename or null.
     */
    private function normaliseFileName(string $fileName): ?string
    {
        $fileName = trim($fileName);

        if ($fileName === ''
            || strlen($fileName) > 255
            || str_contains($fileName, '..')
            || str_contains($fileName, '\\')
            || !preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*\.(?:xml|php)$/iD', $fileName)) {
            return null;
        }

        return $fileName;
    }
}
