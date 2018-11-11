<?php
	echo $this->Html->script('/grid/js/grid',array('inline'=>false));
	echo $this->Html->script('/grid/js/grid_index',array('inline'=>false));
	echo $this->Html->css('/grid/css/grid_index',array('inline'=>false));
	echo $this->Html->css('/grid/css/modal',array('inline'=>false));

	// validações de acesso
    $podeSalvar = $this->SscHtml->link('Salvar','/'.$this->request->controller.'/salvar',[]);

	$paramsTable['arrActionsButtons'][0]['href']  = Router::url('/',true).$this->request->controller.'/visualizar/{id}';
    $paramsTable['arrActionsButtons'][0]['class'] = 'btn-visualizar';
    $paramsTable['arrActionsButtons'][0]['title'] = 'Visualizar';
    if ($podeSalvar)
    {
    	$paramsTable['arrActionsButtons'][1]['href']  = Router::url('/',true).$this->request->controller.'/editar/{id}';
    	$paramsTable['arrActionsButtons'][1]['class'] = 'btn-editar';
    	$paramsTable['arrActionsButtons'][1]['title'] = 'Editar';

    	$paramsTop['arrLinksTop'][0]['url']  = Router::url('/',true).$this->request->controller.'/editar';
    	$paramsTop['arrLinksTop'][0]['text'] = 'Novo';
    }

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

	$paramsTop 		= isset($paramsTop) 	? $paramsTop 	: [];
	$paramsFilter 	= isset($paramsFilter) 	? $paramsFilter : [];
	$paramsTable 	= isset($paramsTable) 	? $paramsTable 	: [];
?>

<div class='divList'>

	<?php echo $this->element('Grid.top',$paramsTop);  ?>

	<?php echo $this->element('Grid.filtros',$paramsFilter);  ?>

	<?php  echo $this->element('Grid.table',$paramsTable); ?>

</div><!-- fim divList -->
