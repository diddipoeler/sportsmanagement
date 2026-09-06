<?php
/**
 * Joomla 5/6 administrator pictures table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

/** Native table for SportsManagement image-package metadata. */
final class PicturesTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_pictures', 'id', $db);
    }
}
