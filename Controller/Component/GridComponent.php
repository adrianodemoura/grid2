<?php
/**
 * Component Grid
 *
 * @package 	Grid.Component
 * @author 		adrianodemoura
 */
/**
 * Mantém o componente Grid. Neste componente será possivel construir as telas:
 * index, editar, excluir e visualiar. Será possível também executar ações de insert, update e delete.
 * Importante repassar os parãmetros (veja descrição em cada action).
 * Para a action index, é possível criar filtros na paginação, a chave de sessão é composta pelo nome do controller.
 */
class GridComponent extends Component {
	/**
	 * Controller
	 * Instância controller herdada pelo componente, obrigatório.
	 *
	 * @var 	object
	 */
	private $controller;

	/**
	 * Chave do controller corrente
	 * Composta por nome do Controller se caso exista, do plugin (plugin.controler->name).
	 *
	 * @var 	string
	 */
	private $chave;

	/**
	 * Url
	 * Importante para ser usada na sessão e guardar filtro e informações do paginate.
	 * 
	 * @var 	strring
	 */
	private $url;

	/**
	 * Inicializa o componente instanciando o controller
	 *
	 * @param 	Controller 	Controller
	 * @return 	void
	 */
	public function initialize(Controller $controller) 
	{
		$this->controller 	= $controller;
		$this->chave 		= camelCase($controller->name);
		if (!empty($controller->plugin))
		{
			$this->chave 	= camelCase($controller->plugin.$controller->name);
		}
		if ($this->controller->action=='limpar_filtro')
		{
			$this->controller->Session->delete($this->chave.'.Filter');
        	return $this->controller->redirect(['action'=>'index']);
		}
		$modelClass 		= $this->controller->modelClass;

		// verificando configuração do model.
		if (Configure::read('debug')>0)
		{
			$possuiFormatData = false;
			foreach($this->controller->$modelClass->actsAs as $_l => $_act)
			{
				if ($_act=='Grid.FormatData')
				{
					$possuiFormatData = true;
				}
			}
			if (!$possuiFormatData)
			{
				die('é preciso configurar o behaviors Grid.FormatData no seu model !!!');
			}
		}

		$this->url = Router::url('/',true).$controller->request->params['controller'];
	}

	/**
	 * Retorna o nome da chave
	 *
	 * @return 	string
	 */
	public function getChave()
	{
		return $this->chave;
	}

	/**
	 * Retorna o valor formatado para consição sql, com base no operador
	 *
	 * @param 	string 	$vlr 		Valor a ser formatado
	 * @param 	string 	$operador 	Valor do operador (veja mais em Model.esquema.filter)
	 * @return 	string 	$vlr 		Valor formatado
	 */
	private function getVlrOperation($vlr='', $operador)
	{
		switch (strtolower($operador)) 
		{
			case '%u%':
				$vlr = "%".mb_strtoupper($vlr)."%";
				break;
			case '%u':
				$vlr = "%".mb_strtoupper($vlr);
				break;
			case 'u%':
				$vlr = mb_strtoupper($vlr)."%";
				break;
			case '%%':
				$vlr = "%$vlr%";
				break;
			case "%-":
				$vlr = "%$vlr";
				break;
			case "-%":
				$vlr = "%$vlr";
				break;
			case "in":
				$vlr = "(".$vlr.")";
				break;
			
		}

		return $vlr;
	}

	/**
	 * Retorna o operador formatado para condição sql, com base no operador.
	 *
	 * @param 	string 	$operador 	Valor do operador (veja mais em Model.esquema.filter)
	 * @return 	string 	$vlr 		Valor formatado
	 */
	private function getOperation($operador)
	{
		switch (strtolower($operador)) 
		{
			case '%u%':
			case '%%':
			case "%-":
			case "-%":
				$operador = "LIKE";
				break;
			case "in":
				$operador = "IN";
				break;
			case "between":
				$operador = "BETWEEN";
				break;
			case ">":
			case "<";
				$operador = $operador;
				break;
			case 1:
			case true:
				$operador = "";
				break;
			default:
				$operador = $operador;
			
		}

		return $operador;
	}

	/**
	 */
	public function setLastId($id=0)
	{
		$lastId['id'] = (isset($this->controller->request->params['pass'][0])  && !empty($this->controller->request->params['pass'][0]) )
			? $this->controller->request->params['pass'][0]
			: $id;
		$lastId['hora'] = date('H:i:s');
		if ($lastId['id']>0)
		{
			$this->controller->Session->write($this->chave.'.lastId',$lastId);
		}
	}

