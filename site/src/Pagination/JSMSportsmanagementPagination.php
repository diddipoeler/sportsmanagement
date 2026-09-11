<?php
/**
 * SportsManagement pagination adapter for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Pagination;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Pagination\Pagination;

/**
 * SportsManagement pagination adapter for Joomla 5/6.
 *
 * Joomla's Pagination class already owns URL generation, pagination data and
 * layout rendering. SportsManagement only needs its custom layout and the
 * active Itemid kept on generated links.
 */
final class JSMSportsmanagementPagination extends Pagination
{
    public function __construct(
        $total,
        $limitstart,
        $limit,
        $prefix = '',
        ?CMSApplication $app = null
    ) {
        $app ??= SportsManagementSiteApplicationResolver::resolve();
        parent::__construct($total, $limitstart, $limit, $prefix, $app);

        $itemId = $app->getInput()->getInt('Itemid', 0);
        if ($itemId > 0) {
            $this->setAdditionalUrlParam('Itemid', $itemId);
        }
    }

    public function getPaginationLinks($layoutId = 'joomla.pagination.links', $options = [])
    {
        return parent::getPaginationLinks('my.pagination.links', $options);
    }
}
