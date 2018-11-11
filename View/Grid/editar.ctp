<?php
	echo $this->Html->script('/grid/js/grid',			['inline'=>false]);
	echo $this->Html->script('/grid/js/grid_editar',	['inline'=>false]);
	echo $this->Html->script('/grid/js/modal',			['inline'=>false]);
	echo $this->Html->css('/grid/css/grid_editar',		['inline'=>false]);

	if (isset($arrFileJs))
	{
		foreach($arrFileJs as $_l => $_file)
		{
			echo $this->Html->script($_file,array('inline'=>false));
		}
	}

	if (isset($arrFileCss))
	{
		foreach($arrFileCss as $_l => $_file)
		{
			echo $this->Html->css($_file,array('inline'=>false));
		}
	}
?>

<div class='divEdit'>
<form name='formEditar' id='formEditar' method="post">
<?php if (isset($token)) { echo $this->Form->input('TokenEdit',['type'=>'hidden','value'=>$token]); } ?>

	<div class='divBarraTop'>
		<div class='divBarTitles'>
			<?php foreach($arrTitles as $_l => $_title) : ?>
				<?php echo "<span>$_title</span>"; ?>
			<?php endforeach; ?>
		</div>
	</div>

	<div class='divFields'>

	<?php echo $this->element('Grid.editFields'); ?>

	</div><!-- fim divFields -->

	<div class='divButtons'>
	<?php if (isset($arrButtons)) : foreach($arrButtons as $_l => $_arrProp) : ?>
		<div class='acoes'>
		<a href="<?php echo $_arrProp['url']; ?>" class="btn-link-botao"
			<?php if (isset($_arrProp['onclick'])) : ?>onclick="<?php echo $_arrProp['onclick']; ?>"<?php endif; ?>
			<?php if (isset($_arrProp['id'])) : ?>id="<?php echo $_arrProp['id']; ?>"<?php endif; ?>
		>
			<?php echo $_arrProp['text']; ?>
		</a></div>
	<?php endforeach; endif; ?>
	</div><!-- 

	<div class='divInfo'>
	</div>-->

</form>
</div><!-- fim divEdit -->
<?php
	//debug($aliasFields);
	//debug($editFields);
	//debug($this->request->esquema);
	//debug($arrButtons);
	//debug($this->request->data);
	//debug($this->viewVars);