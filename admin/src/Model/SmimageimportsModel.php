<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 list model for downloadable SportsManagement image packages. */
final class SmimageimportsModel extends SportsManagementListModel
{
    private const MANIFEST = 'helpers/xml_files/pictures.xml';

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'obj.name', 'name',
            'obj.file', 'file',
            'obj.folder', 'folder',
            'obj.directory', 'directory',
            'obj.published', 'published', 'state',
            'obj.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    public function getXMLFolder(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('folder', 'id'),
                $db->quoteName('folder', 'name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_pictures'))
            ->where($db->quoteName('folder') . ' <> ' . $db->quote(''))
            ->group($db->quoteName('folder'))
            ->order($db->quoteName('folder') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return [];
        }
    }

    /** Refresh the local package manifest from the configured HTTPS endpoint. */
    public function getimagesxml(): bool
    {
        $app = $this->administratorApplication();
        $url = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_images_server', ''));

        if ($url === '') {
            return false;
        }

        if (!preg_match('#^https://#i', $url)) {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'), 'warning');

            return false;
        }

        try {
            $http = HttpFactory::getHttp(new Registry(), ['curl', 'stream']);
            $response = $http->get($url);

            if (!$response || (int) $response->code !== 200) {
                throw new \RuntimeException('HTTP ' . ($response ? (int) $response->code : 0));
            }

            $content = (string) $response->body;
            $xml = @simplexml_load_string($content);

            if ($xml === false) {
                throw new \RuntimeException(Text::_('JLIB_UTIL_ERROR_XML_LOAD'));
            }

            $path = $this->manifestPath();
            $directory = dirname($path);

            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new \RuntimeException(Text::_('JLIB_FILESYSTEM_ERROR_FOLDER_CREATE'));
            }

            if (file_put_contents($path, $content, LOCK_EX) === false) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_WRITABLE'));
            }

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            $app->enqueueMessage($e->getMessage(), 'warning');

            return false;
        }
    }

    /** Parse the local manifest and synchronize package metadata into the DB. */
    public function getXMLFiles(): array
    {
        $path = $this->manifestPath();

        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        $xml = @simplexml_load_file($path);

        if ($xml === false) {
            $this->setError(Text::_('JLIB_UTIL_ERROR_XML_LOAD'));

            return [];
        }

        $db = $this->getDatabase();
        $files = [];
        $index = 0;

        try {
            foreach ($xml->children() as $node) {
                $picture = isset($node->picture) ? $node->picture : $node;
                $name = trim((string) $picture);
                $folder = trim((string) $picture->attributes()->folder);
                $directory = trim((string) $picture->attributes()->directory);
                $file = basename(trim((string) $picture->attributes()->file));

                if ($name === '' || $folder === '' || $directory === '' || $file === '') {
                    continue;
                }

                $files[] = (object) [
                    'id' => $index++,
                    'picture' => $name,
                    'folder' => $folder,
                    'directory' => $directory,
                    'file' => $file,
                ];

                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_pictures'))
                    ->where($db->quoteName('name') . ' = ' . $db->quote($name));
                $db->setQuery($query, 0, 1);

                if ((int) $db->loadResult() === 0) {
                    $db->insertObject(
                        '#__sportsmanagement_pictures',
                        (object) [
                            'name' => $name,
                            'file' => $file,
                            'directory' => $directory,
                            'folder' => $folder,
                            'published' => 0,
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return [];
        }

        return $files;
    }

    protected function populateState($ordering = 'obj.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $this->setState(
            'filter.image_folder',
            $app->getUserStateFromRequest(
                $this->context . '.filter.image_folder',
                'filter_image_folder',
                '',
                'string'
            )
        );
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('obj') . '.*',
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_pictures', 'obj'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('obj.checked_out')
            );

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('obj.published') . ' = ' . (int) $state);
        }

        $search = trim((string) $this->getState('filter.search', ''));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('obj.name') . ') LIKE LOWER(' . $token . ')');
        }

        $folder = trim((string) $this->getState('filter.image_folder', ''));
        if ($folder !== '') {
            $query->where($db->quoteName('obj.folder') . ' = ' . $db->quote($folder));
        }

        $orderMap = [
            'obj.name' => $db->quoteName('obj.name'), 'name' => $db->quoteName('obj.name'),
            'obj.file' => $db->quoteName('obj.file'), 'file' => $db->quoteName('obj.file'),
            'obj.folder' => $db->quoteName('obj.folder'), 'folder' => $db->quoteName('obj.folder'),
            'obj.directory' => $db->quoteName('obj.directory'), 'directory' => $db->quoteName('obj.directory'),
            'obj.published' => $db->quoteName('obj.published'), 'published' => $db->quoteName('obj.published'), 'state' => $db->quoteName('obj.published'),
            'obj.id' => $db->quoteName('obj.id'), 'id' => $db->quoteName('obj.id'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'obj.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['obj.name']) . ' ' . $direction);

        return $query;
    }

    private function manifestPath(): string
    {
        return JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/' . self::MANIFEST;
    }
}
