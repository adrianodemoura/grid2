<?php
/**
 * class FormatDataBehavior
 *
 * @package 	Grid2.Database.Behaviour
 * @author 		adrianodemoura
 * @link 		https://book.cakephp.org/2.0/en/models/behaviors.html
 */
/**
 * Mantem o behavior 
 */
class FormatDataBehavior extends ModelBehavior {
	/**
	 * Se verdadeiro executa o método afterFind.
	 *
	 * @var 	boolean
	 */
	private $executeAfterFind = true;

	/**
	 * Se verdadeiro executa o método beforeValidate.
	 *
	 * @var 	boolean
	 */
	private $executeBeforeValidate = true;

	/**
	 * Se verdadeiro executa o método beforeFind.
	 *
	 * @var 	boolean
	 */
	private $executeBeforeFind = true;

	/**
     * Retorna o valor do atributo.
     *
     * @param   string  $atribuito  Nome do atributo.
     * @return  string 	Valor do atributo.
     */
    public function __get($atributo='')
    {
        return $this->$atributo;
    }

	/**
     * Configura o atributo do Behavior
     *
     * @param   string  $atribuito  Nome do atributo.
     * @param   mixed   $vlr        Valor do atributo.
     * @return  void
     */
    public function __set($atributo='', $vlr='')
    {
        $this->$atributo = $vlr;
    }

	/**
	 * Retorna esquema a partir da estrutura da tabela
	 *
	 * @param 	array 	$schema 	Matriz com o retorno da função Model->schema()
	 * @return 	array 	$esquema 
	 */
	public function getSchema(Model $model)
	{
		$schema 	= $model->schema();
		$esquema 	= [];
		$l 			= 1;
		foreach($schema as $_cmp => $_arrProp)
		{
			$field = $model->name.'.'.$_cmp;
			$_arrProp['alias'] = 'mo1ca'.$l;
			$_arrProp['title'] = camelCase($_cmp);
			if (in_array(strtolower($_arrProp['type']),['date','datetime']))
			{
				$_arrProp['saveFormat'] 	= 'Y-m-d H:i:s';
				$_arrProp['displayFormat'] 	= 'd/m/Y H:i:s';
			}
			$esquema[$field] = $_arrProp;
			$l++;
		}

		return $esquema;
	}

	/**
	 * Re-configura o esquema do model, caso o mesmo não se encontre no cache.
	 *
	 * @return 	array
	 */
	public function setEsquema(Model $model)
	{
		$esquema = Cache::read('esquema'.$model->useTable);
		if (!$esquema)
		{
			$assocs = ['belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany'];
			if (method_exists($model, 'beforeSetEsquema'))
			{
				$model->beforeSetEsquema();
			}
			$esquema = isset($model->esquema) 
				? $model->esquema 
				: [];

			// se o esquema não foi criado
			if (empty($esquema))
			{
				$esquema = $this->getSchema($model);
			}

	        // recuperando o esquema dos associados
	        $campo 	= (count($esquema)+1);
	        $modelC = '';
	        $countM = 1;
			foreach($assocs as $_l => $_assoc)
			{
				if (!empty($model->$_assoc))
				{
					foreach($model->$_assoc as $_model => $_arrPropAssoc)
					{
						if ($_model != $modelC)
						{
							$modelC = $_model;
							$countM++;
							$countC = 1;
						}
						if (method_exists($model->$_model, 'beforeSetEsquema'))
						{
							$model->$_model->beforeSetEsquema();
						}
						$esquemaAssoc = isset($model->$_model->esquema) ? $model->$_model->esquema : [];
						foreach($esquemaAssoc as $_cmpAssoc => $_arrPropEsquemaAssoc)
						{
							$beforeEsquema 					= isset($esquema[$_cmpAssoc]) ? $esquema[$_cmpAssoc] : [];
							$esquema[$_cmpAssoc] 			= $_arrPropEsquemaAssoc;
							$esquema[$_cmpAssoc]['title'] 	= isset($esquema[$_cmpAssoc]['title']) 
								? $esquema[$_cmpAssoc]['title']
								: $_cmpAssoc;
							$esquema[$_cmpAssoc]['alias'] = isset($model->esquema[$_cmpAssoc]['alias'])
								? $model->esquema[$_cmpAssoc]['alias']
								: "m".$countM."c".$countC;
							$esquema[$_cmpAssoc]['origem'] = $_assoc;
							$countC++;
							if (!empty($beforeEsquema))
							{
								foreach($beforeEsquema as $_tag => $_vlr)
								{
									$esquema[$_cmpAssoc][$_tag] = $_vlr;
								}
							}
						}
					}
				}
			}

			foreach($esquema as $_field => $_arrProp)
	        {
	        	$esquema[$_field] = isset($model->esquema[$_field]) ? $model->esquema[$_field] : $_arrProp;

	            $esquema[$_field]['title'] = isset($_arrProp['title']) ? $_arrProp['title'] : $_field;
	            $esquema[$_field]['alias'] = isset($_arrProp['alias']) ? $_arrProp['alias'] : $_field;
	            $esquema[$_field]['input']['type'] = isset($_arrProp['input']['type']) ? $_arrProp['input']['type'] : 'text';
	            $esquema[$_field]['input']['label'] = isset($_arrProp['input']['label']) ? $_arrProp['input']['label'] : $esquema[$_field]['title'];
	            unset($esquema[$_field]['filter']);
	            if (isset($esquema[$_field]['input']['options']))
	            {
	                $esquema[$_field]['input']['type'] = 'select';
	            }
	        }

	        Cache::write('esquema'.$model->useTable,$esquema);
	    }

	    $model->esquema = $esquema;
	}