	/**
	 * Exibe a tela de paginação.
	 *
	 * Os parâmetros são dividiso em quarto partes: parâmetros da view, paramsTop, paramsTable e paramsFilter que podem ter os atributos abaixo:
	 * - parãmetros da view:
	 * arraFileJs  - Matriz com possíveis arquivos do tipo javascript que são inclusos pelo layout.
	 * arraFileCss - Matriz com possíveis arquivos do tipo css que são inclusos pelo layout.
	 * useViewPlugin - Se criado, o componente vai usar a view (template) do próprio plugin, caso contrário, 
	 * o desenvolvedor vai criar sua própria view.
	 *
	 * - paramsQuery
	 * dateFormat - formato para campos data.
	 * conditions - filtro padrão.
	 * 
	 * - paramsTop:
	 * arrTitles - Matriz com vários título possíveis, ao menos o primeiro é configurado automaticamente.
	 * arrLinksTop - Matriz com vários redirecionamentos, usado href com classe btn-link-botao, o que faz parecer um botão.
	 * breakTitle - Caracter de divisão entre os os títulos.
	 * arrButtonsTop - 
	 *
	 * - paramsTable:
	 * esquema 	- Matriz com atributos sobre cada campo do Model corrente. Este esquema é um atributo deste model.
	 * fields 	- campos da pesquisa.
	 * listFields - campos de exibiação
	 * textActionsLine - Texto para a opação empty do select que repete em cada linha, pode-se configurá-la no esquema também.
	 * arrActionsButtons - Opçõar apara da linha, usando botões, este é o padrão.
	 * arrActionsLine - Opções para cada linha, que são colocadas num selectBox.
	 * arrActionsOff - Matriz que contém as ações que podem ser desligadas para cada linha do grid.
	 * limit, fields, page, conditions e join = parâmetros do paginate.
	 * textEmptyTable - Mensagem quando a pesquisa retornar vazio.
	 * lastId - Último ID a ser visualizado ou editado. A linha será destacada.
	 *
	 * - paramsFilter:
	 * filterFields - Campos que vão compor os filtros da tela, sua propriedade é herdada do esquema.
	 * Quando configurado o valor de cada filtro é salvo na sessão.
	 * textEmptyFilter - Texto para campos nulos do filtro.
	 * arrFilterButtons - Botões do filtro, o padrão é "Filtrar" e "Limpar".
	 * 
	 * @param 	Controller 	controller 	Herança controller
	 * @param 	array 		$params 	Parâmetros de paginação que são parâmetors da view, topo, table e filtros.
	 * @return 	void
	 */
	public function index($params=[])
	{
		// verificando configuração do model.
		if (Configure::read('debug')>0)
		{
			$possuiGrid = false;
			foreach($this->controller->components as $_component => $_vlr)
			{
				if ($_component=='Grid.Grid')
				{
					$possuiGrid = true;
				}
			}
			if (!$possuiGrid)
			{
				die('é preciso configurar o Componente Grid.Grid no seu Controller !!!');
			}
		}

		// limpando o filtro
		if (isset($this->controller->request->params['pass'][0]) && $this->controller->request->params['pass'][0]=='limpar_filtro')
		{
			$this->controller->Session->delete($this->chave);
			if (!empty($lastId))
			{
				$this->controller->Session->write($chave.'.lastId',$lastId);
			}
			return $this->controller->redirect(['action'=>'index']);
		}

		// variáveis locais
		$modelClass 		= $this->controller->modelClass;

		// definindo filtros pelo controller.
		$esquemaController = $this->controller->$modelClass->esquema;

		// atualizando esquema
		$velhoEsquema = isset($this->controller->$modelClass->esquema) ? $this->controller->$modelClass->esquema : [];
		$this->controller->$modelClass->setEsquema($this->controller->$modelClass);
		$this->controller->request->esquema = $this->controller->$modelClass->esquema;

		// definindo a view e o caminho da view
		if (isset($params['useViewPlugin']))
		{
			$this->controller->view 	= isset($params['template']) ? $params['template'] : 'index';
			$this->controller->viewPath	= '..'.DS.'Plugin'.DS.'Grid'.DS.'View'.DS.'Grid';
		}

		// variávies locais
		$modelClass 	= $this->controller->modelClass;
		$pk 			= $this->controller->$modelClass->primaryKey;
		$displayField 	= $this->controller->$modelClass->displayField;
		$pagina 		= 1;
		$url 			= $this->url;
		$textActionsLine= isset($params['textActionsLine']) ? $params['textActionsLine'] : '-- Selecione --';
		$counterFilter 	= 0;
		$chave 			= $this->chave;

		// ultimo registro acessado, para ser destacado no grid
		$lastId 		= $this->controller->Session->check($chave.'.lastId') 
			? $this->controller->Session->read($chave.'.lastId')
			: 0;

		// associações
		$associations = $this->controller->$modelClass->getAssociated();

		// título da tela vazia
		$textEmptyTable = isset($params['textEmptyTable']) ? $params['textEmptyTable'] : Mensagem::MSGI004();

		// Texto para opção vazia dos filtros do tipo select
		$textEmptyFilter = isset($params['textEmptyFilter']) ? $params['textEmptyFilter'] : '-- Selecione --';

		// ações para o filtro
		$arrFilterButtons = isset($params['arrFilterButtons']) ? $params['arrFilterButtons'] : [];
		if (empty($arrFilterButtons))
		{
			$arrFilterButtons[0] = ['type'=>'submit', 'value'=>'Filtrar', 'name'=>'btFiltrar', 'class'=>'bto bto-filter-go', 'id'=>'btFilterGo'];
			$arrFilterButtons[1] = ['type'=>'submit', 'value'=>'Limpar',  'name'=>'btErase',  'class'=>'bto bto-filter-clean', 'id'=>'btFilterClean'];
		}

		// ações para cada linha para button
		$arrActionsButtons 	= [];

		// títulos
		$arrTitles = isset($params['arrTitles']) ? $params['arrTitles'] : [];
		if (empty($arrTitles))
		{
			$arrTitles[0] = 'Listando '.$this->controller->name;
		}

		// verificando se o form de filtro foi postado
		if (!empty($this->controller->request->data))
		{
			$tokenSession = $this->controller->Session->read($chave.'.token');
			$tokenPost 	  = $this->controller->request->data['TokenIndex'];
			if ($tokenSession != $tokenPost)
			{
				$this->controller->Flash->error('Token inválido !!!');
				return $this->controller->redirect(['action'=>'index']);
			}

			if (isset($this->controller->request->data['btErase']))
			{
				$this->controller->Session->delete($this->chave);
				if (!empty($lastId))
				{
					$this->controller->Session->write($chave.'.lastId',$lastId);
				}
				return $this->controller->redirect(['action'=>'index']);
			}
			if (isset($this->controller->request->data['Filter']))
			{
				foreach($this->controller->request->data['Filter'] as $_alias => $_vlr)
				{
					$vlr = $_vlr;
					if (!empty($vlr))
					{
						$this->controller->Session->write($this->chave.'.Filter.'.$_alias,$vlr);
					} else
					{
						$this->controller->Session->delete($this->chave.'.Filter.'.$_alias);
					}
				}
			}
			$this->controller->Session->write($this->chave.'.pagina',1);
			return $this->controller->redirect(['action'=>'index']);
		}

		// variáveis obrigatórias
		$esquema 		= isset($this->controller->$modelClass->esquema) ? $this->controller->$modelClass->esquema : [];
		$filterFields	= isset($params['filterFields']) ? $params['filterFields'] : [];
		$aliasFields 	= [];

		// dando um loop no esquema para configurar atributos obrigatórios
		foreach($esquema as $_field => $_arrProp)
		{
			if (isset($_arrProp['origem']) && in_array($_arrProp['origem'],['hasMany','hasAndBelongsToMany']))
			{
				continue;
			}
			$_arrProp['filter'] = isset($esquemaController[$_field]['filter']) ? $esquemaController[$_field]['filter'] : null;

			$aliasFields[$_arrProp['alias']] = $_field;
			if (isset($_arrProp['filter']))
			{
				$filterFields[$_field] = $_arrProp;
				if (isset($_arrProp['input']['options']))
				{
					$filterFields[$_field]['input']['empty'] = isset($_arrProp['input']['empty']) 
						? $_arrProp['input']['empty'] 
						: '-- '.$_arrProp['title'].' --';
				}
				if (isset($_arrProp['filter']) && strtolower($_arrProp['filter']) =='between')
				{
					$filterFields[$_field.'FinalFilter'] = $_arrProp;
					$filterFields[$_field.'FinalFilter']['virtual'] = true;
					$filterFields[$_field.'FinalFilter']['alias'] .= 'FinalFilter';
					$filterFields[$_field.'FinalFilter']['input']['label'] .= ' Final';
					$filterFields[$_field.'FinalFilter']['input']['empty'] = '--';
					if ($this->controller->Session->check($this->chave.'.Filter.'.$_arrProp['alias'].'FinalFilter'))
					{
						$filterFields[$_field.'FinalFilter']['input']['value'] = $this->controller->Session->read($this->chave.'.Filter.'.$_arrProp['alias'].'FinalFilter');
					}
				}
				unset($filterFields[$_field]['input']['default']);
				unset($filterFields[$_field]['input']['required']);
				unset($filterFields[$_field]['input']['readonly']);
				if ($this->controller->Session->check($this->chave.'.Filter.'.$_arrProp['alias']))
				{
					$filterFields[$_field]['input']['value'] = $this->controller->Session->read($this->chave.'.Filter.'.$_arrProp['alias']);
				}
			}
		}

		// campos do paginate
		$fields = isset($params['fields'])
			? $params['fields']
			: null;

		// recuperando pk
		$pk	= isset($this->controller->$modelClass->primaryKey)
			? $this->controller->$modelClass->primaryKey
			: 'id';

		// parâmetro padrão, mas que podem ser alterados pelos parâmetros da table. (paramsTable).
		$limit = isset($params['limit']) ? $params['limit'] : 10;

		// recuperando a página
		$pagina = ($this->controller->Session->check($this->chave.'.pagina')) ? $this->controller->Session->read($this->chave.'.pagina') : 1;
		$pagina = (isset($this->controller->request->params['named']['pagina']))
			? $this->controller->request->params['named']['pagina']
			: $pagina;
		$this->controller->Session->write($this->chave.'.pagina',$pagina);

		// recuperando e configurando a ordem e direção via request
		$direcao = isset($params['direction']) ? $params['direction'] : "ASC";
		$ordem = !empty($this->controller->$modelClass->displayField)
			? $modelClass.'.'.$this->controller->$modelClass->displayField
			: $modelClass.'.'.$pk.'-'.$direcao;
		$ordem = isset($params['order'])
			? implode(',',$params['order'])
			: $ordem;
		$ordem = $this->controller->Session->check($this->chave.'.ordem')
			? $this->controller->Session->read($this->chave.'.ordem')
			: $ordem;
		$ordem = (isset($this->controller->request->params['named']['ordem']))
			? $this->controller->request->params['named']['ordem']
			: $ordem;
		$this->controller->Session->write($this->chave.'.ordem',$ordem);

		// configurando a ordem e direção verdadeira
		$ordemTrue = $this->controller->Session->read($this->chave.'.ordem');
		if (strlen(strpos($ordemTrue,'ASC')))
		{
			$ordemTrue 	= str_replace('-ASC','',$ordemTrue);
			$direcao 	= "ASC";
		} else
		{
			$ordemTrue 	= str_replace('-DESC','',$ordemTrue);
			$direcao 	= "DESC";
		}
		if (!empty($aliasFields) && isset($aliasFields[$ordemTrue]))
		{
			$ordemTrue = $aliasFields[$ordemTrue].' '.$direcao;
		}

		// atualizando o paginate
		$paginateParams['limit'] 	= $limit;
		$paginateParams['page'] 	= $pagina;
		$paginateParams['order'] 	= $ordemTrue;
		if ($ordemTrue!=$modelClass.'.'.$pk)
		{
			$paginateParams['order'] .= ', '.$modelClass.'.'.$pk;
		}
		if (isset($params['fields']))
		{
			$paginateParams['fields']= $fields;
		}
		if (isset($params['group']))
		{
			$paginateParams['group'] = $paginateParams['fields'];
			foreach($params['group'] as $_fieldGroup => $_tag)
			{
				$arrFields = explode('.',$_fieldGroup);
				array_push($paginateParams['fields'],$_tag.'('.$_fieldGroup.') as '.$_tag.'_'.$arrFields[1]);
				$this->controller->request->esquema['0.'.$_tag.'_'.$arrFields[1]] =
				[
					'title' => Inflector::camelize($_tag.'_'.$arrFields[1]),
					'alias' => 'm0c'.$_tag,
				];
			}
			$arrOrder 						= explode(',',$paginateParams['order']);
			$ordem0  						= explode('.',$arrOrder[0]);
			$paginateParams['order'] 		= null;
			if ($ordem0[0] != $modelClass)
			{
				$paginateParams['order'] = $arrOrder[0];
			}
		}
		if (isset($params['joins']))
		{
			$paginateParams['joins'] = $params['joins'];
		}
		if (isset($params['recursive']))
		{
			$paginateParams['recursive'] = $params['recursive'];
		}
		if (isset($params['contain']))
		{
			$paginateParams['contain'] = $params['contain'];
		}
		if (isset($params['recursive']))
		{
			$paginateParams['recursive'] = $params['recursive'];
		}
		$this->controller->paginate = $paginateParams;

		// recuperando e configurando o filtro para a paginação
		$conditions = [];
		$arrFilters = $this->controller->Session->read($this->chave.'.Filter')
			? $this->controller->Session->read($this->chave.'.Filter')
			: [];
		$counterFilterSession = count($arrFilters);
		if (isset($params['conditions']))
		{
			$arrFilters = array_merge($arrFilters,$params['conditions']);
		}
		if (!empty($arrFilters))
		{
			$counterFilter = count($arrFilters);
			foreach($arrFilters as $_alias => $_vlr)
			{
				if (strpos($_alias,' '))
				{
					$a = explode(' ',$_alias);
					$_alias = $a[0];
				}
				$field = isset($aliasFields[$_alias]) ? $aliasFields[$_alias] : $_alias;
				if (!isset($esquema[$field]))
				{
					continue;
					if (Configure::read('debug')==2)
					{
						debug($_POST);
						debug($field);
						debug($esquema);
					}
					die("Campo $field inválido, contacte o Administrador do Sistema !!!");
				}
				if (substr($_alias,strlen($_alias)-11,11) == 'FinalFilter') // ignorando campo between
				{
					$counterFilterSession--;
					continue;
				}
				if ($field)
				{
					$type 		= isset($esquema[$field]['type']) 		? $esquema[$field]['type'] 			: 'text';
					$filter 	= isset($esquema[$field]['filter']) 	? $esquema[$field]['filter'] 		: null;
					$filter 	= isset($velhoEsquema[$field]['filter'])? $velhoEsquema[$field]['filter'] 	: $filter;
					$virtual 	= isset($esquema[$field]['virtual']) 	? $esquema[$field]['virtual'] 		: false;
					$virtual 	= isset($velhoEsquema[$field]['virtual'])? $velhoEsquema[$field]['virtual'] : $virtual;

					if ($virtual===true)
					{
						continue;
					}

					$operador	= $this->getOperation($filter);
					if (isset($a))
					{
						$operador = $a[1];
						unset($a);
					}
					$vlr = $this->getVlrOperation($_vlr,$filter);
					if ($this->controller->Session->check($chave.'.Filter.'.$_alias.'FinalFilter'))
					{
						$vlr2 = $this->controller->Session->read($chave.'.Filter.'.$_alias.'FinalFilter');
						if (in_array($type,['integer','numeric','numero','float','decimal']))
						{
							if ($vlr2 < $vlr)
							{
								$vlr2 = ($vlr+1);
							}
							$operador 	= 'BETWEEN ? AND ?';
							$vlr 		= [$vlr,$vlr2];
						}
					}

					if (strlen(strpos($filter,'u')))
					{
						$field = "UPPER($field)";
					}

					if (in_array($type,['date', 'datetime']))
					{
						$ad = explode((strpos($vlr,'/')?'/':'-'),$vlr);
						if (strlen($ad[0])==2)
						{
							$vlr = $ad[2].'-'.$ad[1].'-'.$ad[0];
						}
						$d1 = date_create_from_format('Y-m-d',$vlr);
						unset($ad);

						$vlr2 = $this->controller->Session->check($chave.'.Filter.'.$_alias.'FinalFilter')
							? $this->controller->Session->read($chave.'.Filter.'.$_alias.'FinalFilter')
							: $vlr;
						$ad = explode((strpos($vlr2,'/')?'/':'-'),$vlr2);
						if (strlen($ad[0])==2)
						{
							$vlr2 = $ad[2].'-'.$ad[1].'-'.$ad[0];
						}
						$d2 = date_create_from_format('Y-m-d',$vlr2);
						$d2 = date_add($d2,date_interval_create_from_date_string('24 hours'));

						$operador = 'BETWEEN ? AND ?';
						$vlr = [$d1->format('Y-m-d'),$d2->format('Y-m-d')];
					}

					$conditions[trim($field.' '.$operador)] = $vlr;
					if (is_string($vlr) && strlen(strpos($vlr,'NULL')))
					{
						unset($conditions[trim($field.' '.$operador)]);
						$conditions = array_merge([trim($field.' '.$operador).' '.$vlr], $conditions);
					}
				}
			}
		}

		if (isset($params['dateFormat']) && !empty($params['dateFormat']))
		{
			$this->controller->$modelClass->query("ALTER SESSION SET NLS_DATE_FORMAT='".$params['dateFormat']."'");
		}

		// recuperando a paginação
		$this->controller->request->data = $this->controller->paginate($modelClass,$conditions);

		// campos da exibição
		$listFields = isset($params['listFields']) ? $params['listFields'] : null;
		if (!isset($listFields)) // recupera doso os campos da busca
		{
			if (isset($this->controller->request->data[0]))
			{
				foreach($this->controller->request->data[0] as $_Model => $_arrCmps)
				{
					foreach($_arrCmps as $_cmp => $_arrVlr)
					{
						$listFields[] = $_Model.'.'.$_cmp;
					}
				}
			}
		}

		// gerando o token que vai ser usado no formulário de filtros.
		$token = getToken($chave.'Index');
		$this->controller->Session->write($chave.'.token',$token);

		// parâmetros obrigatório para a view
		$paramsTop = [];
		$paramsTop['arrTitles'] 		= $arrTitles;
		$paramsTop['arrLinksTop'] 		= isset($params['arrLinksTop']) ? $params['arrLinksTop'] : [];

		$paramsTable = [];
		$paramsTable['direcao'] 		= $direcao;
		$paramsTable['fieldOrder'] 		= trim(str_replace('DESC','',str_replace('ASC','',$ordemTrue)));
		$paramsTable['modelClass'] 		= $modelClass;
		$paramsTable['pk'] 				= $pk;
		$paramsTable['listFields'] 		= $listFields;
		$paramsTable['arrActionsButtons']= isset($params['arrActionsButtons']) ? $params['arrActionsButtons'] : [];
		$paramsTable['counterFilter'] 	= $counterFilter;
		$paramsTable['counterFilterSession'] = $counterFilterSession;
		$paramsTable['url'] 			= $url;
		$paramsTable['pagina'] 			= $pagina;
		$paramsTable['textActionsLine']	= $textActionsLine;
		$paramsTable['textEmptyTable']	= $textEmptyTable;
		$paramsTable['associations']	= $associations;
		$paramsTable['chave']			= $chave;

		$this->controller->request->chave = $chave;

		$paramsFilter = [];
		$paramsFilter['filterFields'] 	= $filterFields;
		$paramsFilter['token'] 			= $token;
		$paramsFilter['arrFilterButtons']= $arrFilterButtons;
		$paramsFilter['labelOff']  		= true;
		$paramsFilter['textEmptyFilter']= $textEmptyFilter;

		// atualizando a view com os parâmetros obrigatório do grid.index
		$this->controller->set(compact('paramsTop','paramsTable','paramsFilter'));

		// se o controller solicitou algum css ou js específico.
		if (isset($params['arrFileCss']))
		{
			$this->controller->viewVars['arrFileCss'] = $params['arrFileCss'];
		}
		if (isset($params['arrFileJs']))
		{
			$this->controller->viewVars['arrFileJs'] = $params['arrFileJs'];
		}
	}

