<?php
/**
 * Class Boot
 * 
 * @package		Core
 * @subpackage	Core.Boot
 */
class Boot {
	/**
	 * Dados do formulário data
	 * 
	 * @var 	array
	 * @access	public
	 */
	public $data 	= array();

	/**
	 * Variáveis da view
	 * 
	 * @var		array
	 * @access	public
	 */
	public $viewVars = array();

	/**
	 * Renderiza o layout a view solicitada via GET
	 *
	 * - Verifica a permissão do perfil logado
	 * 
	 * @access	public
	 * @return	void
	 */
	public function render()
	{
		header('Content-Type: text/html; charset=utf-8');

		// fuso-horário
		date_default_timezone_set('America/Sao_Paulo');

		// exibindo todos os erros
		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		// incluido utilitários
		require_once(CORE.'Util/Util.php');

		// incluindo bootstrap
		require_once(APP.'Config/bootstrap.php');

		// incluindo security pra sessão
		require_once(VENDOR.'PHPSecureSession/SecureSession.php');
		session_save_path(sys_get_temp_dir());

		// sessão
		session_name(md5('seg'.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])); 
		session_start();

		if (array_key_exists('HTTP_USER_AGENT', $_SESSION))
		{
		    if ($_SESSION['HTTP_USER_AGENT'] !=
		        md5($_SERVER['HTTP_USER_AGENT']))
		    {
		      /* Acesso inválido. O header User-Agent mudou
		       durante a mesma sessão. */
		      exit;
		    }
		}
		else
		{
		  /* Primeiro acesso do usuário, vamos gravar na sessão um
		   hash md5 do header User-Agent */
		    $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
		}

		// configurando, módulo, controler, action e parâmetros
		$_url 	= explode('/',$_SERVER['REQUEST_URI']);
		$url 	= array();
		$params	= array();
		$module	= '';
		$l		= 0;
		foreach($_url as $_l => $_u)
		{
			if (!empty($_u)) if (!strpos($_SERVER['SCRIPT_FILENAME'],$_u) || $_u=='index')
			{
				$url[$l] = $_u;
				$l++;
			}
		}
		$url['0'] = isset($url['0']) ? ucfirst(strtolower($url['0'])) : 'Sistema';
		$url['1'] = isset($url['1']) ? ucfirst(strtolower($url['1'])) : 'Usuarios';
		$url['2'] = isset($url['2']) ? strtolower($url['2']) : 'index';
		$module		= $url['0'];
		$controller = $url['1'];
		$action		= $url['2'];

		// identificando os parâmetros
		if (isset($url['3'])) 
		{
			foreach($url as $_l => $_tag)
			{
				if ($_l>=3)
				{
					if (strpos($_tag,':'))
					{
						$arrTag = explode(':',$_tag);
						$params[$arrTag['0']] = $arrTag['1'];
					} else $params[$_tag] = $_tag;
				}
			}
		}

		// identificando o controller
		$arq = 'Modules/'.$module.'/Config/bootstrap.php';
		if (!file_exists(APP.$arq))
		{
			$_SESSION['sistemaErro']['tip'] = 'module';
			$_SESSION['sistemaErro']['txt'] = 'Não foi possível localizar o seguinte arquivo: <br /><br />'.$arq;
			die('<script>document.location.href="'.$this->$controller->base.'sistema/usuarios/erros'.'"</script>');
		} else
		{
			require_once(APP.$arq);
		}

		// atualizando path
		set_include_path(get_include_path() . PATH_SEPARATOR . APP.'Modules/'.$module.'/');
		set_include_path(get_include_path() . PATH_SEPARATOR . CORE);