	/**
	 * Executa código antes do método validate, aqui pode-se reconfigurar alguns valores.
	 *
	 * @param 	Model 	$model 		Model corrente usado neste behavior
	 * @param 	array 	$options 	Opções oriunda do método Model::save().
	 * @return 	mixed 	False or null 	Falso para abortar ou nulo para continuar.
	 * @see Model::save()
	 */
	public function beforeValidate(Model $model, $options = array()) 
	{
		if ($this->executeBeforeValidate===true)
		{
			// recuperando registro original do banco

			// re-configurando alguns campos e complentando com valor original
			foreach($model->data as $_Mod => $_arrCmps)
			{
				foreach($_arrCmps as $_cmp => $_vlr)
				{
					$prop  = isset($model->esquema[$_Mod.'.'.$_cmp]) ? $model->esquema[$_Mod.'.'.$_cmp] : [];
					$model->data[$_Mod][$_cmp] = $this->saveFormat($_vlr,$prop);
				}
			}
		}

		return true;
	}

	/**
 	 * Executa código antes do método find, aqui pode-se costumizar a query bem como alguns valores.
 	 * Executa também a formatação de cada campo para o formato SQL.
 	 *
 	 * @param       Model   $model  Model corrente
 	 * @param       array   $query  Array com propriedades da consulta
 	 * @return      array   $query
 	 * @link        https://book.cakephp.org/2.0/en/models/behaviors.html#behavior-callbacks
 	 */
 	public function beforeFind(Model $model, $query)
 	{
 		if ($this->executeBeforeFind===true)
 		{
 			if (isset($query['conditions'])) // formatando os campos para formato Save
 			{
 				foreach($query['conditions'] as $_cmp => $_vlr)
 				{
 					$prop = isset($model->esquema[$_cmp]) ? $model->esquema[$_cmp] : [];
 					$query['conditions'][$_cmp] = $this->saveFormat($_vlr,$prop);
 				}
 			}
 		}

        return $query;
	}