	/**
	 * Exibe a página de edicação
	 * Os parâmetros são:
	 * - id = Se omitido o formulário será de inclusão.
	 * - useViewPlugin = Se criado, usará a view do Plugin.
	 * - editFields = Campos da edição, no formato Model.field.
	 * - visualizar = Se criado, o formulário não será criado, e apenas exibirá os campos.
	 * - sepadorador = Caracter de sepração entre o label e o input.
	 * - esquema = Array com as propriedades de cada campo, veja mais em Model::esquema.
	 *
	 * obs:
 	 * - Caso o debug esteja ligado (2), as propreidades de cada campo serão impressas logo abaixo do seu campo de formulário. 
 	 * Basta clica no label e o modal será exibido com as propriedades do campo.
 	 * 
	 * @param 	array 	$params 	Parâmetros para a tela
	 * @return 	void
	 */
	public function editar($params=[])
	{
		// definindo a view e o caminho da view
		if (isset($params['useViewPlugin']))
		{
			$this->controller->view 	= isset($params['template']) ? $params['template'] : 'editar';
			$this->controller->viewPath	= '..'.DS.'Plugin'.DS.'Grid'.DS.'View'.DS.'Grid';
		}

		// variáveis locais
		$modelClass 	= $this->controller->modelClass;
		$pk 			= $this->controller->$modelClass->primaryKey;
		$displayField 	= $this->controller->$modelClass->displayField;
		$maskId 		= isset($this->controller->request->params['pass'][0])
			? $this->controller->request->params['pass'][0]
			: 0;
		$vlrId 			= getMaskId($maskId,1);
		$url 			= $this->url;
		$dataPost 		= [];
		$aliasFields 	= [];
		$params['msgInsertOk'] = isset($params['msgInsertOk']) ? $params['msgInsertOk'] : Mensagem::MSGI001();
		$params['msgUpdateOk'] = isset($params['msgUpdateOk']) ? $params['msgUpdateOk'] : Mensagem::MSGI002();
		$params['msgUpdateEr'] = isset($params['msgUpdateEr']) ? $params['msgUpdateEr'] : Mensagem::MSGI012();
		$params['msgInsertEr'] = isset($params['msgInsertEr']) ? $params['msgInsertEr'] : Mensagem::MSGI013();
		$this->controller->$modelClass->setEsquema($this->controller->$modelClass);
		$this->controller->request->esquema = $this->controller->$modelClass->esquema;
		$esquema = $this->controller->request->esquema;

		// parâmetros para a request e ser acessado na view
		$this->controller->request->separador 	= isset($params['separador']) ? $params['separador'] : ':';
		$this->controller->request->associations= $this->controller->$modelClass->getAssociated();

		// salvando o último acesso na sessão
		$this->setLastId();

		// dando um loop no esquema para configurar atributos obrigatórios
		foreach($esquema as $_field => $_arrProp)
		{
			$aliasFields[$_arrProp['alias']] = $_field;
			$f = explode('.',$_field);
			if ($_field == $modelClass.'.'.$pk)
			{
				$esquema[$_field]['input']['readonly'] = 'readonly';
			}
			if (isset($this->controller->request->data[$_arrProp['alias']])) // aproveitando o loop para re-configurar o nome dos campos
			{
				$dataPost[$f[0]][$f[1]] = $this->controller->request->data[$_arrProp['alias']];
			}
		}

		// verificando se o form foi postado
		if (!empty($this->controller->request->data))
		{
			if (isset($this->controller->request->data['btFechar']))
			{
				return $this->controller->redirect(['action'=>'index']);
			}

			// validando o token do formulário
			$tokenSession = $this->controller->Session->read($this->chave.'.token');
			$tokenPost 	  = $this->controller->request->data['TokenEdit'];
			if ($tokenSession != $tokenPost)
			{
				$this->controller->Flash->error(Mensagem::MSGI011());
				return $this->controller->redirect(['action'=>'index']);
			}

			if (!empty($dataPost))
			{
				if ($vlrId>0)
				{
					$dataPost[$modelClass][$pk] = $vlrId;
				}
				try 
				{
					$this->controller->$modelClass->save($dataPost);
					$msg = $params['msgUpdateOk'];
					$this->controller->Flash->success($msg);
					$maskId = getMaskId($this->controller->$modelClass->id);
					return $this->controller->redirect(['action'=>$this->controller->action.'/'.$maskId]);
				} catch (Exception $e) 
				{
					$erro = Apoio::getUltimaChave($this->controller->$modelClass->validationErrors);
					if (empty($erro))
					{
						$erro = @$e->getAttributes()['message'];
						if (empty($erro))
						{
							$erro = $e->getMessage();
						}
						$this->controller->$modelClass->validationErrors[$displayField][0] = utf8_decode($erro);
					}
					$this->controller->viewVars['erros'] = $this->controller->$modelClass->validationErrors;
				}
			}
		} elseif ($vlrId>0) // recupera do banco
		{
			$this->controller->request->data = $this->controller->$modelClass->find('first',['conditions'=>[$modelClass.'.'.$pk=>$vlrId]]);
		}

		// campos de edição, caso não seja passado pelo parâmetros.
		$editFields = isset($params['editFields']) ? $params['editFields'] : [];
		$editFieldsH= [];
		if (empty($editFields))
		{
			foreach($esquema as $_field => $_arrProp)
			{
				$f = explode('.',$_field);
				if (isset($this->controller->$modelClass->belongsTo[$f[0]])) // ignorando campos do relacionamento belongsTo
				{
					continue;
				}
				if (isset($this->controller->$modelClass->hasOne[$f[0]])) // ignorando campos do relacionamento belongsTo
				{
					continue;
				}
				if (	isset($this->controller->$modelClass->hasMany[$f[0]])
					||  isset($this->controller->$modelClass->hasAndBelongsToMany[$f[0]])
					) // campos hasMany ou Habtm
				{
					if (!isset($editFieldsH[$f[0]]))
					{
						$editFieldsH[$f[0]] = '';
					}
					if (!isset($_arrProp['virtual']))
					{
						$editFieldsH[$f[0]] .= $f[1].'.';
					}
					continue;
				}
				$editFields[] = $_field;
			}
		}

		if (strlen($vlrId)==0) // se é uma inclusão remove a pk
		{
			unset($editFields[array_search($modelClass.'.'.$pk,$editFields)]);
		}
		if (!empty($editFieldsH))
		{
			foreach($editFieldsH as $_Assoc => $_editFields)
			{
				$editFields[] = $_Assoc.'.'.substr($_editFields,0,strlen($_editFields)-1);
			}
		}
		$this->controller->request->editFields 	= $editFields;
		$this->controller->request->aliasFields = $aliasFields;

		// títulos
		$arrTitles = isset($params['arrTitles']) ? $params['arrTitles'] : [];
		if (empty($arrTitles))
		{
			$arrTitles[0] = 'Editando '.$this->controller->name;
		}

		// botões
		$arrButtons = isset($params['arrButtons']) ? $params['arrButtons'] : [];
		if (is_array($arrButtons) && empty($params['arrButtons']))
		{
			$arrButtons[0]['text']  = 'Salvar';
			$arrButtons[0]['url'] 	= $url.'/editar';
			$arrButtons[0]['id'] 	= 'btSalvar';
			$arrButtons[1]['text']  = 'Fechar';
			$arrButtons[1]['url'] 	= $url;
		}

		// gerando o token
		$token = getToken($this->chave.'Edit');
		$this->controller->Session->write($this->chave.'.token',$token);

		// atualizando a view com token, título, botões do formulário e ainda possíveis arquivos Css e Js.
		$this->controller->set(compact('arrTitles','arrButtons','token'));
		if (isset($params['arrFileCss']))
		{
			$this->controller->viewVars['arrFileCss'] = $params['arrFileCss'];
		}
		if (isset($params['arrFileJs']))
		{
			$this->controller->viewVars['arrFileJs'] = $params['arrFileJs'];
		}
	}