		// instanciando o controller
		$arq  = 'Modules/'.$module.'/Controller/'.$controller.'Controller.php';
		if (!include_once($arq))
		{
			$url = getBase().'sistema/usuarios/erros';
			$_SESSION['sistemaErro']['tip'] = 'controller';
			$_SESSION['sistemaErro']['txt'] = 'Não foi possível localizar o Controller <b>'.$controller.'</b> do módulo <b>'.$module.'</b>: <br /><br />'.$arq;
			die('<script>document.location.href="'.$url.'"</script>');
		}
		$_controller = $controller.'Controller';
		$this->$controller = new $_controller();
		$this->$controller->viewVars['params'] = $params;

		// atulizando alguns atributos do controller
		$this->$controller->module 				= $module;
		$this->$controller->controller			= $controller;
		$this->$controller->action				= $action;
		$this->$controller->view				= strtolower($action);
		$this->$controller->viewPath			= $controller;
		$this->$controller->base				= $this->$controller->viewVars['base'];
		$this->$controller->params				= $params;

		// recuperando o data
		$this->$controller->data = isset($_POST['data']) ? $_POST['data'] : array();

		// executando código antes de tudo
		$this->$controller->beforeIndex();

		// instanciando o helper html
		include_once('View/Helpers/Html.php');
		$this->Html = new Html();
		$this->Html->controller = $controller;
		$this->Html->base		= $this->$controller->base;

		// model
		if (count($this->$controller->Model))
		{
			foreach($this->$controller->Model as $_mod)
			{
				include_once('Modules/'.$module.'/Model/'.ucfirst(strtolower($_mod)).'.php');
				$this->$controller->$_mod 					= new $_mod();
				$this->$controller->modelClass 				= $_mod;
				$this->$controller->viewVars['modelClass'] 	= $_mod;
			}
			// recuperando as permissões da tela
			if (isset($_SESSION['Usuario']['perfil']))
			{
				$model = $this->$controller->modelClass;

				// recuperando os módulos ativos
				$res = $this->$controller->$model->getMeusModulos($_SESSION['Usuario']['perfil_id']);
				$modulos = array();
				foreach($res as $_l => $_arrCmps)
				{
					$modulos[$_arrCmps['nome']]['id'] = $_arrCmps['id'];
					$modulos[$_arrCmps['nome']]['titulo'] = $_arrCmps['titulo'];
				}
				$this->$controller->viewVars['modulos'] = $modulos;

				// recuperando minhas permissoes, conforme meu perfil, módulo e controller corrente
				$minhasPermissoes = array();
				$sql = "SELECT p.id, p.modulo_id, p.cadastro_id, p.perfil_id, p.visualizar, 
								p.incluir, p.alterar, p.excluir, p.imprimir, p.pesquisar, p.exportar 
							FROM sis_permissoes p 
							INNER JOIN sis_modulos m ON m.id = p.modulo_id AND m.nome='".strtoupper($module)."' 
							INNER JOIN sis_cadastros c ON c.id = p.cadastro_id AND c.nome='".strtoupper($controller)."' 
							WHERE p.perfil_id=".$_SESSION['Usuario']['perfil_id'];
				$_minhasPermissoes = $this->$controller->$model->query($sql);
				if (!empty($_minhasPermissoes))
				{
					$minhasPermissoes = $_minhasPermissoes['0'];
				}

				// recuperando os parâmetros para a tela de configuração de permissões de cada perfil
				if ($_SESSION['Usuario']['perfil_id']==1)
				{
					// todas as permissões pro bonitão da bala chita
					$minhasPermissoes['id'] 			= 1;
					$minhasPermissoes['perfil_id'] 		= 1;
					$minhasPermissoes['visualizar']		= 1;
					$minhasPermissoes['incluir'] 		= 1;
					$minhasPermissoes['alterar'] 		= 1;
					$minhasPermissoes['excluir'] 		= 1;
					$minhasPermissoes['imprimir'] 		= 1;
					$minhasPermissoes['pesquisar'] 		= 1;
					$minhasPermissoes['exportar'] 		= 1;
				}
				$this->$controller->viewVars['minhasPermissoes'] = $minhasPermissoes;
				$this->Html->minhasPermissoes = $minhasPermissoes;

				// recuperando os cadastros 
				$cadastros = array();
				$_cad = $this->$controller->$model->getMeusCadastros($_SESSION['Usuario']['perfil_id'],$module);
				foreach($_cad as $_l => $_arrCmps)
				{
					$cadastros[$_arrCmps['nome']]['tit'] = $_arrCmps['titulo'];
					$cadastros[$_arrCmps['nome']]['link']= $this->$controller->base.strtolower($module).'/'.strtolower($_arrCmps['nome']).'/listar';
				}
				unset($_cad);
				$this->$controller->viewVars['cadastros'] 	= $cadastros;
			}
		}

