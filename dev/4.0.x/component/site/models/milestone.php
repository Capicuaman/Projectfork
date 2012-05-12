<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
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


jimport('joomla.application.component.modelitem');


/**
 * Projectfork Component Milestone Model
 *
 */
class ProjectforkModelMilestone extends JModelItem
{
    /**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_projectfork.milestone';


    protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('milestone.id', $pk);

		$offset = JRequest::getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
		$user = JFactory::getUser();
		if ( (!$user->authorise('core.edit.state', 'com_projectfork') && !$user->authorise('milestone.edit.state', 'com_projectfork')) &&
             (!$user->authorise('core.edit', 'com_projectfork') && !$user->authorise('milestone.edit', 'com_projectfork'))
           ){
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}


    /**
	 * Method to get item data.
	 *
	 * @param	integer	The id of the item.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('milestone.id');

		if ($this->_item === null) $this->_item = array();

		if (!isset($this->_item[$pk])) {

			try {
				$db = $this->getDbo();
				$query = $db->getQuery(true);

				$query->select($this->getState(
					    'item.select',
                        'a.id, a.asset_id, a.project_id, a.title, a.alias, a.description, '
					    . 'a.created, a.created_by, a.modified_by, a.checked_out, a.checked_out_time, '
					    . 'a.attribs, a.access, a.state, a.ordering, a.start_date, a.end_date'
					)
				);
				$query->from('#__pf_milestones AS a');

				// Join on project table.
				$query->select('p.title AS project_title, p.alias AS project_alias');
				$query->join('LEFT', '#__pf_projects AS p on p.id = a.project_id');

				// Join on user table.
				$query->select('u.name AS author');
				$query->join('LEFT', '#__users AS u on u.id = a.created_by');

				$query->where('a.id = ' . (int) $pk);

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');

				if (is_numeric($published)) {
					$query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if ($error = $db->getErrorMsg()) throw new Exception($error);


				if (empty($data)) {
					return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_MILESTONE_NOT_FOUND'));
				}

				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived))) {
					return JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_MILESTONE_NOT_FOUND'));
				}

				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadString($data->attribs);

				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				/*$registry = new JRegistry;
				$registry->loadString($data->metadata);
				$data->metadata = $registry;*/

				// Compute selected asset permissions.
				$user	= JFactory::getUser();

				// Technically guest could edit an article, but lets not check that to improve performance a little.
				if (!$user->get('guest')) {
					$userId	= $user->get('id');
					$asset	= 'com_projectfork.milestone.'.$data->id;

					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset) || $user->authorise('milestone.edit', $asset)) {
						$data->params->set('access-edit', true);
					}
					// Now check if edit.own is available.
					elseif (!empty($userId) && ($user->authorise('core.edit.own', $asset) || $user->authorise('milestone.edit.own', $asset))) {
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by) {
							$data->params->set('access-edit', true);
						}
					}
				}

				// Compute view access permissions.
				if ($access = $this->getState('filter.access')) {
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else {
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					$data->params->set('access-view', in_array($data->access, $groups));
				}

				$this->_item[$pk] = $data;
			}
			catch (JException $e)
			{
				if ($e->getCode() == 404) {
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else {
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}
}
