<?php
	$textThAcoes 	= isset($textThAcoes) 		? $textThAcoes : 'Ações';
	$textEmptyTable	= isset($textEmptyTable) 	? $textEmptyTable : 'A pesquisa retornou vazio !!!';
	$arrowOrder 	= ($direcao=='ASC') ? '&#8593' : '&#8595';
	$lastId 		= $this->Session->check($chave.'.lastId') ? $this->Session->read($chave.'.lastId') : ['id'=>0, 'hora'=>'0'];
	$esquema 		= isset($this->request->esquema) ? $this->request->esquema : [];

	if (count($arrActionsButtons))
	{
		ksort($arrActionsButtons);
	}

	echo $this->Html->script('/grid2/js/grid',array('inline'=>false));
    echo $this->Html->script('/grid2/js/grid_index',array('inline'=>false));
    echo $this->Html->script('/grid2/js/modal',array('inline'=>false));

	echo $this->Html->css('/grid2/css/grid_index',array('inline'=>false));
    echo $this->Html->css('/grid2/css/grid_editar',array('inline'=>false));
    echo $this->Html->css('/grid2/css/modal',array('inline'=>false));

    echo $this->Html->scriptBlock(
    	"
    		var cadastro= '".$this->request->controller."';
    		var acao 	= '".$this->request->action."';
    		var debug 	= '".Configure::read('debug')."';
    	",
    	['inline'=>false]);

?>
<div id="modalFull" class="modal">
</div>

<div id='divTableErrors' class='divTableErrors'>
</div>

