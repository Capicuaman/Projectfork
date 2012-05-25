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
 * This models supports retrieving lists of milestones.
 *
 */
class ProjectforkModelMilestones extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		        JController
	 */
	public function __construct($config = array())
	{
	    // Include query helper class
	    require_once JPATH_BASE.DS.'components'.DS.'com_projectfork'.DS.'helpers'.DS.'query.php';

	    if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'created', 'a.created',
                'modified', 'a.modified',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'state', 'a.state',
                'start_date', 'a.start_date',
                'end_date', 'a.end_date',
                'author_name',
                'editor',
                'access_level',
                'project_title', 'p.title',
                'tasklists',
                'tasks'
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

        // Filter - Author
        $value = JRequest::getCmd('filter_author', '');
        $this->setState('filter.author', $value);

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
        $id .= ':'.$this->getState('filter.published');
        $id .= ':'.$this->getState('filter.project');

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
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
        $user  = JFactory::getUser();


		// Select the required fields from the table.
		$query->select(
			$this->getState('list.select',
			    'a.id, a.asset_id, a.project_id, a.title, a.alias, a.description, a.created,'
                . 'a.created_by, a.modified, a.modified_by, a.checked_out,'
                . 'a.checked_out_time, a.attribs, a.access, a.state, a.start_date,'
                . 'a.end_date, p.alias AS project_alias'
			)
		);

		$query->from('#__pf_milestones AS a');

        // Join over the users for the checked out user
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the owner
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for project title
        $query->select('p.title AS project_title');
        $query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the tasks for task count
        $query->select('COUNT(DISTINCT ta.id) AS tasks');
        $query->join('LEFT', '#__pf_tasks AS ta ON (ta.milestone_id = a.id)');

        // Join over the tasks again for completed task count
        $query->select('COUNT(DISTINCT tc.id) AS completed_tasks');
        $query->join('LEFT', '#__pf_tasks AS tc ON (tc.milestone_id = a.id AND tc.complete = 1)');

        // Join over the task lists for task list count
        $query->select('COUNT(DISTINCT tl.id) AS tasklists');
        $query->join('LEFT', '#__pf_task_lists AS tl ON tl.milestone_id = a.id');

        // Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}


        // Filter fields
        $filters = array();
        $filters['a.state']        = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id']   = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.created_by']   = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a']              = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

		// Add the list ordering clause.
        $query->group('a.id');
		$query->order($this->getState('list.ordering', 'a.title').' '.$this->getState('list.direction', 'ASC'));

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
		$items = parent::getItems();

		// Get the global params
		$global_params = JComponentHelper::getParams('com_projectfork', true);


		foreach ($items as $i => &$item)
		{
		    // Convert the parameter fields into objects.
            $params = new JRegistry;
			$params->loadString($item->attribs);

			$items[$i]->params = clone $this->getState('params');

            // Create slugs
            $items[$i]->slug = $items[$i]->alias ? ($items[$i]->id.':'.$items[$i]->alias) : $items[$i]->id;
            $items[$i]->project_slug = $items[$i]->project_alias ? ($items[$i]->project_id.':'.$items[$i]->project_alias) : $items[$i]->project_id;
        }

		return $items;
	}


    /**
	 * Build a list of authors
	 *
	 * @return	JDatabaseQuery
	 */
	public function getAuthors()
    {
        $db    = $this->getDbo();
		$query = $db->getQuery(true);
        $user  = $user = JFactory::getUser();

		// Construct the query
		$query->select('u.id AS value, u.name AS text, COUNT(DISTINCT a.id) AS count');
		$query->from('#__users AS u');
		$query->join('INNER', '#__pf_milestones AS a ON a.created_by = u.id');

        // Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

        // Filter fields
        $filters = array();
        $filters['a.state']        = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id']   = array('INT-NOTZERO', $this->getState('filter.project'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);


        // Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 4));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $db->Quote('%'.$db->getEscaped(trim(substr($search, 8)), true).'%');
				$query->where('(u.name LIKE '.$search.' OR u.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}

        // Group and order
		$query->group('u.id');
		$query->order('u.name, count');

		$db->setQuery($query->__toString());

        $items = (array) $db->loadObjectList();
        $count = count($items);

        for($i = 0; $i < $count; $i++)
        {
            $items[$i]->text .= ' ('.$items[$i]->count.')';
            unset($items[$i]->count);
        }


		// Return the items
		return $items;
	}


    /**
	 * Build a list of publishing states
	 *
	 * @return	JDatabaseQuery
	 */
    public function getPublishedStates()
    {
        $db     = $this->getDbo();
        $states = JHtml::_('jgrid.publishedOptions');
        $count  = count($states);

        $query_select = $this->getState('list.select');
        $query_state  = $this->getState('filter.published');

        for($i = 0; $i < $count; $i++)
        {
            if($states[$i]->disable == true) {
                $states[$i]->text = JText::_($states[$i]->text).' (0)';
                continue;
            }
            if($states[$i]->value == '*') {
                unset($states[$i]);
                continue;
            }

            $this->setState('list.select', 'COUNT(DISTINCT a.id)');
            $this->setState('filter.published', $states[$i]->value);

            $query = $this->getListQuery();
            $db->setQuery($query->__toString());

            $found = (int) $db->loadResult();

            $states[$i]->text = JText::_($states[$i]->text).' ('.$found.')';
        }

        $this->setState('list.select', $query_select);
        $this->setState('filter.published', $query_state);

        return $states;
    }


	public function getStart()
	{
		return $this->getState('list.start');
	}
}
