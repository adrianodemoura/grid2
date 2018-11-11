<?php
/**
 * Component Fake
 *
 * @package 	Grid.Component
 * @author 		adrianodemoura
 */
/**
 * Mantém o componente Fake. Neste componente será possivel popular algum model com dados fake.
 */
class FakeComponent extends Component {
	/**
	 * Controller
	 * Instância controller herdada pelo componente, obrigatório.
	 *
	 * @var 	object
	 */
	private $controller;

	/**
	 * Inicializa o componente instanciando o controller
	 *
	 * @param 	Controller 	Controller
	 * @return 	void
	 */
	public function initialize(Controller $controller) 
	{
		$this->controller = $controller;
	}

	/**
	 * Retorna o status da inclusão de fake.
     *
     * config pode ser:
     * - model: Model corrente.
     * - total: Total de registros que serão incluídos no model.
     * - truncate: Se verdadeiro limpa a tabela do model corrente.
     * - validation: Se verdadeiro ignora o processo de validação.
     * - totalHM: Total de registros para o(s) model(s) HasMany do model corrente.
     * - schema:- Configurações personalizadas do schema, veja mais em getFakeValue.
     * exemplo: schema['Model']['campo'] = ['options'=>[1,2]]
     *
     * @param   array   $config     Configurações para o fake.
     * @return 	array 	$retorno 	Status da operação, no formato retorno[status|msg].
	 */
	public function get_fake(Array $config)
	{
        set_time_limit(0);

        $this->controller->viewPath = '..'.DS.'Plugin'.DS.'Grid'.DS.'View'.DS.'Grid';
        $this->controller->view     = 'ajax';
        $this->controller->layout   = 'Grid.ajax';

		$retorno = [];
		
        try
        {
            if (Configure::read('debug') != 2)
            {
                throw new Exception(__('O DEBUG deve estar ligado !'), 1);
            }
            $config['truncate']     = ($this->controller->request->data['limpar']==1)   ? true : false;
            $config['validation']   = ($this->controller->request->data['validar']==1)  ? true : false;
            $config['total']        = isset($this->controller->request->data['total'])  
                ? $this->controller->request->data['total'] 
                : null;
            if (!isset($config['total']) || ($config['total']<0 && $config['total']>100000))
            {
                throw new Exception(__('O Valor para o campo limite deve estar entre 1 e 100.000 !'), 2);
            }
            $config['model'] = isset($this->controller->request->data['model']) 
                ? $this->controller->request->data['model'] 
                : null;
            if (!isset($config['model']) || empty($config['model']))
            {
            	throw new Exception(__("Model inválido !"), 3);
            }

			$model  = $config['model'];
			$this->controller->loadModel($model);
	        $ds 	= $this->controller->$model->getDataSource();
	        $ds->begin();

            if (method_exists($this->controller->$model, 'getConfigFake'))
            {
            	$configFake = $this->controller->$model->getConfigFake();
            }

            $config['limite'] 		= 10000;
            $config['total']        = isset($config['total'])       ? $config['total']      : 0;
            $config['truncate']     = isset($config['truncate'])    ? $config['truncate']   : false;
            $config['validation']   = isset($config['validation'])  ? $config['validation'] : false;
            $config['atomic']       = isset($config['atomic'])      ? $config['atomic']     : false;
            if (!$config['total'] || $config['total']>$config['limite'])
            {
                throw new Exception(__('O Total de novos registros deve estar entre 1 e '.$config['limite']), 4);
            }

            // recuperando o schema
            $schema = $this->controller->$model->schema();
            ksort($schema);

            // limpando a tabela
            if ($config['truncate'])
            {
                foreach($this->controller->$model->hasMany as $_ModelHM => $_arrPropHM)
                {
                    $res = $this->controller->$model->query('DELETE FROM '.$this->controller->$model->$_ModelHM->table);
                }
                $res = $this->controller->$model->query('DELETE FROM '.$this->controller->$model->table);
            }

            // variáveis locais
            $msg                = '';
            $contador           = 0;
            $data               = [];
            $pk                 = $this->controller->$model->primaryKey;
            $alias              = $this->controller->$model->alias;

            // recuperando os campos BelongsTo
            $arrCmpsBT = [];
            foreach($this->controller->$model->belongsTo as $_ModelBT => $_arrPropBT)
            {
                if ($_arrPropBT['foreignKey'])
                {
                    $schema[$_arrPropBT['foreignKey']]['options']   = array_keys($this->controller->$model->$_ModelBT->find('list'));
                    $options[$_arrPropBT['foreignKey']]             = [];
                    if (empty($schema[$_arrPropBT['foreignKey']]['options']))
                    {
                        throw new Exception(__('Impossível continuar sem a lista de '.$_ModelBT), 5);
                    }
                }
            }

            // concatenando os esquemas personalizados.
            foreach($schema as $_cmp => $_arrProp)
            {
                if (isset($config['schema'][$model][$_cmp]))
                {
                    $schema[$_cmp] = array_merge($_arrProp, $config['schema'][$model][$_cmp]);
                }
                $schema[$_cmp]['model'] = $alias;
            }

            // incrementando data.
            unset($schema[$pk]); // removendo o pk.
            for($i=1; $i<= $config['total']; $i++)
            {
                foreach($schema as $_cmp => $_arrProp)
                {
                    if (!isset($_arrProp['type']))
                    {
                        gravaLog("schema-".$_cmp."-Invalido", $schema[$_cmp]);
                        throw new Exception(__("Campo $_cmp possui propriedades inválida !"), 6);
                    }
                    if (isset($_arrProp['options']) && in_array($_arrProp['type'],['integer','string']))
                    {
                        if (empty($options[$_cmp]))
                        {
                            $options[$_cmp] = $_arrProp['options'];
                        }
                        $optionId   = array_rand($options[$_cmp]);
                        $vlr        = $options[$_cmp][$optionId];
                        unset($options[$_cmp][$optionId]);
                    } else
                    {
                        $vlr = $this->getFakeValue($_cmp, $_arrProp, $i);
                    }
                    $data[$i][$alias][$_cmp] = $vlr;
                }
            }

            // salvando data.
            if (!$this->controller->$model->saveAll($data,['atomic'=>$config['atomic'], 'validate'=>$config['validation']]))
            {
                throw new Exception(Apoio::getUltimaChave($this->controller->$model->validationErrors), 7);
            }
            $retorno['totalAtual'] = $this->controller->$model->find('count', ['recursive'=>-1]);

            // salvando hasMany
            $retorno['totalHM'] = $this->fakeHM($model, $config, $retorno['totalAtual']);

            // calculando o tempo
            $txtTempo   = __('hora(s)');
            $tempo      = date('H:i:s', mktime(0,0,(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"])));
            $arrTempo   = explode(':', $tempo);
            if($arrTempo[0] != '00')
            {
                $txtTempo   = __('horas');
            } elseif ($arrTempo[1] != '00')
            {
                $txtTempo   = __('minuto(s)');
                $tempo      = $arrTempo[1].':'.$arrTempo[2];
            } else
            {
                $txtTempo   = __('segundo(s)');
                $tempo      = $arrTempo[2];
            }

            // atualizando retorno
            $retorno['status']  = true;
            $retorno['msg']     = number_format($config['total'],0,',','.')." de registro(s) inserido(s) com sucesso em $tempo $txtTempo";
            $ds->commit();
        } catch (Exception $e) 
        {
            $erro = $e->getMessage();
            if (method_exists($e, 'getAttributes'))
            {
                $erro = $e->getAttributes();
                $erro = isset($erro['message']) ? utf8_decode($erro['message']) : $e->getMessage();
            }
            $retorno['msg']     = $erro;
            $retorno['status']  = false;
            if ($e->getCode()>3)
            {
                $ds->rollback();
            }
            if (isset($data[1]))
            {
                gravaLog('dataErro', $data[1]);
            }
            if (isset($dataHM[1]))
            {
                gravaLog('dataHMErro', $dataHM[1]);
            }
        }

		$this->controller->set(compact('retorno'));
	}

    /**
     * Retorna o valor fake
     *
     * As propriedades são recuperadas do schema da tabela do model e podem ser:
     * - type, default, length, precison, options, model.
     *
     * Você pode criar o método getConfigFake para customizar estas propriedades.
     *
     * @param   string      $cmp    Nome do campo.
     * @param   array       $prop   Propriedades do campo.
     * @param   integer     $inc    Incremento.
     * @return  mixed       $vlr    Valor fake.
     */
    private function getFakeValue($cmp='', $prop=[], $inc=0)
    {
        $vlr            = $cmp;
        $prop['length'] = isset($prop['precision']) ? $prop['precision'] : $prop['length'];

        switch ($prop['type'])
        {
            case 'string':
                $model  = isset($prop['model']) ? $prop['model'] : '';
                $vlr    = isset($prop['default']) ? $prop['default'] : Inflector::camelize($cmp).' '.$model;
                $vlr    = $vlr . str_repeat(' '.$vlr, $prop['length']);
                $pos    = ($prop['length'] - strlen($inc));
                $vlr    = trim(substr($vlr,0,$pos).$inc);
                $vlr    = substr($vlr,0,$prop['length']);
                break;
            case 'stringRan':
                $prop['keys']   = isset($prop['keys']) ? $prop['keys'] : '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength   = strlen($prop['keys']);
                $vlr                = '';
                for ($a = 0; $a < $prop['length']; $a++)
                {
                    $vlr .= $prop['keys'][rand(0, $charactersLength - 1)];
                }
                $vlr    = substr($vlr,0,$prop['length']);
                break;
            case 'email':
                $vlr    = "$cmp$alias$inc@$cmp.com.br";
                $vlr    = substr($vlr,0,$prop['length']);
                break;
            case 'integer':
                $vlr    = rand(1,$inc);
                $vlr    = substr($vlr,0,$prop['length']);
                break;
            case 'senha':
                $Hasher = new \Cake\Auth\DefaultPasswordHasher();
                $vlr    = $Hasher->hash($cmp);
                break;
            case 'between':
                $vlr   = rand($prop['options'][0], $prop['options'][1]);
                break;
            case 'date':
            case 'datetime':
                $dateFormat = "Y-m-d";
                $dI  = isset($prop['options'][0]) ? $prop['options'][0] : '2000-01-01';
                $dF  = isset($prop['options'][1]) ? $prop['options'][1] : date($dateFormat);
                $vlr = date($dateFormat,rand(strtotime($dI), strtotime($dF)));
                break;
            case 'blob':
            case 'binary':
                if (isset($prop['options']))
                {
                    $vlr = base64_decode($prop['options'][array_rand($prop['options'])]);
                } else
                {
                    $vlr = base64_decode("iVBORw0KGgoAAAANSUhEUgAAADIAAAAqCAIAAAB+2J8QAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4gQKCzki2tR1MgAABMFJREFUWMO1WFtzsyAQlZVoFG+5tZ3M9P//ov6A9rXTpN41UfketkMoIDXTfvvAeAMPZw+7C+Tl5YUQ4jjOXGt/qF8bjXMOAAAwDIP8ULTKLbVg+iUsQgjnnHNOCNlsNnEcO47z+vqqfIyfCWT4lipDL8FkwacwwRgLwzAMQ/E8DMOu6/CtAIQXYhzOOZVBGDFZHDrHGQAkSYL0KMYY6/ten4NAdnOiEdOSVqcqSZIgCDzPmxMZY+zj4wN7KQzJnFELB0ta1PJqtYqiiDGm03C9XsuybJrm+fkZe8VxXNe1QGD0I7V7x0ihGAUAsixjjAGAAmiapqIo2radpgm/r6oqiiLHcdI0bZpG0bjiUKosiiUyAoAgCIIgkLUs0HRd17atEJAYpG1bhOW6LqV0GAZFUnJLFY3rSpfBAUCapkYtC3rmluf1ep2mCXkNw7AoCqOwvsUtS2jAr+M4ZozpWuacV1XV9/3lctGnp8SLuq5xSnEc53kOALLCVMlbogBqOUkS/TfDMFRV1batINIS5fEXZVkiLEIIYwz7KpL/YkuRnnidZVmSJK7r6oDyPJdD4pIsJD7uum69XjuOE0URDmJmS+EpDMMoilCbuvV933UdISQMQ6Xjj6YsOvSDMT9WVXVz4na7zbLM/hvf933fd/7CCCG6NtC6rqOO4xyPR32pc86LomiaZrvd/hWU5Ygp57xtWwEL43Ke51VVAYBM3jiOGBvtI1q0ZbfVaiWuKSHkdDrtdju8P51OZVniylL+UVVVXdf3lhIKLGN1hfXP8Xi8RWyRGfB+Tux3VYVzAUL/bK4XCCbwwvM8ItlCH90rHTlWG0f+ChB1XYvXURSVZfkjAgCIosh13XEcxayUfy+RlI2taZrO5zNeb7fbH2VLCNntdlhaBUGw2+3uKu2XwgKAPM9FLA6CwL7KPM+Tsw2WXJZ1Rym1qHYWFhJ2uVxEDWkhjHNOKdV9aokISZL4vr+cRZD7C4kwxqZpmlMGFk/KQ3mnpcwBJQgAYRhiLFi6EtE+Pz9l4Suw5NtpmjC8oZVleb1e5/KVkARjzHVd3/fTNDVuEm9OV54WRYGparPZiNLWmHTrum6ahlI6juNcBawHwv1+L+BiBWv0CSixRHAAAJRSvY8SnbH2lSctW5qmemmEFgTBHFU3WOLF5XIRmwJj/ja2xqE9z7PszHDlyr3koUAXTVEUeJ2mqZybFRxGZDJzWZZZ1prrunLIVWYFyqCEEBFXsbSdy7hylvV9/+HhARcaPrRjEiUnTvtnJ+JvRC6SKzUjJhQv1o9xHO/3e0KI3X3KajA6AYz6kDO3wpbyMaVUTlaU0sPhsIQqOTcY2DJWQoItAJBLU3mVoT4Oh8NcDbPEfN83a2uuOhOh1bKNeXx8/GVKXq/XSty/wTJWjDosmSdCyNPT0+9rL9yc6dqi8qZR3kOO49j3vbK5EG8B4P393VLfGaOdfPBnXENq8jEeMlVVpQhLtCIDLi+z5sp5XVhfbCk8iTmdz2ex9UCBixg4R8zynY8xVXw7pNTPTASyPM/TNBVRW1z/p+3hNyfOnTNhQSFD+ZMtxhKjlrMvQkjf9+M42k9j/ty+nDjnQXz19vamH3rNkXevtozhaRiGf91y+1EsVDT+AAAAAElFTkSuQmCC");
                }
                break;
        }

        return $vlr;
    }

    /**
     * Executa o fake para a tabela do tipo hasMany
     *
     * @param   string  $model      Nome da Model pai.
     * @param   array   $config     Configurações fake do Model.
     * @param   integer $totalM     Total do Model pai.
     * @return  array   $retorno    Status da operação, contendo o total de registro por cada hasMany.
     */
    private function fakeHM($model='', $config=[], $totalM=0)
    {
        $retorno = [];
        $pk      = $this->controller->$model->primaryKey;

        // salvando hasMany
        foreach($this->controller->$model->hasMany as $_ModelHM => $_arrPropHM)
        {
            $totalHM    = isset($config[$_ModelHM]['totalHM']) ? $config[$_ModelHM]['totalHM'] : rand(2,4);
            $schemaHM   = $this->controller->$model->$_ModelHM->schema();
            $pkHM       = $this->controller->$model->$_ModelHM->primaryKey;
            $aliasHM    = $this->controller->$model->$_ModelHM->alias;
            $dataHM     = [];
            ksort($schemaHM);

            foreach($this->controller->$model->$_ModelHM->belongsTo as $_ModelBT => $_arrPropBT)
            {
                if ($_arrPropBT['foreignKey'])
                {
                    $schemaHM[$_arrPropBT['foreignKey']]['options'] = array_keys($this->controller->$model->$_ModelHM->$_ModelBT->find('list'));
                    $optionsHM[$_arrPropBT['foreignKey']] = [];
                    if (empty($schemaHM[$_arrPropBT['foreignKey']]['options']))
                    {
                        throw new Exception(__('Impossível continuar sem a lista de '.$_ModelBT), 4);
                    }
                }
            }

            // concatenando os esquemas personalizados do ModelHM.
            if (method_exists($this->controller->$model->$_ModelHM, 'getConfigFake'))
            {
                $configFakeHM = $this->controller->$model->$_ModelHM->getConfigFake();
                foreach($schemaHM as $_cmp => $_arrProp)
                {
                    if (isset($configFakeHM[$_cmp]))
                    {
                        $schemaHM[$_cmp] = array_merge($_arrProp, $configFakeHM[$_cmp]);
                    }
                }
            }

            // recuperando as opções do model.
            $optionsModel   = [];
            $_optionsModel  = $this->controller->$model->query("SELECT $pk FROM ".$this->controller->$model->table);
            foreach($_optionsModel as $_l => $_arrL)
            {
                $optionsModel[] = $_arrL[0][$pk];
            }
            $schemaHM[$pk]['options'] = $optionsModel;

            // incrementando o dataHM
            unset($schemaHM[$pkHM]);
            $c = 1;
            for($a=1; $a<=$totalM; $a++)
            {
                for($i=1; $i<=$totalHM; $i++)
                {
                    foreach($schemaHM as $_cmp => $_arrProp)
                    {
                        if (isset($_arrProp['options']) && in_array($_arrProp['type'],['integer','string']))
                        {
                            if (empty($optionsHM[$_cmp]))
                            {
                                $optionsHM[$_cmp] = $_arrProp['options'];
                            }
                            $optionId   = array_rand($optionsHM[$_cmp]);
                            $vlr        = $optionsHM[$_cmp][$optionId];
                            unset($optionsHM[$_cmp][$optionId]);
                        } else
                        {
                            $vlr = $this->getFakeValue($_cmp, $_arrProp, $c);
                        }

                        $dataHM[$c][$aliasHM][$_cmp] = $vlr;
                    }
                    $c++;
                }
            }

            if (!$this->controller->$model->$_ModelHM->saveAll($dataHM,['atomic'=>$config['atomic'], 'validate'=>$config['validation']]))
            {
                throw new Exception(Apoio::getUltimaChave($this->controller->$model->$_ModelHM->validationErrors), 5);
            } else
            {
                $retorno['totalHM'][$_ModelHM] = !$this->controller->$model->$_ModelHM->find('count',['recursive'=>-1]);
            }
        }

        return $retorno;
    }
}