<?php if (!empty($this->request->data)) : ?>
<div class='divGrid'>
	<div class='divLines'>
	<table border=0 class='padrao'>
	<form name='formTable' id='formTable'>
	<?php
		$novaDirecao = ($direcao=="ASC") ? "DESC" : "ASC";
		foreach($this->request->data as $_l => $_arrMods)
		{
			$id 	= isset($_arrMods[$modelClass][$pk]) ? $_arrMods[$modelClass][$pk] : 0;
			$vlrId 	= getMaskId($id);
			// cabeçalho
			if (!$_l)
			{
				echo "<tr id='tr0'>";
				foreach($listFields as $_l2 => $_field) 
				{
					if (!isset($esquema[$_field]))
					{
						continue;
					}
					$a = explode('.',$_field);
					$t = isset($esquema[$_field]['title']) 	? $esquema[$_field]['title'] 	: $a[1];
					$th= isset($esquema[$_field]['th']) 	? $esquema[$_field]['th'] 		: null;
					$al= isset($esquema[$_field]['alias']) 	? $esquema[$_field]['alias'] 	: $this->Form->domid($_field);

					if (isset($esquema[$_field]['sort']))
					{
						$t = "<a href='$url/index/pagina:$pagina/ordem:$al-$novaDirecao'>$t</a>";
					}

					echo "<th $th id='".camelCase('th_'.$al)."'";
					if (Configure::read('debug')==2)
					{
						echo " title='".$al."'";
					}
					echo ">";
					echo "$t";
					if ($_field == $fieldOrder)
					{
						echo "<span>&nbsp;$arrowOrder</span>";
					}
					echo "</th>";
				}
				if (count($arrActionsButtons))
				{
					echo "<th class='thActions' colspan='".count($arrActionsButtons)."'>$textThAcoes</th>";
				}
				echo "</tr>\n";
				unset($a); unset($t);
			}

			// campo a campo
			echo "<tr id='tr".$vlrId."'";
			if ($vlrId == $lastId['id'])
			{
				echo " class='trLastId' title='Último acesso em ".$lastId['hora']."'";
			}
			echo ">";
			foreach($listFields as $_l2 => $_field) 
			{
				$a 	= explode('.',$_field);
				$vlr= isset($_arrMods[$a[0]][$a[1]]) ? $_arrMods[$a[0]][$a[1]] : null;
				$p  = isset($esquema[$a[0].'.'.$a[1]]) ? $esquema[$a[0].'.'.$a[1]] : [];
				$t  = isset($p['type']) ? $p['type'] : 'text';

				$td = isset($p['td']) ? $p['td'] : null;;
				if (!isset($p['alias']))
				{
					continue;
				}

				// se é um array
				if (is_array($vlr))
				{
					$vlr = implode(', ', $vlr);
				}

				// se tem options
				if (isset($p['input']['options']))
				{
					$vlr = $p['input']['options'][$vlr]; 
				}

				echo "<td class='".camelCase('td_'.$p['alias'])."'";
				if (isset($td))
				{
					foreach($td as $_tag => $_vlr)
					{
						echo " $_tag='$_vlr'";
					}
				}
				echo ">$vlr</td>";
			}

			if (count($arrActionsButtons))
			{
				foreach($arrActionsButtons as $_l2 => $_arrProp)
				{
					preg_match_all('/\{([A-za-z0-9 \/!:.=,$]+)\}/',$_arrProp['href'],$arrKeys);
					if (isset($arrKeys[1]))
					{
						foreach($arrKeys[1] as $_l3 => $_cmpReplace)
						{
							if (strpos($_cmpReplace,'.'))
							{
								$arrCR = explode('.', $_cmpReplace);
								$vlrReplace = getMaskId($_arrMods[$arrCR[0]][$arrCR[1]]);
								$_arrProp['href'] = str_replace('{'.$_cmpReplace.'}',$vlrReplace,$_arrProp['href']);
							}
						}
					}
			
					if (isset($arrActionsOff[$_l2]) && in_array($id, $arrActionsOff[$_l2]))
					{
						$_arrProp['class']= isset($_arrProp['class']) ? $_arrProp['class'].' btn-off' : 'btn-off';
					} else
					{
						$_arrProp['class']= isset($_arrProp['class']) ? $_arrProp['class'].' btn' : 'btn';
					}
					$_arrProp['href'] = str_replace('{id}',$vlrId,$_arrProp['href']);

					echo "<td align='center' class='btn-acoes'><a id='lineActionButton".$_l.($_l2+1)."'";
					if (isset($_arrProp['title']))
					{
						echo " title='".$_arrProp['title']."'";
					}
					echo " class='".$_arrProp['class']."' href='".$_arrProp["href"]."'>";
					if (isset($_arrProp['text']))
					{
						echo $_arrProp['text'];
					}
					echo "</a></td>";
				}
			}

			echo "</tr><!-- fim campo a campo -->";
		};
	?>
	</form>
	</table>
	</div><!-- fim divLines -->

	<div class='divInfo'>

		<?php 
			if ($this->request->params['paging'][$modelClass]['pageCount']>1) : 
				$pr = ($pagina + 1);
				$ar = ($pagina - 1);
				$ul = $this->request->params['paging'][$modelClass]['pageCount'];
		?>
		<div class='pagination'>

			<!--<?php if ($this->request->params['paging'][$modelClass]['prevPage']) : ?>
			<a title='primeira página' href='<?php echo $url."/index/pagina:1"; ?>'> << </a>
			<?php else : ?>
			<span> << </span>
			<?php endif; ?>-->

			<?php if ($this->request->params['paging'][$modelClass]['prevPage']) : ?>
			<span class='prev'><a title='página anterior' href='<?php echo $url."/index/pagina:1"; ?>'> « </a></span>
			<?php else : ?>
			<span class='prev disabled'> « </span>
			<?php endif; ?>

			<?php if ($ar>0) : ?>
			<span><a href='<?php echo $url."/index/pagina:$ar" ?>'><?php echo $ar; ?></a></span>
			<?php else: ?>
			<span class='current'><?php echo $pagina; ?></span>
			<?php endif; ?>

			<?php if ($pr>2) : ?>
			<span class='current'><?php echo $pagina; ?></span>
			<?php else: ?>
			<span><a href='<?php echo $url."/index/pagina:$pr" ?>'><?php echo $pr; ?></a></span>
			<?php endif; ?>

			<?php if ($this->request->params['paging'][$modelClass]['nextPage']) : ?>
			<span class='next'><a title='próxima página' href='<?php echo $url."/index/pagina:$ul"; ?>'> » </a></span>
			<?php else : ?>
			<span class='next disabled'> » </span>
			<?php endif; ?>

			<!--<?php if ($this->request->params['paging'][$modelClass]['nextPage']) : ?>
			<a title='última página' href='<?php echo $url."/index/pagina:$ul"; ?>'> >> </a>
			<?php else : ?>
			<span> >> </span>
			<?php endif; ?>-->

		</div><!-- fim divInfoNavigation -->
		<?php endif; ?>

		<!--<div class='divInfoPage'>
		<?php 
			if ($counterFilterSession>0)
			{
				$msgCounterFilter = ($counterFilterSession>1) ? " filtros ativos." : " filtro ativo.";
				echo "<span class='spanCounterFilter'>".$counterFilterSession." $msgCounterFilter</span>";
			}
		?>
		Exibindo: <?php echo $this->request->params['paging'][$modelClass]['current']; ?> 
		de <?php echo number_format($this->request->params['paging'][$modelClass]['count'],0,',','.'); ?> 

		<?php if ($this->request->params['paging'][$modelClass]['pageCount']>1) : ?>
		- Página: <?php echo $this->request->params['paging'][$modelClass]['page']; ?> 
		de <?php echo $this->request->params['paging'][$modelClass]['pageCount']; ?>
		<?php endif; ?>

		</div>--><!-- fim divInfoPage -->

	</div><!-- fim divInfo -->
</div><!-- fim divGrid -->

<?php else : ?>

<div class='divEmpty'><?php echo $textEmptyTable; ?></div>

<?php endif; ?>
