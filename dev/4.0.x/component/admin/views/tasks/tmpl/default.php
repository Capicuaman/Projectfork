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

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user	    = JFactory::getUser();
$uid	    = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$save_order = ($list_order == 'a.ordering');
?>
<form action="<?php echo JRoute::_('index.php?option=com_projectfork&view=tasks'); ?>" method="post" name="adminForm" id="adminForm">

    <fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />

			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

            <select name="filter_access" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<select name="filter_author_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
				<?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
			</select>

            <select name="filter_assigned_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ASSIGNED_USER');?></option>
				<?php echo JHtml::_('select.options', $this->assigned, 'value', 'text', $this->state->get('filter.assigned_id'));?>
			</select>

            <select name="filter_milestone" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_MILESTONE');?></option>
				<?php echo JHtml::_('select.options', $this->milestones, 'value', 'text', $this->state->get('filter.milestone'));?>
			</select>

            <select name="filter_tasklist" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_TASKLIST');?></option>
				<?php echo JHtml::_('select.options', $this->tasklists, 'value', 'text', $this->state->get('filter.tasklist'));?>
			</select>
            <?php echo JHtml::_('projectfork.filterProject');?>
		</div>
	</fieldset>
	<div class="clr"></div>

    <table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $list_dir, $list_order); ?>
				</th>
                <th>
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
				</th>
                <th width="12%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_PROJECT', 'project_title', $list_dir, $list_order); ?>
				</th>
                <th width="12%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_MILESTONE', 'milestone_title', $list_dir, $list_order); ?>
				</th>
                <th width="12%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_TASKLIST', 'tasklist_title', $list_dir, $list_order); ?>
				</th>
                <th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'a.ordering', $list_dir, $list_order); ?>
					<?php if ($save_order) :?>
						<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'tasks.saveorder'); ?>
					<?php endif; ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $list_dir, $list_order); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
				</th>
                <th width="10%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $list_dir, $list_order); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
				</th>
			</tr>
		</thead>
        <tbody>
		<?php foreach ($this->items as $i => $item) :
			$asset_name = 'com_projectfork.task.'.$item->id;
            $ordering	= ($list_order == 'a.ordering');

			$canCreate	= ($user->authorise('core.create', $asset_name) || $user->authorise('task.create', $asset_name));
			$canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('task.edit', $asset_name));
			$canCheckin	= ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
			$canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('task.edit.own', $asset_name)) && $item->created_by == $uid);
			$canChange	= (($user->authorise('core.edit.state',	$asset_name) || $user->authorise('task.edit.state', $asset_name)) && $canCheckin);
            ?>
            <tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
                <td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'tasks.', $canChange, 'cb'); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'tasklists.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit || $canEditOwn) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_projectfork&task=task.edit&id='.$item->id);?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
                    <p class="smallsub">
                    <?php echo JText::_('COM_PROJECTFORK_ASSIGNED_TO').': ';?>
                    <?php
                    $users = array();
                    foreach($item->users AS $usr)
                    {
                        $users[] = $this->escape($usr->name);
                    }
                    $users = implode(', ', $users);

                    echo ($users) ? $users : JText::_('COM_PROJECTFORK_ASSIGNED_TO_NOBODY');
                    ?>
                    </p>
				</td>
                <td><?php echo $this->escape($item->project_title); ?></td>
                <td><?php echo $this->escape($item->milestone_title); ?></td>
                <td><?php echo $this->escape($item->tasklist_title); ?></td>
                <td class="order">
					<?php if ($canChange) : ?>
						<?php if ($save_order) :?>
							<?php if ($list_dir == 'asc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, ($item->catid == @$this->items[$i-1]->catid), 'tasks.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->catid == @$this->items[$i+1]->catid), 'tasks.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php elseif ($list_dir == 'desc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, ($item->catid == @$this->items[$i-1]->catid), 'tasks.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->catid == @$this->items[$i+1]->catid), 'tasks.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php endif; ?>
						<?php endif; ?>
						<?php $disabled = ($save_order ?  '' : 'disabled="disabled"'); ?>
						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
					<?php else : ?>
						<?php echo $item->ordering; ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->author_name); ?>
				</td>
				<td class="center nowrap">
					<?php echo JHtml::_('date',$item->created, JText::_('DATE_FORMAT_LC4')); ?>
				</td>
                <td class="center">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
        <tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
    </table>


    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>