	/**
	 * Executa a exclusão do registro no banco de dados
	 *
	 * @param 	integer 	$id 	id do registro mascarados
	 * @param 	token 		$token 	Token gerado na tela Index
	 * @return 	void
	 */
	public function excluir($params=[])
	{
		$retorno 	= [];
		$modelClass = $this->controller->modelClass;
		$this->controller->viewPath	= '..'.DS.'Plugin'.DS.'Grid'.DS.'View'.DS.'Grid';
		if ($this->controller->request->is('post'))
		{
			$this->controller->layout = 'Grid.ajax';
		}

		try 
		{
			if (!isset($this->controller->request->data['TokenIndex']))
			{
				throw new Exception(Mensagem::MSGI011(), 1);
			}
			$tokenSession = $this->controller->Session->read($this->chave.'.token');
			$tokenPost 	  = $this->controller->request->data['TokenIndex'];
			if ($tokenSession != $tokenPost)
			{
				throw new Exception(Mensagem::MSGI011(), 2);
			}
			$vlrId = isset($this->controller->request->params['pass'][0])
	            ? getMaskId($this->controller->request->params['pass'][0],1)
	            : 0;
	        if (!$vlrId)
	        {
	        	throw new Exception(Mensagem::MSGI011(), 3);
	        }
	        if (!$this->controller->$modelClass->delete($vlrId))
            {
            	throw new Exception(Mensagem::MSGI014(), 4);
            }

            $retorno['status'] 	= true;
            $retorno['msg'] 	= Mensagem::MSGI015();
            $retorno['redirect']= $this->url;
            $this->controller->Flash->success($retorno['msg']);
		} catch (Exception $e) 
		{
			$retorno['status'] 	= false;
			$retorno['msg'] 	= __('Não foi possível excluir o registro !');
            /*$retorno['msg'] 	= $e->getMessage();
			if (method_exists($e, 'getAttributes'))
			{
				$retorno['msg'] = utf8_decode($e->getAttributes()['message']);
			}*/
		}

		$this->controller->set(compact('retorno'));
	}

