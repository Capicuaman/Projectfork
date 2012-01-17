<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
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

// No direct access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
Joomla.submitbutton = function(task) {
    if (task == 'project.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
	    <?php echo $this->form->getField('description')->save(); ?>
        Joomla.submitform(task, document.getElementById('item-form'));
	}
    else {
	    alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}
Joomla.pfSelectAccess = function(idx) {
    var access_val = $('jform_access').options[idx].value;

    if(access_val == 0) {
        $('jform_access_new-li').show();
        $('jform_access_groups-li').show();
    }
    else {
        $('jform_access_new-li').hide();
        $('jform_access_groups-li').hide();
    }
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_projectfork&view=project&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="width-60 fltlft">
        <fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_PROJECTFORK_NEW_PROJECT') : JText::_('COM_PROJECTFORK_EDIT_PROJECT'); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('title').$this->form->getInput('title'); ?></li>
				<li><?php echo $this->form->getLabel('state').$this->form->getInput('state'); ?></li>
				<li>
                    <span class="faux-label"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></span>
				    <div class="button2-left">
                        <div class="blank">
    					    <button type="button" onclick="document.location.href='#access-rules';">
                                <?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?>
                            </button>
					    </div>
				    </div>
				</li>
			</ul>
			<div class="clr"></div>
			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
		</fieldset>
    </div>

    <div class="width-40 fltrt">
        <?php echo JHtml::_('sliders.start','project-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

            <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
            <fieldset class="panelform">
				<ul class="adminformlist">
                    <li><?php echo $this->form->getLabel('created_by').$this->form->getInput('created_by'); ?></li>
                    <li><?php echo $this->form->getLabel('has_deadline').$this->form->getInput('has_deadline'); ?></li>
                    <li><?php echo $this->form->getLabel('start_date').$this->form->getInput('start_date'); ?></li>
                    <li><?php echo $this->form->getLabel('end_date').$this->form->getInput('end_date'); ?></li>
                    <?php if ($this->item->modified_by) : ?>
						<li><?php echo $this->form->getLabel('modified_by').$this->form->getInput('modified_by'); ?></li>
						<li><?php echo $this->form->getLabel('modified').$this->form->getInput('modified'); ?></li>
					<?php endif; ?>
                </ul>
            </fieldset>

            <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_FIELDSET_ACCESS'), 'access-details'); ?>
            <fieldset class="panelform">
                <ul class="adminformlist">
                    <li id="jform_access-li"><?php echo $this->form->getLabel('access').$this->form->getInput('access'); ?></li>
				    <li id="jform_access_new-li" style="display: none;"><?php echo $this->form->getLabel('access_new').$this->form->getInput('access_new'); ?></li>
                    <li id="jform_access_groups-li" style="display: none;">
                        <label id="jform_access_groups-lbl" class="hasTip" title="<?php echo JText::_('COM_PROJECTFORK_FIELD_ACCESS_GROUPS_DESC');?>">
                            <?php echo JText::_('COM_PROJECTFORK_FIELD_ACCESS_GROUPS_LABEL');?>
                        </label>
                        <div id="jform_access_groups">
                        <?php echo JHtml::_('access.usergroups', 'jform[access_rules]', array()); ?>
                        </div>
                    </li>
                </ul>
            </fieldset>


            <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
				<?php echo JHtml::_('sliders.panel',JText::_($fieldSet->label), $name.'-options'); ?>
				<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
					<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
				<?php endif; ?>
				<fieldset class="panelform">
					<ul class="adminformlist">
					    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
						    <li><?php echo $field->label. $field->input; ?></li>
					    <?php endforeach; ?>
					</ul>
				</fieldset>
			<?php endforeach; ?>

       <?php echo JHtml::_('sliders.end'); ?>
       <div class="clr"></div>
    </div>

    <div class="clr"></div>
	<div class="width-100 fltlft">
	    <?php echo JHtml::_('sliders.start','permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

		<?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_PROJECT_FIELDSET_RULES'), 'access-rules'); ?>
    		<fieldset class="panelform">
    		    <?php echo $this->form->getLabel('rules'); ?>
    			<?php echo $this->form->getInput('rules'); ?>
    		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
    </div>
    <div class="clr"></div>

    <div>
		<?php
            echo $this->form->getInput('alias');
            echo $this->form->getInput('created');
            echo $this->form->getInput('id');
        ?>
        <input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>