	/**
	 * Executa código após o método find, aqui pode-se modificar o resultado.
	 * - Campos no formato data são reconfigurados para seu formato conforme Model->esquema['displayFormat'].
	 * - Campos no formato HasMany e HABTM são incrementados num campo virtual.
	 * - Campos com máscara são mascarados aqui.
	 *
	 * @param 	Model 	$model 		Model
	 * @param 	array 	$results 	Resultado passados pelo método find.
	 * @param 	boolean $primary 	Se a conulta foi pelo pk.
	 * @return 	array 	$results 	Resultado possivelmente formatado.
	 */
	public function afterFind(Model $model, $results, $primary = false)
	{
		// formatando os campos para formato displayField
		if ($this->executeAfterFind===true)
		{
			if (isset($results[0]))
			{
				foreach($results as $_l => $_arrMods)
				{
					foreach($_arrMods as $_Mod => $_arrCmps)
					{
						foreach($_arrCmps as $_cmp => $_vlr)
						{
							if (is_array($_vlr))
							{
								foreach($_vlr as $_cmpRel => $_vlrRel)
								{
									$prop = isset($model->esquema[$_Mod.'.'.$_cmpRel]) ? $model->esquema[$_Mod.'.'.$_cmpRel] : [];
									if (empty($prop))
									{
										if (isset($model->$_Mod->esquema[$_Mod.'.'.$_cmpRel]))
										{
											$prop = $model->$_Mod->esquema[$_Mod.'.'.$_cmpRel];
										}
									}
									$results[$_l][$_Mod][$_cmp][$_cmpRel] = $this->displayField($_vlrRel,$prop);
								}
							} else
							{
								$prop = isset($model->esquema[$_Mod.'.'.$_cmp]) ? $model->esquema[$_Mod.'.'.$_cmp] : [];
								if (empty($prop))
								{
									if (isset($model->$_Mod->esquema[$_Mod.'.'.$_cmp]))
									{
										$prop = $model->$_Mod->esquema[$_Mod.'.'.$_cmp];
									}
								}
								$results[$_l][$_Mod][$_cmp] = $this->displayField($_vlr, $prop);
							}
						}
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Retorn o valor no formato para SQL
	 * - Formata campos do tipo date e datetime
	 * - Formata campos com máscara, removendo-a, caso queira salvar com masca crie a propriedade esquema.campo.saveMask
	 * - Formata campos do tipo decimal, removenod pontos e trocando virgula por ponto.
	 *
	 * @return mixed/string 	$vlr;
	 */
	private function saveFormat($vlr='', $prop=[])
	{
		$prop['type'] = isset($prop['type']) ? strtolower($prop['type']) : 'text';

		if ($prop['type']=='integer')
		{
			$vlr = (int) $vlr;
		}
		if ($prop['type']=='float')
		{
			$vlr = (float) $vlr;
		}
		if (in_array($prop['type'],['text','string','texto']))
		{
			$vlr = str_replace("'","''",$vlr);
		}

		// se tem máscara
		if ( isset($prop['mask']) && !isset($prop['saveMask']) )
		{
			$removeChar = str_replace(['#','9','0'],'',$prop['mask']);
			for($i=0; $i<strlen($removeChar); $i++)
			{
				$vlr = str_replace($removeChar[$i],'',$vlr);
			}
		}

		// se é decimal
		if ($prop['type']=='decimal')
		{
			$vlr = str_replace('.','',$vlr);
			$vlr = str_replace(',','.',$vlr);
		}

		// se é um campo data ou datetime
		if ((in_array($prop['type'],['date','datetime'])) && !empty($vlr))
		{
			$displayFormat 	= isset($prop['displayFormat']) ? $prop['displayFormat'] 	: 'd/m/Y H:i:s';
			$saveFormat 	= isset($prop['saveFormat']) 	? $prop['saveFormat'] 		: 'Y-m-d H:i:s';
			if (strlen($vlr)<11)
			{
				$vlr .= '00:00:00';
			}
			$vlrDate 		= date_create_from_format($displayFormat,$vlr);
			$vlr 			= is_a($vlrDate,'DateTime')
				? date_format($vlrDate,$saveFormat)
				: $vlr;
		}

		// se pediu pra limpar a máscara
		if (isset($prop['limpaMascara']))
		{
			$vlr = str_replace($prop['limpaMascara'],'',$vlr);
		}

		return $vlr;
	}

	/**
	 * Retorna o valor para campo data conforme o atributo displayFormat
	 *
	 * @param 	strnig 	$vlr 	Valor a ser configurado
	 * @param 	array 	$prop 	Propriedades do campo, herdada no Model->esquema
	 * @return 	string
	 */
	private function displayField($vlr='', $prop=[])
	{
		$prop['type'] = isset($prop['type']) ? strtolower($prop['type']) : 'text';

		// máscara
		if (isset($prop['mask']) && strlen(trim($vlr)))
		{
			if ($prop['mask']=='##.###.###/####-##' && strlen($vlr)<14)
			{
				$prop['mask'] = "###.###.###-##";
			}
			if ($prop['mask']=='###.###.###-##' && strlen($vlr)>11)
			{
				$prop['mask'] = "##.###.###/####-##";
			}
			$vlr = mascarado($vlr, $prop['mask']);
		}

		// tipo
		if (isset($prop['type']))
		{
			if ($prop['type']=='integer')
			{
				$vlr = (int) $vlr;
			}
			if ($prop['type']=='float')
			{
				$vlr = (float) $vlr;
			}
			if ($prop['type']=='decimal')
			{
				$vlr = getFormatDecimalBrasil($vlr);
			}
			if ($prop['type']=='cpf_cnpj')
			{
				$mask = (strlen($vlr)<12) ? "###.###.###-##" : "##.###.###/####-##";
				$vlr = mascarado($vlr, $mask);
			}
		}

		// se é um campo data ou datetime
		if (in_array($prop['type'],['date','datetime']))
		{
			if (!empty($vlr))
			{

				$displayFormat 	= isset($prop['displayFormat']) ? $prop['displayFormat'] 	: 'd/m/Y H:i:s';
				$saveFormat 	= isset($prop['saveFormat']) 	? $prop['saveFormat'] 		: 'Y-m-d H:i:s';
				$vlrDate 		= date_create_from_format($saveFormat,$vlr);
				$vlr 			= is_a($vlrDate,'DateTime') ? date_format($vlrDate,$displayFormat) : $vlr;
				if (substr($vlr,11,8)=='00:00:00')
				{
					$vlr = substr($vlr,0,10);
				}
			} else
			{
				$vlr = '';
			}
		}

		return $vlr;
	}
}