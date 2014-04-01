<?php
	$this->Html->setHead('css','lista');
	$this->Html->setHead('js','lista');
	$this->Html->setHead('js','jquery.maskedinput.min');
?>

<?php $this->element('ajax_form'); ?>

<div id='lista' class='row'>

	<div class='col-md-2'><!-- menu lista -->
		<?php $this->element('menu_lista'); ?>

	</div><!-- fim menu lista -->

	<div class='col-md-10'><!-- direita -->

		<?php $this->element('tabela_novo'); ?>

		<div id='ferramentas'><!-- ferramentas_topo -->
				
			<div  style='float: left;'><!-- paginação -->
			<?= $this->element('paginacao') ?>
			</div><!-- fim paginação -->

			<?php 
			if (isset($botoesLista)) : ?>
			<div style='float: left; margin: 0px 10px 0px 0px;'><!-- botoes -->
			<?php
			foreach($botoesLista as $_l => $_arrProp)
			{
				if (!empty($_arrProp))
				{
					echo "<input";
					foreach($_arrProp as $_tag => $_vlr) echo " $_tag='$_vlr'";
					echo " /> \n";
				}
			}
			?>
			</div><!-- fim botoes -->
			<?php endif ?>
				
			<?php if (isset($marcadores)) : ?>
			<div style='float: left; margin: 9px 0px 0px 0px;'><!-- marcadores -->
			<select name='cxSel' id='cxSel' >
				<option value=''>-- Aplicar aos Marcadores --</option>
				<?php foreach($marcadores as $_txt => $_link) : ?>
					<option value='<?= $_link ?>'><?= $_txt ?></option>
				<?php endforeach ?>
			</select>
				
			<?php endif ?>
			</div><!-- fim marcadores -->

		</div><!-- fim ferramentas_topo -->

		<?php if (isset($this->viewVars['filtros'])) : ?>
		<div class='filtro'>
		<?php echo $this->element('filtro'); ?>
		</div>
		<?php endif ?>

		<div class='tabela'><!-- tabela -->
		<?php echo $this->element('tabela'); ?>
		</div><!-- fim tabela -->

	</div><!-- fim direita -->

</div><!-- fim lista -->
