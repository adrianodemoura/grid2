<?php
	// títulos da tela
	$arrTitles 	= isset($arrTitles) ? $arrTitles : [0=>'Listando '.$this->request->controller];
	$breakTitle = isset($breakTitle) ? $breakTitle : "<br />";

?>
<div id='divBarTop' class='divBarTop'>

	<div class='divBarTitles'>
		<?php if (isset($arrTitles)) : ?>
		<?php foreach($arrTitles as $_l => $_title) : if ($_l) echo $breakTitle; ?>
			<span><?php echo $_title ?></span>
		<?php endforeach; endif; ?>
	</div><!-- divBarTitle -->

	<div class='divBarActions'>

		<?php if (isset($arrLinksTop)) : ?>
		<div class='divBarLinks'>
		<?php foreach($arrLinksTop as $_l => $_arrProp) : ?>
			<span class='acoes'>
				<a id='btnActionTop<?php echo ($_l+1); ?>' class='btn-link-botao' href='<?php echo $_arrProp["url"]; ?>'>
					<?php echo $_arrProp['text']; ?>
				</a>
			</span>
		<?php endforeach; ?>
		</div><!-- fim divBarLinks -->
		<?php endif; ?>

		<?php if (isset($arrButtonsTop)) : ?>
		<div class='divBarButtons'>
		<?php foreach($arrButtonsTop as $_l => $_arrProp) : ?>
			<span class='acoes'>
				<?php echo $this->Form->button($_arrProp); ?>
			</span>
		<?php endforeach; ?>
		</div><!-- fim divBarButtons -->
		<?php endif; ?>

	</div><!-- fim divBarActions -->

</div><!-- fim divBarTop -->