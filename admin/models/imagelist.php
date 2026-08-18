<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagelist
 * @file       imagelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

/**
 * sportsmanagementModelimagelist
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelimagelist extends ListModel
{
    var $_identifier = 'imagelist';
    var $limitstart = 0;
    var $limit = 0;
    static public $filesOutput = array();
    var $items = array();

    /**
     * Constructor.
     *
     * @param array $config Model configuration.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->limitstart = Factory::getApplication()->getInput()->getInt('limitstart', 0);
    }

    /**
     * Build the requested image file list.
     *
     * @param string $path      Relative image directory.
     * @param mixed  $scopeName Legacy scope parameter.
     * @param array  $post      Filter data.
     *
     * @return array
     */
    public function getFiles($path, $scopeName, $post)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $relativePath = trim(str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, (string) $path), DIRECTORY_SEPARATOR);

        if ($relativePath === '' || preg_match('#(^|[\\/])\.\.([\\/]|$)#', $relativePath))
        {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE'), Log::WARNING, 'jsmerror');
            self::$filesOutput = array();
            $this->items = array();
            return $this->items;
        }

        $directory = Path::clean(JPATH_ROOT . '/images/com_sportsmanagement/database/' . $relativePath);

        if (!Folder::exists($directory))
        {
            try
            {
                Folder::create($directory);
                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_CREATE_FOLDER'), Log::NOTICE, 'jsmerror');
            }
            catch (Throwable $e)
            {
                Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
                self::$filesOutput = array();
                $this->items = array();
                return $this->items;
            }
        }

        $allowedExtensions = array('jpg', 'png', 'gif');
        $search = trim((string) ($post['filter_search'] ?? ''));
        $extensionFilter = implode('|', array_merge($allowedExtensions, array_map('strtoupper', $allowedExtensions))));
        $filter = '^.*' . ($search !== '' ? preg_quote($search, '/') . '.*' : '') . '\\.(' . $extensionFilter . ')$';

        try
        {
            $files = Folder::files($directory, $filter) ?: array();
        }
        catch (Throwable $e)
        {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            $files = array();
        }

        self::$filesOutput = array();
        $this->items = array();

        foreach ($files as $file)
        {
            $fullPath = $directory . DIRECTORY_SEPARATOR . $file;

            if (!is_file($fullPath))
            {
                continue;
            }

            $fileParts = explode('.', $file);
            $extension = array_pop($fileParts);
            $stat = @stat($fullPath);
            $fileDate = ($stat !== false && isset($stat['mtime'])) ? $stat['mtime'] : @filemtime($fullPath);

            $fileMeta = new stdClass;
            $fileMeta->size = @filesize($fullPath) ?: 0;
            $fileMeta->is_writable = (int) is_writable($fullPath);
            $fileMeta->name = implode('.', $fileParts);
            $fileMeta->exs = $extension;
            $fileMeta->file = $file;
            $fileMeta->fileP = '';
            $fileMeta->path_relative = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            $fileMeta->width_60 = '60';
            $fileMeta->height_60 = '60';
            $fileMeta->dateC = $fileDate;
            $fileMeta->dateM = $fileDate;
            self::$filesOutput[] = $fileMeta;
        }

        $limit = (int) $this->getUserStateFromRequest(
            $this->context . '.limit',
            'limit',
            (int) $app->get('list_limit', 20),
            'int'
        );
        $start = $input->getInt('limitstart', 0);

        $this->setState('list.limit', $limit);
        $this->setState('list.start', $start);
        $this->limitstart = $start;
        $this->limit = $limit;

        $this->items = $limit > 0
            ? array_slice(self::$filesOutput, $start, $limit)
            : self::$filesOutput;

        return $this->items;
    }

    /**
     * Return pagination for the current file list.
     *
     * @return Pagination
     */
    public function getPagination()
    {
        $store = $this->getStoreId('getPagination');
        $limit = max(0, (int) $this->getState('list.limit'));
        $this->cache[$store] = new Pagination($this->getTotal(), $this->getStart(), $limit);

        return $this->cache[$store];
    }

    /**
     * Return total files before pagination.
     *
     * @return int
     */
    public function getTotal()
    {
        $store = $this->getStoreId('getTotal');
        $this->cache[$store] = count(self::$filesOutput);

        return $this->cache[$store];
    }

    /**
     * Return the validated pagination start offset.
     *
     * @return int
     */
    public function getStart()
    {
        $store = $this->getStoreId('getstart');
        $start = max(0, (int) $this->getState('list.start', $this->limitstart));
        $limit = max(0, (int) $this->getState('list.limit', $this->limit));
        $total = $this->getTotal();

        if ($limit > 0 && $start >= $total && $total > 0)
        {
            $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
        }
        elseif ($limit === 0)
        {
            $start = 0;
        }

        $this->setState('list.start', $start);
        $this->cache[$store] = $start;

        return $this->cache[$store];
    }

    /**
     * Populate list state from the current Joomla input object.
     *
     * @param mixed $ordering  Default ordering.
     * @param mixed $direction Default direction.
     *
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        $value = $this->getUserStateFromRequest(
            $this->context . '.limit',
            'limit',
            (int) $app->get('list_limit', 20),
            'int'
        );
        $this->setState('list.limit', $value);
        $this->setState('list.start', $input->getInt('limitstart', 0));

        $published = $this->getUserStateFromRequest(
            $this->context . '.filter.state',
            'filter_published',
            '',
            'string'
        );
        $this->setState('filter.state', $published);

        $filterOrder = $this->getUserStateFromRequest(
            $this->context . '.filter_order',
            'filter_order',
            '',
            'string'
        );
        $filterOrderDir = $this->getUserStateFromRequest(
            $this->context . '.filter_order_Dir',
            'filter_order_Dir',
            '',
            'cmd'
        );

        if (!in_array(strtoupper($filterOrderDir), array('ASC', 'DESC', ''), true))
        {
            $filterOrderDir = 'ASC';
        }

        $this->setState('filter_order', $filterOrder);
        $this->setState('filter_order_Dir', $filterOrderDir);
    }

    /**
     * File-backed list model; no database list query is used.
     *
     * @return null
     */
    protected function getListQuery()
    {
        return null;
    }
}
