<span class="usernameWelcome"><?php echo $textWelcome; ?></span>
<span class="usernameName"><?php echo $this->Html->link($this->Session->read('Auth.User.username'), array('plugin' => 'users', 'controller' => 'users', 'action' => 'my'), array('class' => $linkClass, 'id' => $linkIdUser, 'checkPermissions' => true)); ?></span>
<?php echo $textSeparator; ?>
<?php echo $logOutLink; ?>