		// validando a permissão
		if (isset($_SESSION['Usuario']) && $_SESSION['Usuario']['perfil'] != 'ADMINISTRADOR')
		{
			if (!in_array(strtolower($action),array('erros'))
				)
			{
				$pode = isset($minhasPermissoes['visualizar']) ? $minhasPermissoes['visualizar'] : 0;
				if (!$pode)
				{
					$_SESSION['negadoAcesso']['txt'] = 'Caro '.$_SESSION['Usuario']['nome'].', 
						o seu perfil '.$_SESSION['Usuario']['perfil'].' não possui privilégios suficientes para acessar a página '.
						strtolower($module.'/'.$controller.'/'.$action);
					if (!in_array($action, array('get_options')))
					{
						header('Location: '.$this->$controller->base.'sistema/usuarios/index');
					}
				}
			}
		}

		// verificando se o perfil logado pode filtrar, se não, deleta os registros
		if (isset($_SESSION['Filtros'][$module][$controller]) && $_SESSION['Usuario']['perfil'] != 'ADMINISTRADOR')
		{
			if (!$minhasPermissoes['pesquisar']) unset($_SESSION['Filtros'][$module][$controller]);
		}

		// executando a action
		if (!method_exists($this->$controller,$action))
		{
			$_SESSION['sistemaErro']['tip'] = 'Página';
			$_SESSION['sistemaErro']['txt'] = 'Não foi possível localizar a Página <b>'.$action.'</b> do Cadastro <b>'.$controller.'</b> do módulo <b>'.$module.'</b>: <br />';
			//die('<script>document.location.href="'.$this->$controller->base.'sistema/usuarios/erros'.'"</script>');
		}
		$this->$controller->$action();

		// identificando o título do MVC
		$this->$controller->viewVars['tituloModule'] = isset($this->$controller->viewVars['tituloModule'])
		  ?  $this->$controller->viewVars['tituloModule']
		  : $module;
		if (empty($this->$controller->viewVars['tituloModule']))
		{
			$this->$controller->viewVars['tituloModule'] = !empty($this->$controller->viewVars['tituloModule'])
			? $this->$controller->viewVars['tituloModule']
			:  $module;
		}
		$this->$controller->viewVars['tituloController'] = isset($this->$controller->viewVars['tituloController'])
		  ?  $this->$controller->viewVars['tituloController']
		  : $cadastros[strtoupper($controller)]['tit'];
		$this->$controller->viewVars['tituloController'] = !empty($this->$controller->viewVars['tituloController'])
		  ?  $this->$controller->viewVars['tituloController']
		  : $controller;

		$this->$controller->viewVars['tituloAction'] = isset($this->$controller->viewVars['tituloAction'])
		  ?  $this->$controller->viewVars['tituloAction']
		  : ucfirst($action);
		$this->$controller->viewVars['tituloAction'] = !empty($this->$controller->viewVars['tituloAction'])
		  ?  $this->$controller->viewVars['tituloAction']
		  : ucfirst($action);

