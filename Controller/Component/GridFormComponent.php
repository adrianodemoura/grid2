<?php
/**
 * Component GridForm
 *
 * @package 	GridForm.Component
 * @author 		adrianodemoura
 */
/**
 * Mantém o componente GridForm.
 */
class GridFormComponent extends Component {
	/**
	 * Controller
	 * Instância controller herdada pelo componente, obrigatório.
	 *
	 * @var 	object
	 */
	private $controller;

	/**
	 * Sufixo o elemento paginador
	 *
	 * @var 	string
	 */
	private $sufixo = 'selPag';

	/**
	 * Parâmetros
	 * Parâmetros do formulário
	 *
	 * @var 	array
	 */
	protected $params = [];

	/**
	 * Novo token
	 * A cada validação do token o mesmo é renovado.
	 *
	 * @var 	string
	 */
	protected $novoToken = '';

	/**
	 * Inicializa o componente instanciando o controller
	 *
	 * @param 	Controller 	Controller
	 * @return 	void
	 */
	public function initialize(Controller $controller) 
	{
		$this->controller 					= $controller;
		$this->controller->request->sufixo 	= $this->sufixo;
	}

	/**
	 * verifica se o token postado é o mesmo token do cache.
	 *
	 * @return 	string
	 */
	public function validaToken($data=[])
	{
		$erro = '';
		try
        {
			// recuperando os parâmetros.
	        $name   = @$data['name'];
	        $filtro = @$data['filtro'];
	        $campo  = @$data['campo'];
	        $token  = @$data['token'];
	        $pagina = isset($data['pagina']) ? (int) $data['pagina'] : 1;
	        $limite = isset($data['limite']) ? (int) $data['limite'] : 50;
	        $this->log($pagina, 'debug');
	        $this->log($data, 'debug');

	        // validando os parâmetros.
	        if (empty($name))
	        {
	            throw new Exception(__("O parâmetro *name* é obrigatório !"), 1);
	        }
	        if (empty($campo))
	        {
	            throw new Exception(__("O parâmetro *campo* é obrigatório !"), 2);
	        }
	        if (empty($token))
	        {
	            throw new Exception(__("O parâmetro *token* é obrigatório !"), 3);
	        }
	        if (!is_int($pagina))
	        {
	            throw new Exception(__("O parâmetro *pagina* é obrigatório !"), 4);
	        }

	        // recupera o token
	        $nomeToken  = $this->sufixo.$name;
	        $cache      = Cache::read($nomeToken);
	        if (empty($cache))
	        {
	            throw new Exception(__("Não foi possível recupear o token !"), 5);
	        }
	        if ($cache['token'] != $token)
	        {
	            throw new Exception("Token incompatível !", 6);
	        }
	        if ($cache['time']>strtotime('now'))
	        {
	            throw new Exception("Token com horário incompatível !", 7);
	        }
	        $novoToken = getToken($nomeToken);
	        Cache::write($nomeToken, ['token'=>$novoToken, 'time'=>strtotime('now')]);

	        $this->params 		= ['name'=>$name, 'pagina'=>$pagina, 'limite'=>$limite, 'campo'=>$campo, 'filtro'=>$filtro, 'token'=>$token];
	        $this->novoToken 	= $novoToken;

	    } catch (Exception $e)
        {
        	$erro = $e->getMessage();
        }

		return $erro;
	}
}