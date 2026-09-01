<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Filesystem\File;

/**
 * Native Joomla 5/6 file-editor model for SportsManagement extended XML/PHP files.
 */
final class SmextxmleditorModel extends AdminModel
{
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
        $fileName = $this->normaliseFileName((string) ($data['filename'] ?? ''));

        if ($fileName === null) {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));

            return false;
        }

        $filePath = $this->getEditorPath() . DIRECTORY_SEPARATOR . $fileName;
        $source = (string) ($data['source'] ?? '');

        try {
            if (!File::write($filePath, $source)) {
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
        $fileName = $this->normaliseFileName(
            $this->administratorApplication()->getInput()->getString('file_name')
        );
        $item = new \stdClass();

        if ($fileName === null) {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        $filePath = $this->getEditorPath() . DIRECTORY_SEPARATOR . $fileName;

        if (!is_file($filePath) || !is_readable($filePath)) {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        try {
            $source = file_get_contents($filePath);

            if ($source === false) {
                throw new \RuntimeException('Extended source file could not be read.');
            }
        } catch (\Throwable) {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        $item->filename = $fileName;
        $item->source = $source;

        return $item;
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
        return Factory::getContainer()->get(AdministratorApplication::class);
    }

    private function normaliseFileName(string $fileName): ?string
    {
        $fileName = trim($fileName);

        if ($fileName === '' || basename($fileName) !== $fileName) {
            return null;
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return in_array($extension, ['xml', 'php'], true) ? $fileName : null;
    }
}
