<?php
/**
 * Joomla 5/6 administrator tournament-tree form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetoTable;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator form model for tournament trees. */
final class TreetoModel extends SportsManagementAdminModel
{
    public function getTable($type = 'Treeto', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Treeto') === 0) {
            return new TreetoTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getTreeToData($treeto_id)
    {
        $id = (int) $treeto_id;

        if ($id <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_treeto'))
            ->where($db->quoteName('id') . ' = :treeId')
            ->bind(':treeId', $id, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: false;
    }

    /** Generate the complete node set for one tournament tree. */
    public function setGenerateNode(): bool
    {
        $input = $this->administratorApplication()->getInput();
        $treetoId = $this->positivePostId($input->post->get('id', null, 'raw'));
        $projectId = $this->positivePostId($input->post->get('pid', null, 'raw'));
        $formData = new Registry($input->post->get('jform', [], 'array'));
        $rawDepth = $formData->get('tree_i', 0);
        $depthText = is_int($rawDepth) || is_string($rawDepth) ? trim((string) $rawDepth) : '';
        $treeDepth = preg_match('/^[1-7]$/D', $depthText) ? (int) $depthText : 0;

        // Seven levels yield at most 255 nodes. Never evaluate untrusted
        // exponents before checking the depth.
        if ($treetoId <= 0 || $projectId <= 0 || $treeDepth <= 0) {
            return false;
        }

        // A caller may invoke the model without the protected controller.
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException('Not authorized to generate tournament-tree nodes.', 403);
        }

        $globalBestOf = $input->post->getInt('global_bestof');
        $globalMatchday = $input->post->getInt('global_matchday');
        $globalKnown = $input->post->getInt('global_known');
        $globalFake = $input->post->getInt('global_fake');
        $db = $this->getDatabase();

        try {
            // Never update a bracket belonging to a different project.
            $scope = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_treeto'))
                ->where($db->quoteName('id') . ' = :generationTreeId')
                ->where($db->quoteName('project_id') . ' = :generationProjectId')
                ->bind(':generationTreeId', $treetoId, ParameterType::INTEGER)
                ->bind(':generationProjectId', $projectId, ParameterType::INTEGER);
            $db->setQuery($scope, 0, 1);

            if ((int) $db->loadResult() !== $treetoId) {
                $this->setError('Tournament tree does not belong to this project.');
                return false;
            }

            // Repeated generation used to silently append duplicate nodes.
            // Existing brackets must be reset through their explicit workflow.
            $existing = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_treeto_node'))
                ->where($db->quoteName('treeto_id') . ' = :generationExistingTreeId')
                ->bind(':generationExistingTreeId', $treetoId, ParameterType::INTEGER);
            $db->setQuery($existing, 0, 1);

            if ((int) $db->loadResult() > 0) {
                $this->setError('Tournament tree already has generated nodes.');
                return false;
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $db->transactionStart();

        try {
            $tree = (object) [
                'id' => $treetoId,
                'global_bestof' => $globalBestOf,
                'global_matchday' => $globalMatchday,
                'global_known' => $globalKnown,
                'global_fake' => $globalFake,
                'leafed' => 2,
                'tree_i' => $treeDepth,
            ];
            if (!$db->updateObject('#__sportsmanagement_treeto', $tree, 'id')) {
                throw new \RuntimeException('Could not update tournament tree.');
            }

            $nodeCount = (2 ** ($treeDepth + 1)) - 1;

            for ($node = 1; $node <= $nodeCount; ++$node) {
                $i = $treeDepth;
                $x = $node;
                $base = 2 ** $i;
                $row = $base;

                while ($x > 1) {
                    if ($x >= (2 ** $i)) {
                        $delta = $base * (1 / (2 ** $i));
                        $row += ($x % 2 === 1) ? $delta : -$delta;
                        --$i;
                        $x = (int) floor($x / 2);
                    } else {
                        --$i;
                    }
                }

                $record = (object) [
                    'treeto_id' => $treetoId,
                    'node' => $node,
                    'row' => $row,
                    'bestof' => $globalBestOf,
                ];
                if (!$db->insertObject('#__sportsmanagement_treeto_node', $record)) {
                    throw new \RuntimeException('Could not insert tournament-tree node.');
                }
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    private function positivePostId(mixed $raw): int
    {
        if (!is_string($raw) && !is_int($raw)) {
            return 0;
        }

        $value = trim((string) $raw);

        if (!preg_match('/^[1-9][0-9]*$/D', $value)) {
            return 0;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }
}
