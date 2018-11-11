<?php
/**
 * Class GridFormHelper
 * 
 * @package 	Grid.View.Helper
 * @author 		Adriano
 */
/**
 * Mantém o ajudante GridForm.
 */
class GridFormHelper extends AppHelper {
	/**
	 * Ajudantes
	 *
	 * @var 	array
	 */
	public $helpers = ['Form', 'Html'];

	/**
	 * Parâmetro para uso do select pagination.
	 *
	 * @var 	boolean
	 */
	private $useSelectPagination = [];

	/**
	 * Executa chamada antes da renderização da view.
	 *
	 *
	 * @param 	string 	$viewFile  	O Arquivo que será renderizado.
	 * @return 	void
	 */
	public function afterRender($viewFile)
	{
		if (!empty($this->useSelectPagination))
		{
			echo $this->Html->script('/grid/js/select_pagination', 	['inline'=>false]);
			echo $this->Html->css('/grid/css/select_pagination', 	['inline'=>false]);

			$htmlScript = "";
			foreach ($this->useSelectPagination as $_field => $_params)
			{
				if (!in_array($_field, ['schema']))
				{
					$htmlScript .= "var Paginacao".$_field." = Object.create(PaginacaoSP);\n";
					$htmlScript .= "Paginacao".$_field.".init(".json_encode($_params).");\n";
					$htmlScript .= "window.onload = function() {Paginacao".$_field.".getPagina(1);};\n";
				}
			}
			echo $this->Html->scriptBlock($htmlScript, ['inline'=>false]);
		}
	}

	/**
	 * Retorna o input select para paginação
	 *
	 * @param 	string 	$field 	Nome do campo.
	 * @param 	array 	$params Parêmtros que seguem o método Form.input, acrescentado os parâmetros abaixo:
	 * - url: url que vai buscar o resultado da paginação.
	 * - field_search: campo que vai executar o filtro na paginação.
	 *
	 * @return 	string 	$input 	Elemento de paginação.
	 */
	public function selectPagination($name='', $params=[])
	{
		try
		{
			if (!isset($params['url']))
			{
				throw new Exception(__("O Parâmetro url é obrigatório !"), 1);
			}
			if (!isset($params['schema']))
			{
				throw new Exception(__("O Parâmetro schema é obrigatório !"), 1);
			}
			if (!isset($params['field_search']))
			{
				throw new Exception(__("O Parâmetro field_search é obrigatório !"), 2);
			}
			if (!isset($params['fields_title']))
			{
				throw new Exception(__("O Parâmetro fields_title é obrigatório !"), 3);
			}
			$params['type'] = 'text';
			$params['token']= getToken($name);
			$fieldsTitle	= $params['fields_title'];

			$domId = $this->Form->domId($name);
			$comId = ucfirst($this->Form->domId($name));

			// configurando os parâmetros obrigatórios da paginação.
			$this->useSelectPagination[$comId]['id'] 			= $domId;
			$this->useSelectPagination[$comId]['name'] 			= $name;
			$this->useSelectPagination[$comId]['page'] 			= 1;
			$this->useSelectPagination[$comId]['limit'] 		= isset($params['limit']) ? $params['limit'] : 10;
			$this->useSelectPagination[$comId]['last_page'] 	= 10;
			$this->useSelectPagination[$comId]['total'] 		= 100;
			$this->useSelectPagination[$comId]['url']  			= $params['url'];
			$this->useSelectPagination[$comId]['field_search'] 	= $params['field_search'];
			$this->useSelectPagination[$comId]['token'] 		= $params['token'];
			$this->useSelectPagination[$comId]['schema'] 		= $params['schema'];

			// salvando o token para posterior recuperação
			Cache::write($this->request->sufixo.$name, ['token'=>$params['token'], 'time'=>strtotime('now')]);

			// removendo alguns campos para não atrabalhar o input.
			unset($params['options']);
			unset($params['url']);
			unset($params['schema']);
			unset($params['token']);
			unset($params['field_search']);
			unset($params['fields_title']);
			unset($params['fields_select']);

			$html 	= "<div id='div".$comId."' class='select-pagination'>";

			$html 	.= $this->Form->input($name, $params);

			$html 	.= "<div id='div".$comId."Lista' class='select-pagination-result'>";
			$html 	.= "<table class='select-pagination-table' border=0>";
			$html 	.= "<tr><thead>";
			foreach($fieldsTitle as $_l => $_title)
			{
				$html .= "<th id='th".$_l."' class='select-pagination-th'>$_title</th>";
			}
			$html 	.= "</thead></tr>";
			$html 	.= "<tbody id='tbody".$comId."'></tbody>";
			$html 	.= "</table>";
			$html 	.= "</div>";

			$html 	.= "<div id='div".$comId."Rodape' class='select-pagination-footer'>";
			$html 	.= "<input type='button' value='<<' id='btnPri".$comId."' onclick='Paginacao".$comId.".getPagina(1)'  />";
			$html 	.= "<input type='button' value='<'  id='btnAnt".$comId."' onclick='Paginacao".$comId.".getPagina(-1)' />";
			$html 	.= "<span id='page".$comId."'>&nbsp;&nbsp;&nbsp;</span>&nbsp;";
			$html 	.= "<input type='button' value='>'  id='btnPro".$comId."' onclick='Paginacao".$comId.".getPagina(2)' />";
			$html 	.= "<input type='button' value='>>' id='btnUlt".$comId."' onclick='Paginacao".$comId.".getPagina(0)' />";
			$html 	.= "<span id='total".$comId."'>exibindo: ".$this->useSelectPagination[$comId]['limit'].' de '.$this->useSelectPagination[$comId]['total']."</span>&nbsp;";
			$html 	.= "</div>";

			$html 	.= "<div id='div".$comId."Message' class='select-pagination-message'>";
			$html 	.= "</div>";

			$html 	.= "</div><!-- fim select-pagination -->";	
		} catch (Exception $e)
		{
			$html = $e->getMessage();	
		}

		return $html;
	}
}