		// atualizando viewVars do controller com algumas informações do model
		if (count($this->$controller->Model))
		{
			foreach($this->$controller->Model as $_mod)
			{
				$this->$controller->viewVars['primaryKey'] 	= $this->$controller->$_mod->primaryKey;
				if (isset($this->$controller->$_mod->pag) && isset($this->$controller->viewVars['paginacao'])) $this->$controller->viewVars['paginacao'] 	= $this->$controller->$_mod->pag;
				if (isset($this->$controller->$_mod->esquema))
				{
					$this->$controller->viewVars['esquema'][$_mod] = $this->$controller->$_mod->esquema;
					if (isset($this->$controller->$_mod->outrosEsquemas))
					{
						foreach($this->$controller->$_mod->outrosEsquemas as $_mod2 => $_esquema2)
						{
							$this->$controller->viewVars['esquema'][$_mod2] = $_esquema2;
						}
					}
				}
			}
		}

		// executando código antes da renderização e depois da action
		$this->$controller->beforeRender();

		// atualizando as sqls do model na view
		$modelClass = $this->$controller->modelClass;
		if (isset($this->$controller->$modelClass->sqls))
		{
			$this->$controller->viewVars['sql_dump'] 	= $this->$controller->$modelClass->sqls;
		}

		// atualizando as variáveis locais
		$this->data		= $this->$controller->data;
		$this->viewVars = $this->$controller->viewVars;
		foreach($this->viewVars as $_var => $_vlr) if (!in_array($_var,array('controller','action','module'))) ${$_var} = $_vlr;
		$this->viewVars['module']	 	= $module;
		$this->viewVars['controller'] 	= $controller;
		$this->viewVars['action'] 		= $action;

		// salvando dados da view e dando adeus ao controller, ele não vai pra view
		$viewPath 		= $this->$controller->viewPath;
		$view			= $this->$controller->view;
		$layout			= $this->$controller->layout;
		unset($this->$controller);

		// verificando a mensagen flash
		if (isset($_SESSION['msgFlash']))
		{
			$msgFlash = $_SESSION['msgFlash'];
			unset($_SESSION['msgFlash']);
		}

		// configurando o css e js da action
		$arq = strtolower($module.'_'.$controller.'_'.$action);
		if (file_exists('./css/'.$arq.'.css'))
		{
			array_push($head,'<link rel="stylesheet" type="text/css" href="'.$base.'css/'.$arq.'.css" />');
		}
		if (file_exists('./js/'.$arq.'.js'))
		{
			array_push($head,'<script type="text/javascript" src="'.$base.'js/'.$arq.'.js"></script>');
		}

		// incluindo a view 
		$conteudo = '';
		$arq = 'Modules/'.$module.'/View/'.$viewPath.'/'.$view.'.phtml';
		if (!file_exists(APP.$arq))
		{
			$msg = 'N&atilde;o foi poss&iacute;vel localizar a view <b>'.$arq.'</b>';
			$arq = CORE.'View/Scaffolds/'.$view.'.phtml';
			if (!file_exists($arq))
			{
				die('<center>'.$msg.'</center>');
			}
		}

		ob_start();
		include_once($arq);
		$conteudo = ob_get_contents();
		ob_end_clean();

		// configurando o head da página
		foreach($this->Html->head as $_l => $_head) array_unshift($this->viewVars['head'],$_head);
		foreach($this->viewVars['head'] as $_l => $_line) $head[$_l] = $_line;

		// incluindo o layout
		ob_start();
		include_once('View/Layouts/'.$layout.'.php');
		$html = ob_get_contents();
		ob_end_clean();

		// retornando a página renderizada
		return $html;
	}
	
	/**
	 * Inclui o bloco de elemento
	 * 
	 * @param	string	$e		Nome do elemento
	 * @param	array	$vars	Variáveis quer serão importadas para dentro do element, mas se usar this->viewVars você pega dos as variáveis da view
	 * @return	void
	 */
	function element($e='',$vars=array())
	{
		$base 		= $this->viewVars['base'];
		$module		= $this->viewVars['module'];
		$controller = $this->viewVars['controller'];
		$action		= $this->viewVars['action'];

		// atualizando as variáveis locais
		foreach($vars as $_var => $_vlr) ${$_var} = $_vlr;
		$arq = 'View/Elements/'.$e.'.phtml';

		require_once($arq);
	}
}