	/**
	 * Exibe a tela de visualização do registro corrente.
	 * O Parâmetro $params pode conter os seguintes atributos: 
	 * - editFields - campos para visualização no formato Model.campo, e ainda é possível criar fieldSet como (.legena,Model.Campo1,Model.Campo2)
	 * - useViewPlugin - Se criado, usará a view do plugin.
	 * - arrTitles  - Array com os títulos possíveis na barra de cima.
	 * - arrButtons - Botões do formulário de visualização, caso seja omitido o plugin irá criar o botão fechar como padrão.
	 * - arrFileCss - Outros arquivos do tipo css.
	 * - arrFileJs  - Outros arquivos do tipo Js.
	 *
	 * @param 	integer $vlrid 		Id do registro a ser visualizado.
	 * @param 	array 	$params 	Parãmetros para vizualiação.
	 * @return 	void
	 */
	public function visualizar($params=[])
	{
		$retorno 		= [];
		$url 			= $this->url;
		$modelClass 	= $this->controller->modelClass;
		$pk 			= $this->controller->$modelClass->primaryKey;
		$displayField 	= $this->controller->$modelClass->displayField;
		$visualizar 	= true;

		if (isset($params['recursive']))
		{
			$this->controller->$modelClass->recursive = $params['recursive'];
		}

		// atualizando esquema
		$this->controller->$modelClass->setEsquema($this->controller->$modelClass);
		$this->controller->request->esquema = $this->controller->$modelClass->esquema;
		$esquema = $this->controller->request->esquema;

		// definindo a view e o caminho da view
		if (isset($params['useViewPlugin']))
		{
			$this->controller->view 	= isset($params['template']) ? $params['template'] : 'editar';
			$this->controller->viewPath	= '..'.DS.'Plugin'.DS.'Grid'.DS.'View'.DS.'Grid';
		}

		try
		{
			$vlrId = isset($this->controller->request->params['pass'][0])
	            ? getMaskId($this->controller->request->params['pass'][0],1)
	            : 0;
	        if (!$vlrId)
	        {
	        	throw new Exception(Mensagem::MSGI016(), 1);
	        }
	        if (empty($pk))
            {
            	throw new Exception(Mensagem::MSGI017(), 2);
            }
            if (empty($esquema))
            {
            	throw new Exception(Mensagem::MSGI018(), 3);
            }

			$this->controller->request->data = $this->controller->$modelClass->find('first',['conditions'=>[$modelClass.'.'.$pk=>$vlrId]]);

			// salvando o último acesso na sessão
			$lastId['id'] 	= $this->controller->request->params['pass'][0];
			$lastId['hora'] = date('H:i:s');
			$this->controller->Session->write($this->chave.'.lastId',$lastId);

			// campos de edição
			$editFields = isset($params['editFields']) ? $params['editFields'] : [];
			if (empty($editFields))
			{
				foreach($esquema as $_field => $_arrProp)
				{
					$f = explode('.',$_field);
					$editFields[] = $_field;
				}
			}

			// títulos
			$arrTitles = isset($params['arrTitles']) ? $params['arrTitles'] : [];
			if (empty($arrTitles))
			{
				$arrTitles[0] = 'Visualizando '.$this->controller->name;
			}

			// botões
			$arrButtons = isset($params['arrButtons']) ? $params['arrButtons'] : [];
			if (is_array($arrButtons) && empty($params['arrButtons']))
			{
				$arrButtons[0]['text'] = 'Fechar';
				$arrButtons[0]['url']  = $this->url;
			}

			// variávies request
			$this->controller->request->visualizar 	= true;
			$this->controller->request->esquema 	= $esquema;
			$this->controller->request->editFields 	= $editFields;
			$this->controller->request->associations= $this->controller->$modelClass->getAssociated();
			$this->controller->request->separador 	= isset($params['separador']) ? $params['separador'] : ':';

			$this->controller->set(compact('arrTitles','arrButtons'));

			if (isset($params['arrFileCss']))
			{
				$this->controller->viewVars['arrFileCss'] = $params['arrFileCss'];
			}
			if (isset($params['arrFileJs']))
			{
				$this->controller->viewVars['arrFileJs'] = $params['arrFileJs'];
			}
			
		} catch (Exception $e)
		{
			$this->controller->Flash->error($e->getMessage());
			return $this->controller->redirect(['action'=>'index']);
		}
	}

