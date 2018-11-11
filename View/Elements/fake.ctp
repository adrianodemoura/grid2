<?php
	echo $this->Html->script('/grid/js/fake', 	['inline'=>false]);
	echo $this->Html->css('/grid/css/fake',		['inline'=>false]);

?>

<div id='atencao'>
	ATENÇÃO: Esta ação irá preencher o Model selecionado com vários registros Fake. Tenha cuidado em executá-la.
</div>

<table class='padraoFixo'>
	<thead>
	<tr>
		<th id='thModel' width='450px;'>Model</th>
		<th id='thTotal' width='100px;'>Total de Registros Atualmente</th>
		<th id='thNovos' width='100px;'>Novos Registros</th>
		<th id='thNovos' width='100px;'>Limpar a Tabela</th>
		<th id='thNovos' width='100px;'>Ignorar as validações</th>
		<th id='thAcoes' width='170px;'>Ações</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($arrModels as $_Model => $_arrProp) : ?>
		<tr>
			<td width='450px;'><?php echo $_arrProp['name'].' ('.$_arrProp['table'].')'; ?>
				<input type='hidden' 
					value='<?php echo $_arrProp['name']; ?>' 
					name='data[model][<?php echo $_Model; ?>]' 
					id='Model<?php echo $_Model; ?>' />
			</td>
			<td width='100px;' align="center" id='tdTotal<?php echo $_Model; ?>'><?php echo number_format($_arrProp['totalRegistros'],0,',','.'); ?></td>
			<td width='100px;' align="center">
				<input type='numeric' name='data[total][<?php echo $_Model ?>]' id='Total<?php echo $_Model ?>' value='10' maxlength='5' class='in_total' />
			</td>
			<td width='80px;' align="center">
				<input type='checkbox' name='data[limpar][<?php echo $_Model ?>]' id='Limpar<?php echo $_Model ?>' />
			</td>
			<td width='80px;' align="center">
				<input type='checkbox' name='data[validar][<?php echo $_Model ?>]' id='Validar<?php echo $_Model ?>' />
			</td>
			<td width='170px;' align="center">
				<input type='button' name='btnFakePopular' id='btnFakePopular<?php echo $_Model; ?>' value='Popular' class='btnFakePopular' />
			</td>
		</tr>
		<?php if (isset($_arrProp['msg'])) : ?>
		<tr>
			<td colspan='4' width='900px'><?php echo $_arrProp['msg']; ?></td>
		</tr>
		<?php endif; ?>
	<?php endforeach; ?>
	</tbody>
</table>

<div id='msg'>
</div>