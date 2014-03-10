<?php
/**
 * Core Controller
 * 
 * @package		Core
 * @subpackage	Core.Controller
 */
class Controller {
	/**
	 * Módulo
	 * 
	 * @var		string
	 * @access 	public
	 */
	public $module 		= null;

	/**
	 * Controller
	 * 
	 * @var		string
	 * @access 	public
	 */
	public $controller	= null;

	/**
	 * Action
	 * 
	 * @var		string
	 * @access 	public
	 */
	public $action 		= null;

	/**
	 * Layout padrão
	 * 
	 * @var		string
	 */
	public $layout 		= 'padrao';

	/**
	 * Nome da classe do model
	 *
	 @var 		string
	 @access 	public
	 */
	public $modelClass	= '';

	/**
	 * Model do controlador
	 * 
	 * @var		mixed
	 */
	public $Model		= array();

	/**
	 * Variáveis da visão
	 * 
	 * @var		mixed
	 */
	public $viewVars	= array();

	/**
	 * caminha da view
	 * 
	 * @var		mixed
	 */
	public $viewPath	= null;

	/**
	 * Dados do formulário data
	 * 
	 * @var		array
	 * @access	public
	 */
	public $data		= array();

	/**
	 * Start do controller
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// variávies obrigatórias para a view
		$this->viewVars['base'] 			= '';
		$this->viewVars['aqui'] 			= '';
		$this->viewVars['se'] 				= '';
		$this->viewVars['tituloPagina'] 	= '';
		$this->viewVars['tituloModule'] 	= '';
		$this->viewVars['tituloController'] = '';
		$this->viewVars['tituloAction'] 	= '';
		$this->viewVars['position'] 		= '';
		$this->viewVars['tempoOn'] 			= 20;
		$this->viewVars['primaryKey'] 		= array();
		$this->viewVars['permissoes'] 		= array();
		$this->viewVars['paginacao'] 		= array();
		$this->viewVars['params'] 			= array();
		$this->viewVars['msgFlash'] 		= array();
		$this->viewVars['head'] 			= array();
		$this->viewVars['onRead'] 			= array();
		$this->viewVars['esquema'] 			= array();
		
		// configurando o base
		$this->viewVars['base'] = getBase();

		// configura o aqui
		$aqui = isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'http';
		$aqui .= '://'.$_SERVER['SERVER_NAME'];
		$this->viewVars['aqui'] = $aqui.$_SERVER['REQUEST_URI'];
	}

	/**
	 * Primeira função do index
	 * 
	 * @return	void
	 */
	public function index()
	{
		if (isset($_SESSION['Usuario']))
		{
			$modelClass = $this->modelClass;
			$params['pag'] = 1;
			$params['ord'] = $this->$modelClass->getDisplayField();
			$params['dir'] = 'asc';
			$this->redirect(strtolower($this->module), strtolower($this->controller), 'lista',$params);
		} else $this->redirect('sistema', 'usuarios', 'login');
	}

	/**
	 * Executa código antes da action
	 * 
	 * @return	void
	 */
	public function beforeIndex()
	{
	}

	/**
	 * Executa código depois da action
	 * 
	 * @return	void
	 */
	public function afterIndex()
	{
	}

	/**
	 * Executa código antes da renderização da View
	 * 
	 * @return	void
	 */
	public function beforeRender()
	{
	}

	/**
	 * Configura a mensagen Flahs, usada no topo do cabeçalho
	 * 
	 * @param	string	$texto	Texto da mensagen
	 * @param	string	$class	Classe que vai ser usada na div msg_flash
	 * @return	void
	 */
	public function setMsgFlash($texto='', $class='')
	{
		$_SESSION['msgFlash']['txt']	= $texto;
		$_SESSION['msgFlash']['class'] 	= $class;
	}

	/**
	 * Redirecion o fluxo da request
	 * 
	 * @param	string	$con	Controller
	 * @param	string	$act	Action
	 * @param	string	$plug	Plugin
	 * @param	string	$par	Parâmetros
	 * @return	void
	 */
	public function redirect($mod='', $con='', $act='', $par=array())
	{
		$url = $this->base.$mod.'/'.$con.'/'.$act;
		if (!empty($par))
		{
			foreach($par as $_l => $_vlr)
			{
				$url .= '/'.$_l.':'.$_vlr;
			}
		}
		header('Location: '.$url);
		die();
	}

	/**
	 * Exibe a tela de listagem do cadastro corrente
	 *
	 * @param 	array 	
	 * @return 	void
	 */
	public function lista()
	{
		$modelClass 		= $this->modelClass;
		$params 			= array();
		$params['pag'] 		= isset($this->params['pag']) ? $this->params['pag'] : 1;
		$params['pag']		= empty($params['pag']) ? 1 : $params['pag'];
		$params['order'] 	= isset($this->params['ord']) ? $this->params['ord'] : array();
		$params['direc'] 	= isset($this->params['dir']) ? $this->params['dir'] : 'ASC';
		$this->data 		= $this->$modelClass->find('all',$params);
		if (!isset($this->viewVars['fields']))
		{
			$fields = array();
			foreach($this->data as $_l => $_arrMods)
			{
				foreach($_arrMods as $_mod => $_arrCmps)
				{
					foreach($_arrCmps as $_cmp => $_vlr)
					{
						if ($_mod==$modelClass && !in_array($_cmp,$this->$modelClass->primaryKey)) array_push($fields,$_mod.'.'.$_cmp);
					}
				}
				break;
			}
			$this->viewVars['fields'] = $fields;
		}
		$this->viewVars['urlRetorno'] = isset($this->viewVars['urlRetorno']) ? $this->viewVars['urlRetorno'] : $this->viewVars['aqui'];
	}

	/**
	 * Salva o formulário postado no banco de dados
	 * 
	 * @param	array	Formulário com o nome data
	 * @return	void
	 */
	public function salvar()
	{
		$modelClass = $this->modelClass;
		if (!$this->$modelClass->save($this->data))
		{
			$this->viewVars['msgErro'] = $this->$modelClass->erro;
		} else
		{
			$this->viewVars['msgOk'] = 'Os Registros foram salvos com sucesso !!!';
			$this->viewVars['dados'] = $this->data;
		}
		$this->viewVars['urlRetorno'] = isset($_POST['urlRetorno']) ? $_POST['urlRetorno'] : '';
		if (!empty($this->viewVars['urlRetorno']))
		{
			$msg = 'O Registro foi salvo com sucesso ...';
			if (isset($this->data['1'])) $msg = 'Os Registros foram salvos com sucesso ...';
			$this->setMsgFlash($msg,'msgFlashOk');
			header('Location: '.$this->viewVars['urlRetorno']);
			die();
		}
	}
}
