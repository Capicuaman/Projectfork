<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');


/**
 * This models supports retrieving lists of tasks.
 *
 */
class ProjectforkModelTasks extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
	    if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'created', 'a.created',
                'modified', 'a.modified',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'state', 'a.state',
                'priority', 'a.priority',
                'complete', 'a.complete',
                'start_date', 'a.start_date',
                'end_date', 'a.end_date',
                'author_name',
                'editor',
                'access_level',
                'project_title',
                'milestone_title',
                'tasklist_title',
                'ordering', 'a.ordering',
                'parentid', 'a.parentid',
                'assigned_id'
			);
		}

		parent::__construct($config);
	}


	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 */
	protected function populateState($ordering = 'title', $direction = 'ASC')
	{
		$app  = JFactory::getApplication();
        $user = JFactory::getUser();

		// Query limit
		$value = JRequest::getUInt('limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

        // Query limit start
		$value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

        // Query order field
		$value = JRequest::getCmd('filter_order', 'a.title');
		if(!in_array($value, $this->filter_fields)) $value = 'a.title';
		$this->setState('list.ordering', $value);

        // Query order direction
		$value = JRequest::getCmd('filter_order_Dir', 'ASC');
		if(!in_array(strtoupper($value), array('ASC', 'DESC', ''))) $value = 'ASC';
		$this->setState('list.direction', $value);

        // Params
		$value = $app->getParams();
		$this->setState('params', $value);

        // State
        $value = JRequest::getCmd('filter_published', '');
        $this->setState('filter.published', $value);

        if ((!$user->authorise('core.edit.state', 'com_projectfork') && !$user->authorize('milestone.edit.state', 'com_projectfork')) &&
            (!$user->authorise('core.edit', 'com_projectfork') && !$user->authorize('milestone.edit', 'com_projectfork'))){
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}

        // Filter - Search
        $value = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $value);

        // Filter - Project
        $value = $this->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');
        $this->setState('filter.project', $value);
        ProjectforkHelper::setActiveProject($value);

        // Filter - Milestone
        $value = JRequest::getCmd('filter_milestone', '');
        $this->setState('filter.milestone', $value);

        // Filter - Task list
        $value = JRequest::getCmd('filter_tasklist', '');
        $this->setState('filter.tasklist', $value);

        // Filter - Author
        $value = JRequest::getCmd('filter_author', '');
        $this->setState('filter.author', $value);

        // Filter - Assigned User
        $value = JRequest::getCmd('filter_assigned', '');
        $this->setState('filter.assigned', $value);

        // Filter - Priority
        $value = JRequest::getCmd('filter_priority', '');
        $this->setState('filter.priority', $value);

        // View Layout
		$this->setState('layout', JRequest::getCmd('layout'));
	}


	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':'.$this->getState('filter.project');
		$id .= ':'.$this->getState('filter.milestone');
		$id .= ':'.$this->getState('filter.tasklist');
        $id .= ':'.$this->getState('filter.published');

		return parent::getStoreId($id);
	}


	/**
	 * Get the master query for retrieving a list of items subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 */
	function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.project_id, a.list_id, a.milestone_id, a.catid, a.title, '
                . 'a.description, a.alias, a.checked_out, a.attribs, a.priority, '
				. 'a.checked_out_time, a.state, a.access, a.created, a.created_by, '
				. 'a.start_date, a.end_date, a.ordering, p.alias AS project_alias, '
                . 'tl.alias AS list_alias, m.alias AS milestone_alias'
			)
		);
		$query->from('#__pf_tasks AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
		$query->select('p.title AS project_title');
		$query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the task lists for the task list title.
		$query->select('tl.title AS tasklist_title');
		$query->join('LEFT', '#__pf_task_lists AS tl ON tl.id = a.list_id');

        // Join over the milestones for the milestone title.
		$query->select('m.title AS milestone_title');
		$query->join('LEFT', '#__pf_milestones AS m ON m.id = a.milestone_id');

		// Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

        // Filter by project
        $project = $this->getState('filter.project');
        if(is_numeric($project) && $project != 0) {
            $query->where('a.project_id = ' . (int) $project);
        }

        // Filter by milestone
		$milestone = $this->getState('filter.milestone');
		if (is_numeric($milestone)) {
			$query->where('a.milestone_id = ' . (int) $milestone);
		}

        // Filter by task list
		$task_list = $this->getState('filter.tasklist');
		if (is_numeric($task_list)) {
			$query->where('a.list_id = ' . (int) $task_list);
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '') {
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by author
		$author_id = $this->getState('filter.author');
		if (is_numeric($author_id)) {
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by '.$type.(int) $author_id);
		}

        // Filter by assigned user
        $assigned = $this->getState('filter.assigned');
        if(is_numeric($assigned)) {
            $query->join('INNER', '#__pf_ref_users AS ru ON (ru.item_type = '.
                                   $db->quote('task').' AND ru.item_id = a.id)');
            $query->where('ru.user_id = '.(int)$assigned);
        }

        // Filter by priority
		$priority = $this->getState('filter.priority');
		if (is_numeric($priority)) {
			$query->where('a.priority = '.(int) $priority);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			elseif (stripos($search, 'manager:') === 0) {
				$search = $db->Quote('%'.$db->getEscaped(substr($search, 7), true).'%');
				$query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}

        // Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.title');
		$orderDirn	= $this->state->get('list.direction', 'asc');

		if ($orderCol == 'a.ordering') {
			$orderCol = 'p.title, m.title, tl.title '.$orderDirn.', '.$orderCol;
		}
        if($orderCol == 'project_title') {
            $orderCol = 'm.title, tl.title, a.title '.$orderDirn.', p.title';
        }
        if($orderCol == 'milestone_title') {
            $orderCol = 'p.title '.$orderDirn.', m.title';
        }
        if($orderCol == 'tasklist_title') {
            $orderCol = 'p.title, m.title '.$orderDirn.', tl.title';
        }

		$query->order($db->getEscaped($orderCol.' '.$orderDirn));


		return $query;
	}


	/**
	 * Method to get a list of items.
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function getItems()
	{
	    JModel::addIncludePath(JPATH_SITE.'/components/com_projectfork/models', 'ProjectforkModel');

		$items = parent::getItems();
        $ref   = JModel::getInstance('UserRefs', 'ProjectforkModel');

		// Get the global params
		$global_params = JComponentHelper::getParams('com_projectfork', true);

        // Convert the parameter fields into objects.
		foreach ($items as $i => &$item)
		{
            $params = new JRegistry;
			$params->loadString($item->attribs);

			$items[$i]->params = clone $this->getState('params');
            $items[$i]->users  = $ref->getItems('task', $items[$i]->id);
        }

		return $items;
	}


    /**
	 * Build a list of project authors
	 *
	 * @return	JDatabaseQuery
	 */
	public function getAuthors()
    {
        $project = $this->getState('filter.project');

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text');
		$query->from('#__users AS u');
		$query->join('INNER', '#__pf_tasks AS a ON a.created_by = u.id');
        $query->where('a.project_id = ' . (int) $project);
		$query->group('u.id');
		$query->order('u.name');

		// Setup the query
		$db->setQuery($query->__toString());

		// Return the result
		return $db->loadObjectList();
	}


    /**
	 * Build a list of milestones
	 *
	 * @return	JDatabaseQuery
	 */
    public function getMilestones()
    {
        $project = $this->getState('filter.project');

        // Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('m.id AS value, m.title AS text');
		$query->from('#__pf_milestones AS m');
		$query->join('INNER', '#__pf_tasks AS a ON a.milestone_id = m.id');
        $query->where('a.project_id = ' . (int) $project);
		$query->group('m.id');
		$query->order('m.title');

		// Setup the query
		$db->setQuery($query->__toString());

		// Return the result
		return $db->loadObjectList();
    }


    /**
	 * Build a list of task lists
	 *
	 * @return	JDatabaseQuery
	 */
    public function getTaskLists()
    {
        $project = $this->getState('filter.project');

        // Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('t.id AS value, t.title AS text');
		$query->from('#__pf_task_lists AS t');
		$query->join('INNER', '#__pf_tasks AS a ON a.list_id = t.id');
        $query->where('a.project_id = ' . (int) $project);
		$query->group('t.id');
		$query->order('t.title');

		// Setup the query
		$db->setQuery($query->__toString());

		// Return the result
		return $db->loadObjectList();
    }


    /**
	 * Build a list of assigned users
	 *
	 * @return	JDatabaseQuery
	 */
    public function getAssignedUsers()
    {
        $project = $this->getState('filter.project');

        // Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text');
		$query->from('#__users AS u');
		$query->join('INNER', '#__pf_ref_users AS a ON a.user_id = u.id');
		$query->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id');
		$query->where('a.item_type = '.$db->quote('task'));
        $query->where('t.project_id = ' . (int) $project);
		$query->group('u.id');
		$query->order('u.name');

		// Setup the query
		$db->setQuery($query->__toString());

		// Return the result
		return $db->loadObjectList();
    }


	public function getStart()
	{
		return $this->getState('list.start');
	}
}
