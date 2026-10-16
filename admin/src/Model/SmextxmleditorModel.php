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

    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');

        return $this->loadForm(
            'com_sportsmanagement.smextxmleditor',
            'smextxmleditor',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

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

    private function assertEditorPermission(): void
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

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

    private function getEditorPath(): string
    {
        return JPATH_ADMINISTRATOR
            . DIRECTORY_SEPARATOR . 'components'
            . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
            . DIRECTORY_SEPARATOR . 'assets'
            . DIRECTORY_SEPARATOR . 'extended';
    }

    private function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

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
