<div class="userRoles index">
  <table cellpadding="0" cellspacing="0" class="table-hover">
    <tr>
      <th><?php echo $this->Paginator->sort('id');?></th>
      <th><?php echo $this->Paginator->sort('name');?></th>
      <th><?php echo $this->Paginator->sort('view_prefix');?></th>
      <th class="actions"><?php echo __('Actions');?></th>
    </tr>
    <?php foreach ($userRoles as $userRole) : ?>
	<tr>
		<td><?php echo $userRole['UserRole']['id']; ?></td>
      	<td><?php echo $userRole['UserRole']['name']; ?></td>
      	<td><?php echo $userRole['UserRole']['view_prefix']; ?></td>
      	<td class="actions">
      		<?php 
      		echo __('%s %s %s', 
      			//$this->Html->link(__('View', true), array('action' => 'view', $userRole['UserRole']['id']), array('class' => 'btn btn-mini')),
      			$userRole['UserRole']['name'] != 'admin' && $userRole['UserRole']['name'] != 'guests' ? $this->Html->link(__('Edit', true), array('action' => 'edit', $userRole['UserRole']['id']), array('class' => 'btn btn-mini')) : '',
				$this->Html->link(__('Duplicate', true), array('action' => 'add', $userRole['UserRole']['id']), array('class' => 'btn btn-mini')),
				$userRole['UserRole']['name'] != 'admin' && $userRole['UserRole']['name'] != 'guests' ? $this->Html->link(__('Delete', true), array('action' => 'delete', $userRole['UserRole']['id']), array('class' => 'btn btn-mini'), sprintf(__('Are you sure you want to delete # %s?', true), $userRole['UserRole']['id'])) : ''
			); ?>
		</td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php echo $this->Element('paging'); ?>
<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'User Roles',
		'items' => array(
			 $this->Html->link(__('Add User Role', true), array('action' => 'add')),
			 $this->Html->link(__('Manage Permissions', true), array('plugin' => 'privileges', 'controller' => 'sections')),
			 )
		),
	array(
		'heading' => 'Users',
		'items' => array(
			 $this->Html->link(__('List Users', true), array('controller' => 'users', 'action' => 'index')),
			 )
		),
	))); ?>
