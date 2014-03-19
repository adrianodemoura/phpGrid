<script>
$(document).ready(function()
{
	$('#formLiNo').submit(function() 
	{
		var retorno = true;
		var msgErro	= '';
		$.each(campos, function(index, object) 
		{
		    $.each(object, function(cmp, arrProp)
		    {
		    	var pri = arrProp['primary'];
		    	var obr = arrProp['obrigatorio'];
		    	if (pri==undefined && obr!=undefined)
		    	{
		    		for(var i=0; i<1; i++)
		    		{
		    			var id = "#"+i+index+cmp;
		    			vlr = $(id).val();
		    			if (vlr.length==0)
		    			{
		    				msgErro = 'O Campo '+cmp+', é de preenchimento obrigatório !!!';
		    			}
		    		}
		    	}
		    })
		}); 
		if (msgErro.length>0)
		{
			alert(msgErro);
			return false;
		} else return true;
	});
});

</script>
<div id='novo' style='position: absolute;  min-height: 30px; display: none;'><!-- novo -->
<div style='margin: 0px 0px 5px 0px;'>
	<input type='button' class='btn' name='CancerNovo' id='btFecNo' value='Cancelar' 
		onclick='$("#novo").fadeOut(); $("#filtros").fadeIn(); $("#ferramentas").fadeIn(); $("#tabela").fadeIn();' />
	<input type='button' class='btn btn-success' name='SalvarNovo' id='btSalvarN' value='Salvar Novo'
		onclick='$("#formLiNo").submit();' />
</div>

<table id='tabNovo'>
<form name='formLiNo' id='formLiNo' method='post' 
	action='<?= $base.strtolower($this->viewVars['module']).'/'.
				strtolower($this->viewVars['controller']).'/salvar' ?>' >
<input type='hidden' name='urlRetorno' value='<?= $this->viewVars['urlRetorno'] ?>' style='width: 300px;' />

<tr><!-- cabeçalho novo -->
	<?php
		foreach($this->viewVars['fields'] as $_l2 => $_cmp) : 
		$a = explode('.',$_cmp);
		$p = $this->viewVars['esquema'][$a['0']][$a['1']];
		$t = $this->viewVars['esquema'][$a['0']][$a['1']]['tit'];
		if (isset($p['belongsTo'])) $t = substr($t,0,strlen($t)-2);
		if (!isset($p['edicaoOff'])) :
	?>
	<th class="th<?= $this->Html->domId($a['1']) ?>">
		<?= $t ?>
	</th>
	<?php endif; endforeach ?>
</tr>
<tr><!-- input novo -->
	<?php
		foreach($this->viewVars['fields'] as $_l2 => $_cmp) : 
		$a = explode('.',$_cmp);
		$p = $this->viewVars['esquema'][$a['0']][$a['1']];
		if (!isset($p['edicaoOff'])) :
	?>
	<td align='center'>
	<?php
		$cmp = '0.'.$a['0'].'.'.$a['1'];

		$vlr = isset($p['default']) ? $p['default'] : '';

		echo $this->Html->getInput($cmp,$p);

		if (isset($p['mascara']))
		{
			array_push($this->viewVars['onRead'],'$("#'.$this->Html->domId($cmp).'")
					.mask("'.str_replace('#','9',$p['mascara']).'")');
		}
	?>
	</td>
	<?php endif; endforeach ?>
</tr>
</form>
</table>
</div><!-- fim novo -->