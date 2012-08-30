<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
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

defined('JPATH_PLATFORM') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');


/**
 * Form Field class for selecting a task.
 *
 */
class JFormFieldTask extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 */
	public $type = 'Task';


	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$attr   = '';
        $hidden = '<input type="hidden" id="'.$this->id.'_id" name="'.$this->name.'" value="" />';

		// Initialize some field attributes.
		$attr .= $this->element['class']                         ? ' class="'.(string) $this->element['class'].'"'       : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"'                                : '';
		$attr .= $this->element['size']                          ? ' size="'.(int) $this->element['size'].'"'            : '';
		$attr .= $this->multiple                                 ? ' multiple="multiple"'                                : '';



        // Handle onchange event attribute.
        if((string) $this->element['submit'] == 'true') {
            $view = JRequest::getCmd('view');
            $attr = ' onchange="';
            if($this->element['onchange']) $attr .= (string) $this->element['onchange'].';';
            $attr .= "Joomla.submitbutton('".$view.".setTask');";
            $attr .= '"';
        }
        else {
            $attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
        }


        // Get parent item field values.
        $project_id   = (int) $this->form->getValue('project_id');
        $milestone_id = (int) $this->form->getValue('milestone_id');


        if(!$project_id) {
            // Cant get task list without at least a project id.
            return '<span class="readonly">'.JText::_('COM_PROJECTFORK_FIELD_PROJECT_REQ').'</span>'.$hidden;
        }

		// Get the field options.
        $options = $this->getOptions($project_id, $milestone_id);

        // Return if no options are available.
        if(count($options) == 0) {
            return '<span class="readonly">'.JText::_('COM_PROJECTFORK_FIELD_TASK_EMPTY').'</span>'.$hidden;
        }


		return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
	}


    /**
	 * Method to get the field list options markup.
	 *
	 * @return    array    The list options markup.
	 */
	protected function getOptions($project_id, $milestone_id = 0)
	{
	    $options = array();

        // Get field attributes for the database query
        $query_state = ($this->element['state']) ? (int) $this->element['state'] : NULL;


        // Find task list items.
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.id AS value, a.title AS text');
        $query->from('#__pf_tasks AS a');
        $query->where('a.project_id = '.(int) $project_id);

        // Filter by milestone.
        if($milestone_id) $query->where('a.milestone_id = '.(int) $milestone_id);

        // Filter state
        if(!is_null($query_state)) $query->where('a.state = '.$query_state);

        // Implement View Level Access.
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

        $query->order('a.title');

        $db->setQuery($query->__toString());
        $list = (array) $db->loadObjectList();

        // Generate the options
        if(count($list) > 0) {
            $options[] = JHtml::_('select.option',
                                  '',
                                  JText::alt('COM_PROJECTFORK_OPTION_SELECT_TASK',
                                  preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                                  'value',
                                  'text'
                                 );
        }


        foreach($list AS $item)
        {
            // Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option',
                            (string) $item->value,
                            JText::alt(trim((string) $item->text),
                            preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                            'value',
                            'text'
                           );

			// Add the option object to the result set.
			$options[] = $tmp;
        }

		reset($options);

		return $options;
	}
}