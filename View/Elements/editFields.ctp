<?php

$esquema 	= isset($this->request->esquema) 	? $this->request->esquema : [];
$editFields	= isset($this->request->editFields) ? $this->request->editFields : [];
$separador 	= isset($this->request->separador) 	? $this->request->separador : ':';

foreach($editFields as $_l => $_field)
{
	if (strlen(strpos(strtolower($_field),'(')))
	{
		echo "<fieldset class='fieldSetEdit'>";
		$arrFs = explode('.',$_field);
		if (isset($arrFs[1]))
		{
			echo "<legend>".$arrFs[1]."</legend>";
		}
		continue;
	}
	if (strtolower($_field)==')')
	{
		echo "</fieldset>";
		continue;
	}
	if (strtolower($_field)=='-')
	{
		echo "<div class='divQuebraLinha'>&nbsp;</div>";
		continue;
	}
	if (strtolower($_field)=='#')
	{
		echo "<div class='divQuebraLinha'>&nbsp;</div>";
		continue;
	}

	$f = explode('.',$_field);
	if (count($f)==2)
	{
		$esquema[$_field]['title'] = isset($esquema[$_field]['title']) ? $esquema[$_field]['title'] : $_field;
		$alias 	= isset($esquema[$_field]['alias']) 	? $esquema[$_field]['alias'] 	: $_field;
		$type 	= isset($esquema[$_field]['type']) 		? $esquema[$_field]['type'] 	: [];
		$input 	= isset($esquema[$_field]['input']) 	? $esquema[$_field]['input'] 	: [];
		$input['value'] = isset($this->request->data[$f[0]][$f[1]]) 
			? $this->request->data[$f[0]][$f[1]]
			: null;
		$input['div'] = null;
		$input['label'] = ['text'=>$esquema[$_field]['title'].$separador.'&nbsp;', 'id'=>camelCase('label_'.$alias)];
		if (isset($this->request->visualizar))
		{
			$input['disabled'] = 'disabled';
		}
		if ($type=='date' && empty(@$input['class']))
		{
			$input['class'] = isset($input['class']) ? $input['class'].' txtData' : ' txtData';
		}
		if ($type=='datetime' && empty(@$input['class']))
		{
			$input['class'] = isset($input['class']) ? $input['class'].' txtDataHora' : ' txtDataHora';
		}

		echo "<div class='divField' id='".camelCase("div_".$alias)."'>";
		if (!isset($this->request->visualizar))
		{
			echo $this->Form->input($alias,$input);
			if (isset($erros[$f[1]]))
			{
				echo "<div class='divErroEdit'>".$erros[$f[1]][0]."</div>";
			}
		} else
		{
			echo "<label id='label".$alias."'";
			if (isset($esquema[$_field]['label']))
			{
				foreach($esquema[$_field]['label'] as $_tag => $_vlr)
				{
					echo " $_tag='$_vlr'";
				}
			}
			echo ">".$esquema[$_field]['title'];
			echo $separador;
			echo "</label>";
			if (!empty(trim($input['value'])))
			{
				if (in_array($type,['png','gif','jpeg']))
				{
					$extensao 	= isset($esquema[$_field]['extension']) ? $esquema[$_field]['extension'] : 'png';
					$tamanho 	= isset($esquema[$_field]['width']) ? $esquema[$_field]['width'] : 200;
					$input['value'] = "<img id='img' src='data:image/$extensao;base64,".$input['value']."' class='imgField' />";
				}
				if (isset($input['options']))
				{
					$input['value'] = $input['options'][$input['value']];
				}
				echo '<span class="spanOnlyView" id="'.camelCase('span_'.str_replace('.','_',$_field)).'">';
				echo $input['value'];
				echo '</span>';
			}
		}
		if (Configure::read('debug')==2)
		{
			echo "<div id='prop$alias' class='divShowField'><pre>".print_r([$_field=>$esquema[$_field]],true)."</pre></div>";
		}
		echo "</div>";
	} else // se trata de uma edição hasMany ou HABTM
	{
		$modelRel 	= $f[0];
		array_shift($f);
		$dataRel  	= isset($this->request->data[$modelRel]) 	? $this->request->data[$modelRel] 	: [];
		$esquema  	= isset($this->request->esquema) 			? $this->request->esquema 			: [];

		echo "<div class='divAssociations' id='divAssociation".$_l."'>";
		echo "<table border='0' class='gridTable'>";
		echo "<tr>"; // cabeçalho dos campos relacionados
		foreach($f as $_lRel => $_cmpRel)
		{
			if (isset($esquema[$modelRel.'.'.$_cmpRel]['pk']))
			{
				continue;
			}
			$titleRel = isset($esquema[$modelRel.'.'.$_cmpRel]['title']) ? $esquema[$modelRel.'.'.$_cmpRel]['title'] : $_cmpRel;
			$aliasRel = isset($esquema[$modelRel.'.'.$_cmpRel]['alias']) ? $esquema[$modelRel.'.'.$_cmpRel]['alias'] : $_cmpRel;
			echo "<th class='thAssoc' id='thAssoc".$aliasRel."'>$titleRel</th>";
		}
		echo "</tr>";

		// linha INSERT
		if (!isset($this->request->visualizar))
		{
			echo "<tr id='trAssoc0'>";
			foreach($f as $_lRel => $_cmpRel)
			{
				if (isset($esquema[$modelRel.'.'.$_cmpRel]['pk']))
				{
					continue;
				}
				$esquemaRel = isset($esquema[$modelRel.'.'.$_cmpRel]) ? $esquema[$modelRel.'.'.$_cmpRel] : [];
				$inputRel 	= isset($esquemaRel['input']) 	? $esquemaRel['input'] : [];
				$inputRel['label'] = false;
				$aliasRel 	= isset($esquemaRel['alias']) 	? $esquemaRel['alias'] : $_cmpRel;
				$type 		= isset($esquemaRel['type']) 	? $esquemaRel['type'] 	: 'text';
				if ($type=='date')
				{
					$inputRel['class'] = isset($inputRel['class']) ? $inputRel['class'] : '';
					$inputRel['class'] .= " txtData";

				}

				echo "<td id='tdAssoc0".$_lRel."'>";
				echo $this->Form->input($aliasRel,$inputRel);
				if (Configure::read('debug')==2)
				{
					echo "<div id='propField$_l".$_lRel."' class='divShowField'><pre>".print_r([$aliasRel=>$esquemaRel],true)."</pre></div>";
				}
				echo "</td>";
			}
			echo "</tr>";
		}

		// linha a linha dos relacionados
		if (!empty($dataRel))
		{
		}
		echo "</table>";
		echo "</div>";
	}

}