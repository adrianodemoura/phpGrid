<div class='container'>
<center>
	<?php if (isset($msgOk)) : ?>
	<div>
		<?= $msgOk ?>
	</div>
	<?php endif ?>

	<?php if (isset($msgErro)) : ?>
	<div class='error'>
		<?= $msgErro ?>
	</div>
	<?php endif ?>

	<div>
		Clique <a href='<?= $urlRetorno ?>'>aqui</a> para voltar
	</div>
</center>

	<div>
		<?php if (isset($dados)) debug($dados); ?>
	</div>
</div>
