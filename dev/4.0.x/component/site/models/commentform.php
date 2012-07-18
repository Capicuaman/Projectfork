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

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_projectfork/models/comment.php';


/**
 * Projectfork Component Comment Form Model
 *
 */
class ProjectforkModelCommentForm extends ProjectforkModelComment
{
	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 *
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('comment.id', $pk);

		$return = JRequest::getVar('return', null, 'default', 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', JRequest::getCmd('layout'));
	}


	/**
	 * Method to get item data.
	 *
	 * @param	  integer	The id of the item.
	 * @return    mixed	    Item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('comment.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return false;
		}

		$properties = $table->getProperties(1);
		$value = JArrayHelper::toObject($properties, 'JObject');

		// Convert attrib field to Registry.
		$value->params = new JRegistry;
		$value->params->loadString($value->attribs);

        // Count parent children
        $value->parent_children = $this->countChildren($value->parent_id);

        // Count children of this item
        $value->children = $this->countChildren($value->id);

		// Compute selected asset permissions.
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$asset	= 'com_projectfork.comment.'.$value->id;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset) || $user->authorise('comment.edit', $asset)) {
			$value->params->set('access-edit', true);
		}
		// Now check if edit.own is available.
		elseif (!empty($userId) && ($user->authorise('core.edit.own', $asset) || $user->authorise('comment.edit.own', $asset))) {
			// Check for a valid user and that they are the owner.
			if ($userId == $value->created_by) {
				$value->params->set('access-edit', true);
			}
		}

		// Check edit state permission.
		if ($itemId) {
			// Existing item
			$value->params->set('access-change', ($user->authorise('core.edit.state', $asset) || $user->authorise('comment.edit.state', $asset)));
		}
		else {
		    // New item
			$value->params->set('access-change', ($user->authorise('core.edit.state', 'com_projectfork') ||
                                                  $user->authorise('comment.edit.state', 'com_projectfork')));
		}

		return $value;
	}


    /**
	 * Get the return URL.
	 *
	 * @return	string	The return URL.
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}


    /**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_projectfork.edit.commentform.data', array());

		if(empty($data)) $data = $this->getItem();

		return $data;
	}


    protected function countChildren($parent = 0)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(id)')
              ->from('#__pf_comments')
              ->where('parent_id = ' . (int) $parent);

        $db->setQuery($query->__toString());

        $count = (int) $db->loadResult();

        return $count;
    }
}