	/**
	 * Retrona o esquema para ser usada no Javascript da View
	 *
	 * @return 	json
	 */
	public function getEsquemaView()
	{
		$modelClass = $this->controller->modelClass;
		$this->controller->$modelClass->setEsquema($this->controller->$modelClass);
		$this->controller->request->esquema = $this->controller->$modelClass->esquema;
		$_esquema 	= $this->controller->$modelClass->esquema;
		$esquema 	= [];
		foreach($_esquema as $_Field => $_arrProp)
		{
			$alias = isset($_arrProp['alias']) ? $_arrProp['alias'] : $_Mod.$_cmp;
			$esquema[$alias]['title'] = isset($_arrProp['title']) ? $_arrProp['title'] : $alias;
			if (isset($_arrProp['mask']))
			{
				$esquema[$alias]['mask'] = $_arrProp['mask'];
			}
		}

		return $esquema;
	}

	/**
	 * Retorna o valor data para a view.
	 *
	 * @param 	array 	$esquema 	Configurações do esquema de todos os módulos relacionados
	 * @return 	array  	$data 		Array com o resultado de uma pesquisa no banco
	 */
	public function getDataView($data=[])
	{
	    $dataAlias  = [];
	    $lRel       = 0;
	    $lRel2 		= 0;
	    $totalModel = 0;
	    $modelClass = $this->controller->modelClass;
	    $this->controller->$modelClass->setEsquema($this->controller->$modelClass);
		$this->controller->request->esquema = $this->controller->$modelClass->esquema;
	    $esquema 	= $this->controller->$modelClass->esquema;
	    foreach($data as $_Mod => $_arrCmps)
	    {
	    	if(is_array($_arrCmps))
	    	{
	    		$totalModel++;
		        foreach($_arrCmps as $_cmp => $_vlr)
		        {
		            if(!is_array($_vlr))
		            {
		                $alias = isset($esquema[$_Mod.'.'.$_cmp]['alias']) 
		                    ? $esquema[$_Mod.'.'.$_cmp]['alias']
		                    : $_Mod.'.'.$_cmp;

		                $dataAlias[$_Mod][$alias] = $_vlr;
		            } elseif(is_array($_vlr))
		            {
		                foreach($_vlr as $_cmpRel => $_vlrRel)
		                {
		                    if (!is_array($_vlrRel))
		                    {
		                        $alias = isset($esquema[$_Mod.'.'.$_cmpRel]['alias']) 
		                            ? $esquema[$_Mod.'.'.$_cmpRel]['alias']
		                            : $_Mod.'.'.$_cmpRel;

		                        $dataAlias[$_Mod][$lRel][$alias] = $_vlrRel;
		                    } elseif(is_array($_vlrRel))
		                    {
		                    	$totalModel++;
		                    	$dataAlias[$_Mod][$lRel][$_cmpRel] = [];
		                    	$aliasModelRel2 = 'm'.$totalModel;
		                    	foreach($_vlrRel as $_lRel2 => $_arrCmpRel2)
		                    	{
		                    		$cRel2 = 1;
		                    		foreach($_arrCmpRel2 as $_cmpRel2 => $_vlrRel2)
		                    		{
		                    			if (!is_array($_vlrRel2))
		                    			{
		                    				$aliasRel2 = $aliasModelRel2.'c'.$cRel2;
		                    				$dataAlias[$_Mod][$lRel][$_cmpRel][$_lRel2][$aliasRel2] = $_vlrRel2;
		                    				$cRel2++;
		                    			}
		                    		}
		                    	}
		                    } else
		                    {
		                        die($_cmpRel);
		                    }
		                }
		                $lRel++;
		            } else
		            {
		                die('Erro !!!');
		            }
		        }
		    }
	    }

	    return $dataAlias;
	}
}