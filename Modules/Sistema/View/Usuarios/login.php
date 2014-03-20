<?php
	$this->Html->setHead('css','sistema_usuarios_login');
	$inputUsu['input'] = array('value'=>'admin@admin.com.br','class'=>'form-control','placeholder'=>'e-mail','autofocus'=>'autofocus');
	$inputEma['input'] = array('value'=>'admin','type'=>'password','placeholder'=>'senha','class'=>'form-control');
?>
<div class='container-fluid'>
	<br />
	<center>
	<!-- <img src='<?= $base ?>img/angu.png' width='130px' /> -->
	<h1>
		<?= SISTEMA ?>
		<?php if (AMBIENTE!='PRODUÇÃO') : ?> (<span style='color: red; font-size: 18px;'><?= AMBIENTE ?></span>)<?php endif ?>
	</h1>
	</center>
	<br />
</div>

<div id='login' class="container">
	<form id='formLogin' method='post' action='' class="form-horizontal">

	<?= $this->Html->getInput('Usuario.email',$inputUsu); ?>
	<?= $this->Html->getInput('Usuario.senha',$inputEma); ?>

	<div style='float: left; line-height: 34px; margin: 10px 0px 0px 0px;'>
		<input type='submit' value='Enviar' id='btEnviar' class='btn btn-large btn-primary' />
	</div>

	<div style='float: right; line-height: 40px; margin: 6px 0px 0px 0px;'>
		<a href='<?= $base ?>sistema/usuarios/esqueci_a_senha'>esqueci a senha</a> |
		<a href='<?= $base ?>sistema/usuarios/registro'>registrar</a>
	</div>

	</form>
</div>
<br />
<div class="container">

<center>
	<a href='<?= $base ?>'>Volta para a página inicial</a>
</center>

</div>
