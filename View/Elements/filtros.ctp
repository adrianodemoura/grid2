<?php
$filtrosAtivos = 0;
if (!isset($filterOrdem))
{
	$filterOrdem = $filterFields;
} else
{
	$_filterOrdem = [];
	foreach($filterOrdem as $_filter)
	{
		$_filterOrdem[$_filter] = $filterFields[$_filter];
	}
	$filterOrdem = $_filterOrdem;
}
?>
<div class='divBarraFilter'>
<form name='formFilter' id='formFilter' name='formFilter' method="post">
<?php echo $this->Form->input('TokenIndex',['value'=>$token,'type'=>'hidden','style'=>'width: 1200px;','div'=>false,'label'=>false]) ?>
<?php if (count($filterOrdem)) : ?>
<div class='divFilter clearfix'>
	<div class='divFilterFields'>
		<?php foreach($filterOrdem as $_l => $_arrProp) : ?>
			<div class='divFilterField' id='divFilter<?php echo $this->Form->domId($_arrProp['alias']); ?>'>
				<?php 
					$i 			= $_arrProp['input'];
					$i['title'] = $_arrProp['title'];
					$i['div'] 	= false;
					if (isset($labelOff))
					{
						$i['label'] = false;
					}
					$i['autocomplete'] 	= 'off';
					$i['placeholder'] 	= isset($i['placeholder']) ? $i['placeholder'] : $i['title'];
					$a = $_arrProp['alias'];
					if ($this->Session->check($this->request->chave.'.Filter.'.$a))
					{
						$i['value'] = $this->Session->read($this->request->chave.'.Filter.'.$a);
					}
					if (isset($i['value']))
					{
						$i['class'] = isset($i['class']) ? $i['class'].' filtroAtivo' : 'filtroAtivo';
						$filtrosAtivos++;
					}

					echo $this->Form->input('Filter.'.$a,$i); 
				?>
			</div>
		<?php endforeach; ?>
	</div>

	<div class='divFilterButtons'><center>
	<?php 
		if (!empty($arrFilterButtons)) : 
		foreach($arrFilterButtons as $_l => $_arrProp) : 
		if ($_arrProp['id']=='btFilterClean' && $filtrosAtivos>0)
		{
			$_arrProp['class'] .= " bto-filter-clean-active";
		}
		echo "<input";
		foreach($_arrProp as $_tag => $_vlr) { echo " $_tag='$_vlr'"; };
		echo "/>";
		endforeach; endif; 
	?>
	</center></div>
</div><!-- divFilter -->
<?php endif; ?>
</form><!-- fim formFilter -->
</div>