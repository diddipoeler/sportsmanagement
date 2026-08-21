<?php
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
