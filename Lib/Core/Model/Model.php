<?php
/**
 * Class Model
 * 
 * @package			Core
 * @subpackage		Core.Model
 */
class Model {
	/**
	 * Prefixo no nome da tabela
	 * 
	 * @var		string
	 * @access	public
	 */
	public $prefixo		= '';

	/**
	 * Nome do model
	 * 
	 * @var		string
	 * @access	public
	 */
	public $name		= '';

	/**
	 * Alias para a tabela
	 * 
	 * @var		string
	 * @access	public
	 */
	public $alias		= '';

	/**
	 * Banco de Dados
	 * 
	 * @var		string
	 * @access	public
	 */
	public $database	= 'default';

	/**
	 * Driver do banco de dados
	 */
	public $driver		= '';

	/**
	 * Nome da tabela
	 * 
	 * @var		string
	 * @access	public
	 */
	public $tabela 		= '';

	/**
	 * Chave primária da tabela
	 * 
	 * @var		array
	 * @access	public
	 */
	public $primaryKey	= array();

	/**
	 * Historic of the sqls
	 * 
	 * @var		array
	 */
	public $sqls 		= array();

	/**
	 * Dados do model
	 * 
	 * @var		array
	 * @access	public
	 */
	public $data 		= array();

	/**
	 * Não retornar belongsTo , relacionamento 1 para n
	 * Este parâmetro é usado no método find
	 * 
	 * @var		boolean
	 * @access	public
	 */
	public $belongsToOff = false;

	/**
	 * Relacionamento Habtm (HasAndBelongsToMany), n:n
	 * 
	 * @var		array
	 * @access	public
	 */
	public $habtm		= array();

	/**
	 * Não retornar habtm HasAndBelongsToMany, relacionamento n para n
	 * Este parâmetro é usado no método find
	 * 
	 * @var		boolean
	 * @access	public
	 */
	public $habtmOff 	= false;

	/**
	 * Matriz com as propriedades de cada campo do model corrente
	 * As propriedades do esquema podem ser:
	 * - tit			Título do campo
	 * - notempty		Não valores em branco para o campo
	 * - unique			Não aceita valores duplicados para o campo
	 * - filtro			Se o campo faz parte do filtro do cadastro
	 * - emptyFiltro	Mensagem para o comboBox do filtro do campo
	 * - type			Tipo do campo, pode ser numeric, float, varchar, text
	 * - edicaoOff		Se verdadeiro o campo não será editável nos formulários de manutenção
	 * - mascara		Máscara do campo
	 * - upperOff		Por padrão, todos os campos são salvos em maiúsculo, mas com este parâmetro não.
	 * - options		Valores possíveis para o campo, no formato [valor][label]. exemplo: array(1=>'Sim',2=>'Não')
	 * - belongsTo		Se o campo tem relacionamento 1:n
	 * 
	 * Sobre o belongsTo:
	 * O parâmetro belongsTo, possuis algumas opções obrigatórias, como mostrado no exemplo abaixo.
	 * exemplo:
	 * 'cidade_id'	=> array
	 * (
	 *		'tit'		=> 'Cidade',
	 *		'belongsTo' 	=> array
	 *		(
	 *			'Cidade'	=> array // nome do outro model
	 *			(
	 *				'key'	=> 'id',	// chave de relacionamento do outor model
	 *				'fields'=> array('id','nome','uf'), // campos de exibição do relacionamento
	 *				'order'	=> array('nome','uf'), // ordem do relacionamento
	 *				'ajax'	=> 'sistema/cidades/get_options/', // busca via ajax do relacionamento
	 *				'txtPesquisa' => 'Digite o nome da cidade para pesquisar ...', // texto de pesquisa
	 *			),
	 *		),
	 *	)
	 * 
	 * @var		array
	 * @access	public
	 */
	public $esquema 	= array();

	/**
	 * Matriz com as propriedades de cada campo de belongsTo ou Habtm
	 * 
	 * segue o mesmo exemplo de esquema.
	 * 
	 * @var		array
	 * @access	public
	 */
	public $outrosEsquemas = array();

	/**
	 * Formato da date e hora do banco de dados
	 * 
	 * @var		string
	 * @access	public
	 */
	public $dateFormatBD	= 'Y-m-d H:i:s';

	/**
	 * Formato da date e hora para a view
	 * 
	 * @var		string
	 * @access	public
	 */
	public $dateFormat		= 'd/m/Y H:i:s';

	/**
	 * Erros, quando na execusão de alguma sql
	 *
	 * @var		array
	 * @access	public
	 */
	public $erros			= array();

	/**
	 * Executa start do Obejeto Model
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->name = get_class($this);
	}

	/**
	 * Inicia conexão com o banco de dados
	 * 
	 * @return	void
	 */
	public function open()
	{
		if (!isset($this->db))
		{
			include_once(APP.'Config/database.php');
			$dbConfig 			= new Database_Config($this->database);
			$banco				= $dbConfig->default;
			$driver 			= $banco['driver'];
			$driver				= ucfirst(strtolower($driver));
			$this->driver 		= $driver;
			$this->dateFormatBD = isset($banco['dateFormatBD']) ? $banco['dateFormatBD'] : $this->dateFormatBD;
			$this->dateFormat 	= isset($banco['dateFormat'])   ? $banco['dateFormat']   : $this->dateFormat;
			$params				= array();
			switch($driver)
			{
				case 'Mysql':
				case 'MariaDB':
					$dsn = "mysql:host=".$banco['host'].";dbname=".$banco['database'];
					if ($banco['persistent']==true) $params['PDO::ATTR_PERSISTENT'] = true;
					break;
			}
			try
			{
				$this->db = new PDO($dsn,$banco['user'],$banco['password'],$params);
				$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			} catch (PDOException $e) 
			{
				switch($e->getCode())
				{
					case '1049':
					case '1045':
						$msg = '<br /><br /><br /><br />
						<center>
						Erro: <b style="color: red;">' . $e->getMessage() . '</b>
						
							<p>N&atilde;o foi poss&iacute;vel conectar no banco de dados: </p>
							</center>
							<pre>
							Pe&ccedil;a ao administrador do banco de dados para executar:
							
							create database '.$banco['database'].' character set '.$banco['encoding'].';
							grant all privileges on '.$banco['database'].'.* to '.$banco['user'].'@'.$banco['host'].' identified by "'.$banco['password'].'" with grant option;
							flush privileges;
							</pre>
							<p><center>Clique <a href="../usuarios/instalacao">aqui</a> para tentar novamente.</center></p>';
						echo $msg;
						break;
				}
				die("<center>!!</center>");
			}
			//foreach(PDO::getAvailableDrivers() as $d) echo $d.'<br />';
			$this->setEsquema();
		}
	}

	/**
	 * Executa a validação de cada campo do model
	 * As validações podem ser: 
	 * - notnull, não aceita valores nulos
	 * - unique, não aceita duplicidades
	 * 
	 * @return	boolean
	 */
	public function validate()
	{
		foreach($this->data as $_l	=> $_arrMods)
		{
			foreach($_arrMods[$this->name] as $_cmp => $_vlr)
			{
				$tit	= isset($this->esquema[$_cmp]['tit']) 	? $this->esquema[$_cmp]['tit'] : $_cmp;
				$empty 	= isset($this->esquema[$_cmp]['notEmpty']) 	? $this->esquema[$_cmp]['notEmpty'] : null;
				if (!empty($empty) && empty($_vlr))
				{
					$this->erros[$_l] = 'O Campo '.$tit.' é de preenchimento obrigatório';
				}
			}
		}
		
		return true;
	}

	/**
	 * Salva um registro ou conjunto de registros no banco de dados
	 * 
	 * @param	array	$data	Matriz com os dados a serem salvos
	 * @return	boolean	Retorna verdadeiro em caso de sucesso
	 */
	public function save($data=array())
	{
		$this->open();
		$sqls	= array();
		$sqlTi 	= 'INSERT';
		$sqlUp 	= '';
		$sqlIn 	= '';
		$where 	= '';
		$lCm	= 0;
		$lPk	= 0;
		$this->data = $this->getData($data);

		if (!$this->validate()) return false;

		if (!$this->beforeSave()) return false;

		// dando um loop na data pra criar cada sql
		foreach($this->data as $_l => $_arrMods)
		{
			foreach($_arrMods as $_l2 => $_arrCmps)
			{
				$sqlInC= array();
				$sqlInV= array();
				$sqlUp = '';
				$where = '';
				foreach($_arrCmps as $_cmp => $_vlr)
				{
					$tipo = (isset($this->esquema[$_cmp]['type'])) ? $this->esquema[$_cmp]['type'] : 'text';
					if ($lCm>0 && !empty($sqlUp)) $sqlUp .= ", ";
					if (!empty($_vlr)) $sqlTi = (in_array($_cmp,$this->primaryKey)) ? 'UPDATE' : $sqlTi;

					switch($tipo)
					{
						case 'numero':
						case 'numeric':
						case 'float':
						case 'integer':
						case 'int':
							$_sqlUp = "$_cmp=$_vlr";
							array_push($sqlInC,$_cmp);
							array_push($sqlInV,$_vlr);
							break;
						default:
							$_sqlUp = "$_cmp='$_vlr'";
							array_push($sqlInC,$_cmp);
							array_push($sqlInV,"'$_vlr'");
					}
					$lCm++;
					if (in_array($_cmp,$this->primaryKey))
					{
						if (!empty($where)) $where .= ' AND ';
						$where .= $_sqlUp;
					} else
					{
						$sqlUp .= $_sqlUp;
					}
				}
			}
			switch($sqlTi)
			{
				case 'UPDATE':
					$sqls[$_l] = 'UPDATE '.$this->prefixo.$this->tabela.' SET '.$sqlUp.' WHERE '.$where.';';
					break;
				case 'INSERT':
					$sqls[$_l] = 'INSERT INTO '.$this->prefixo.$this->tabela.' ('.implode(',',$sqlInC).') VALUES ('.implode(',',$sqlInV).');';
					break;
			}
		}
		try
		{
			$this->db->beginTransaction();
			array_push($this->sqls,array('sql'=>'BEGIN;','ts'=>0.0001));
			foreach($sqls as $_l => $_sql)
			{
				$ini = microtime(true);
				$res = @$this->db->exec($_sql);
				$ts = microtime(true);
				$ts = round(($ts-$ini),6);
				if (strlen($res)==0)
				{
					$err 	= $this->db->errorInfo();
					$_sql = '<div class="erro_sql">erro: '.$_sql.' '.$err['2'].'</div>';
					$this->erros[$_l] = $err['1'].' - '.$err['2'];
				}
				array_push($this->sqls,array('sql'=>$_sql,'ts'=>$ts));
			}
			array_push($this->sqls,array('sql'=>'END;','ts'=>0.0001));
			$this->db->commit();
			return true;
		} catch (PDOException $e)
		{
			$this->db->rollBack();
			debug($e->getMessage());
			array_push($this->erros,$e->getMessage());
			return false;
		}
	}

	/**
	 * Execute SQL code in database
	 * 
	 * @param	$sql	string	Strin SQL
	 * @return	$data	mixed	O resultado pode ser verdadeiro, falso ou uma array de uma resposta select
	 */
	public function query($sql='')
	{
		$this->open();
		$_data 	= $this->db->query($sql);
		$erro 	= $this->db->errorInfo();

		$l 		= 0;
		$data 	= array();
		$ini	= microtime(true);
		if (empty($erro['2']))
		{
			$linhas = @$_data->fetchAll(PDO::FETCH_NAMED);
			if (is_array($linhas))
			{
				foreach($linhas as $_l => $_arrCmps)
				{
					foreach($_arrCmps as $_cmp => $_vlr)
					if (is_array($_vlr))
					{
						foreach($_vlr as $_l2 => $_vlr2)
						{
							$cmp = $_cmp;
							if ($_l2) $cmp = $_cmp.$_l2;
							$data[$_l][$cmp] = $_vlr2;
						}
					} else
					{
						$data[$_l][$_cmp] = $_vlr;
					}
				}
			}
		}

		$ts = microtime(true);
		$ts = round(($ts-$ini)*360,4);
		array_push($this->sqls,array('sql'=>$sql,'ts'=>$ts));
		return $data;
	}

	/**
	 * End of the Object Model
	 * 
	 * @return	void
	 */
	public function __destruct()
	{
		if (isset($this->db))
		{
			//$this->db->close();
		}
	}

	/**
	 * Exclui um registro no banco de dados
	 *
	 * @params array 	$params Parâmetros da exclusão, params['tabela'], params['where']
	 */
	public function exclude($data=array())
	{
		$this->data = $this->getData($data);

		if (!$this->beforeExclude()) return false;

		$sqls = array();
		foreach($data as $_l => $_arrMods)
		{
			$where = '';
			foreach($_arrMods[$this->name] as $_cmp => $_vlr)
			{
				if (!empty($where)) $where .= ' AND ';
				if (is_numeric($_vlr))
				{
					$where .= "$_cmp=$_vlr";
				} else
				{
					$where .= "$_cmp='$_vlr'";
				}
			}
			$sql = 'DELETE FROM '.$this->prefixo.$this->tabela.' WHERE '.$where;
			array_push($sqls, $sql);
		}

		try
		{
			$this->open();
			$this->db->beginTransaction();
			array_push($this->sqls,array('sql'=>'BEGIN;','ts'=>0.0001));
			foreach($sqls as $_l => $_sql)
			{
				$ini = microtime(true);
				$this->db->exec($_sql);
				$ts = microtime(true);
				$ts = round(($ts-$ini),6);
				array_push($this->sqls,array('sql'=>$_sql,'ts'=>$ts));
			}
			array_push($this->sqls,array('sql'=>'END;','ts'=>0.0001));
			$this->db->commit();
			return true;
		} catch(PDOException $e)
		{
			$this->db->rollBack();
			$this->erro = $e->getMessage();
			return false;
		}
	}

	/**
	 * Retorno o campo principal do model, o segundo campo do esquema é considerado como tal campo.
	 *
	 * @return 	string 	$field Nome do campo
	 */
	public function getDisplayField()
	{
		$field 	= '';
		$l 		= 0;
		foreach($this->esquema as $_cmp => $_arrProp)
		{
			if ($l)
			{
				$field = strtolower($this->name.'_'.$_cmp);
				break;
			}
			$l++;
		}
		return $field;
	}

	/**
	 * Execute a search in database
	 * 
	 * @param	string	$tipo	Tipos list|all|neighbors
	 * @param	mixed	$params	Opções para a busca, tabela, fields, where, order, inner, página, total de registro por página.
	 * @return	mixed	$data	Results
	 */
	public function find($tipo='all',$params=array())
	{
		$params = $this->beforeFind($params);

		$sql 	= '';
		$sqlC	= '';
		$join 	= array();
		$tabela = isset($params['tabela']) 	? $params['tabela'] : $this->prefixo.$this->tabela;
		$fields = isset($params['fields']) 	? $params['fields'] : array();
		$where	= isset($params['where']) 	? $params['where'] 	: array();
		$order	= isset($params['order']) 	? $params['order'] 	: array();
		$direc	= isset($params['direc']) 	? $params['direc'] 	: 'asc';
		$pag	= isset($params['pag']) 	? $params['pag'] 	: 0;
		$pagT	= isset($params['pagT']) 	? $params['pagT'] 	: 20;
		$ali1	= isset($this->alias) ? $this->alias : $this->name;

		// verificando os campos
		switch($tipo)
		{
			case 'all':
				if (empty($fields))
				{
					foreach($this->esquema as $_cmp => $_arrProp)
					{
						array_push($fields,$this->name.'.'.$_cmp);
					}
				}
				break;
			case 'first':
				if (empty($fields))
				{
					$l = 0;
					foreach($this->esquema as $_cmp => $_arrProp)
					{
						array_push($fields,$this->name.'.'.$_cmp);
						$l++;
					}
				}
				break;
			case 'list':
				if (empty($fields))
				{
					foreach($this->primaryKey as $_cmp)
					{
						array_push($fields,$this->name.'.'.$_cmp);
					}
					$l = 0;
					foreach($this->esquema as $_cmp => $_arrProp)
					{
						if ($l>0) break;
						if (!in_array($_cmp,$this->primaryKey))
						{
							array_push($fields,$this->name.'.'.$_cmp);
							$l++;
						}
					}
				}
				break;
		}

		// início da sql
		$cmps 		= '';
		$join 		= array();
		$belongs 	= array();
		$cmpsBelongs= array();
		$cmpsBelongsFunc= array();
		foreach($fields as $_l => $_cmp)
		{
			$nome = strpos($_cmp,'.') ? explode('.',$_cmp) : ucfirst(strtolower($_cmp));
			if (is_array($nome)) $nome = $nome['1'];
			if ($_l) $cmps .= ', ';
			$cmps .= $_cmp.' AS '.str_replace('.','_',$_cmp);
			$a = explode('.',$_cmp);
			$c = isset($a['1']) ? $a['1'] : $_cmp;

			// se é pra pegar todos os campos, pega relacionamentos também
			if ($tipo=='all')
			{
				if (isset($this->esquema[$c]['optionsFunc']) && !isset($this->esquema[$c]['belongsTo']['ajax']))
				{
					array_push($cmpsBelongsFunc, $c);
				}

				// belongsTo
				if (isset($this->esquema[$c]['belongsTo']))
				{
					foreach($this->esquema[$c]['belongsTo'] as $_model => $_arrProp)
					{
						require_once('Model/'.$_model.'.php');
						$belo 	= new $_model();
						if (isset($belo->esquema)) $this->outrosEsquemas[$_model] = $belo->esquema;
						$tabB	= $belo->prefixo.$belo->tabela;
						$aliB	= $_model;
						$keyB	= (isset($_arrProp['key'])) ? $_arrProp['key'] : 'id';
						$aliA 	= (isset($this->alias)) ? $this->alias : $this->name;
						$cmpA 	= $c;
						$jSel	= "LEFT JOIN $tabB $aliB ON $aliB.$keyB = $aliA.$cmpA";
						foreach($_arrProp['fields'] as $_l2 => $_cmp2)
						{
							$cmps .= ', '.$_model.'.'.$_cmp2.' AS '.$_model.'_'.$_cmp2;
						}
						array_push($join,$jSel);
						if (!isset($this->esquema[$c]['belongsTo'][$_model]['ajax']))
						{
							array_push($cmpsBelongs,$c);
						}
					}
				}
			}
		}

		// iniciando a sql
		$sql  .= "SELECT ".$cmps." FROM $tabela ".$ali1;
		$sqlC .= "SELECT COUNT(1) as tot FROM $tabela ".$ali1;

		// join
		if (!empty($join))
		{
			foreach($join as $_l => $j) $sql .= ' '.$j;
		}

		// where
		if (count($where))
		{
			$sql   .= " WHERE ";
			$sqlC  .= " WHERE ";
			$l 		= 0;
			foreach($where as $_cmp => $_vlr)
			{
				if ($l)
				{
					$sql  .= " AND ";
					$sqlC .= " AND ";
				}
				$b = explode(' ',$_vlr);
				switch(strtoupper($b['0']))
				{
					case 'IN':
						$sql .= $_cmp.' IN '.$_vlr;
					case 'BETWEEN':
						$sql .= $_cmp.' BETWEEN ('.$_vlr.')';
					case 'NOT':
						$sql .= $_cmp.' NOT IN '.$_vlr;
					case 'LIKE':
						$_vlr = trim(str_replace('LIKE','',$_vlr));
						$sql .= $_cmp." LIKE '%$_vlr%'";
						$sqlC .= $_cmp." LIKE '%$_vlr%'";
						break;
					default:
						if (is_numeric($_vlr))
						{
							$sql .= $_cmp."=$_vlr";
							$sqlC .= $_cmp."=$_vlr";
						} else
						{
							$sql .= $_cmp."='$_vlr'";
							$sqlC .= $_cmp."='$_vlr'";
						}
						break;
				}
				$l++;
			}
		}
		if (count($order))
		{
			$l 		= 0;
			$sql   .= " ORDER BY ";
			if (is_array($order))
			{
				$l = 0;
				foreach($order as $_cmp)
				{
					if ($l) $sql .= ' AND ';
					$sql .= $_cmp;
					$l++;
				}
			} else $sql .= $order;
			$sql .= ' '.strtoupper($direc);
		}

		// verificando a página
		if (!empty($pag))
		{
			switch($this->driver)
			{
				default:
					$sql .= ' LIMIT '.(($pag*$pagT)-$pagT).','.$pagT;
			}
			// descobrindo o total
			$data = $this->query($sqlC);
			$this->pag['tot'] 	= $data['0']['tot'];
			$this->pag['pag'] 	= $pag;
			$this->pag['pagU'] 	= round($data['0']['tot']/$pagT)+1;
		}

		$_data 	= $this->query($sql);
		$data	= array();
		foreach($_data as $_l => $_arrCmps)
		{
			foreach($_arrCmps as $_cmp => $_vlr)
			{
				$c = explode('_',$_cmp);
				if (isset($c['2'])) $c['1'] .= '_'.$c['2'];
				if (!isset($data[$_l][$c['0']])) $data[$_l][$c['0']] = array();
				$data[$_l][$c['0']][$c['1']] = $_vlr;
			}
			
			// incrementando habtm, caso poussua
			if ($tipo=='all' AND isset($this->habtm) && !empty($this->habtm))
			{
				foreach($this->habtm as $_mod => $_arrProp)
				{
					$tabEsquerda= $_arrProp['tableFk'];
					$tabLiga	= $_arrProp['table'];
					$arrTab		= explode('_',$tabLiga);
					$cmpEsquerda= $_arrProp['key'];
					
					$tabDireita = $arrTab['2'];
					$cmpDireita = $_arrProp['keyFk'];
					$sql = ' SELECT * FROM '.$tabEsquerda.' '.$_mod;
					$sql .= ' INNER JOIN '.$tabLiga.' t1 ON ';
					$l = 0;
					foreach($cmpDireita as $_cmp)
					{
						if ($l) $sqlHabtm .= ' AND ';
						$arrCmp = explode('_',$_cmp);
						$sql .= $_mod.'.'.$arrCmp['1'].'= t1.'.$_cmp;
						$l++;
					}
					$l = 0;
					foreach($cmpEsquerda as $_cmp)
					{
						if ($l) $sql .= ' AND ';
						$vlrCmpEsquerda = $_arrCmps[ucfirst($_cmp)];
						$sql .= ' WHERE t1.'.$_cmp.'='.$vlrCmpEsquerda;
						$l++;
					}
					$dataHbtm = $this->query($sql);
					foreach($dataHbtm as $_lHa => $_arrCmHa)
					{
						$data[$_l][$_mod][$_lHa] = $_arrCmHa;
					}
				}
			}
		}

		// recuperando options para belongsTo
		if (!empty($cmpsBelongs))
		{
			foreach($cmpsBelongs as $_l => $_cmp)
			{
				$this->esquema[$_cmp]['options'] = $this->getOptions($_cmp);
			}
		}

		// recuperando options para belongsToFunc
		if (!empty($cmpsBelongsFunc))
		{
			foreach($cmpsBelongsFunc as $_l => $_cmp)
			{
				$func = $this->esquema[$_cmp]['optionsFunc'];
				$this->esquema[$_cmp]['options'] = $this->$func();
			}
		}

		return $this->afterFind($data);
	}

	/**
	 * Retorna um lista conforme as opções de campo belongsTo ou Habtm
	 *
	 * @params 	$cmp 	string 	$nome do campo
	 * @params 	$linha 	array 	primeira linha de lista
	 * @return array
	 */
	public function getOptions($cmp='')
	{
		$options 	= array();
		$tipo 		= isset($this->esquema[$cmp]['belongsTo']) ? 'belongsTo' : null;
		$tipo 		= isset($this->esquema[$cmp]['hbatm']) ? 'hbatm' : $tipo;
		switch($tipo)
		{
			case 'belongsTo':
				foreach($this->esquema[$cmp]['belongsTo'] as $_mod => $_arrProp)
				{
					$alias 	= $_mod;
					$cmps 	= $_arrProp['fields'];
					$ordem 	= $_arrProp['order'];
					if (isset($_arrProp['where']))
					{
						$where = '';
						foreach($_arrProp['where'] as $_cmp => $_vlr)
						{
							if (!empty($where)) $where .= ' AND ';
							if (is_numeric($_vlr))
								$where .= "$_cmp=".$_vlr;
							else
								$where .= "$_cmp='$_vlr'";
						}
					}
					$limite = isset($_arrProp['limit']) ? $_arrProp['limit'] : null;

					require_once('Model/'.$_mod.'.php');
					$belo 	= new $_mod();
					$tabela = $belo->prefixo.$belo->tabela;
					$sql 	= "SELECT ".implode(',', $cmps)." FROM $tabela as $alias";
					if (!empty($where)) $sql .= " WHERE ".$where;
					$sql .= " ORDER BY ".implode(',', $ordem);
					if (!empty($limite)) $sql .= ' LIMIT '.$limite;

					$res = $this->query($sql);
					foreach ($res as $_l => $_a)
					{
						$l = 0;
						$c1='';
						$c2='';
						foreach($cmps as $_l2 => $_cmp)
						{
							if (!$l) $c1 = $_a[$_cmp]; else $c2 = $_a[$_cmp];
							$l++;
						}
						$options[$c1] = $c2;
					}
				}
				break;
		}
		return $options;
	}


	/**
	 * Atualiza as propriedades de cada campo da tabela
	 * 
	 * @param	string	$tabela	Nome da tabela
	 * @return	void
	 */
	public function setEsquema($tabela='')
	{
		$this->open();
		$tabela = !empty($tabela) ? $tabela : $this->prefixo.$this->tabela;
		// completando o esquema
		switch($this->driver)
		{
			case 'Mysql':
			case 'MariaDB':
				$_data = $this->query('DESCRIBE '.$tabela);
				foreach($_data as $_l => $_arrProp)
				{
					$field 	= $_arrProp['Field'];
					$type  	= $_arrProp['Type'];
					$null	= $_arrProp['Null'];
					$default= $_arrProp['Default'];
					$key	= $_arrProp['Key'];
					$_t = substr($type,strpos($type,'('),strlen($type));
					if (strpos($type,'(')) $type = substr($type,0,strpos($type,'('));
					$length= str_replace('(','',$_t);
					$length= str_replace(')','',$length);
					if (in_array($type,array('varchar')))
					{
						$type = 'text';
					}
					if (in_array($type,array('int','float','tinyint')))
					{
						$type = 'numeric';
					}
					$this->esquema[$field]['tit'] 	= !isset($this->esquema[$field]['tit']) ? ucfirst(str_replace('_','',$field)) : $this->esquema[$field]['tit'];
					$this->esquema[$field]['type'] 	= !isset($this->esquema[$field]['type']) ? $type : $this->esquema[$field]['type'];
					$this->esquema[$field]['length']= !isset($this->esquema[$field]['length']) ? $length : $this->esquema[$field]['length'];
					$this->esquema[$field]['null']	= !isset($this->esquema[$field]['null']) ? $null : $this->esquema[$field]['null'];
					if (!empty($default))
						$this->esquema[$field]['default']= !isset($this->esquema[$field]['default']) ? $default : $this->esquema[$field]['default'];
					if (!empty($key))
					{
						$this->esquema[$field]['key']= !isset($this->esquema[$field]['key']) ? $key : $this->esquema[$field]['key'];
						if ($key=='PRI' && !in_array($field,$this->primaryKey)) array_push($this->primaryKey,$field);
					}
					
					if (in_array($field,array('modificado','criado')))
					{
						$this->esquema[$field]['edicaoOff'] = true;
					}

				}
				break;
		}
	}

	/**
	 * Executa código antes da exclusão de um registro no banco de dados
	 * 
	 * @param	array	$data	Atributo do Model contendo os valores do model, seguindo o modelo data[campo][valor]
	 * @return	boolean	Verdadeiro se o método salvar deve continuar
	 */
	public function beforeExclude()
	{
		return true;
	}

	/**
	 * Executa código antes do método save
	 * 
	 * - Remove a máscara de cada campo
	 * - Se o model possui os campos criado e modificado, então atualiza seus valores
	 * - Transforma tudo em maiúsculo, salvo se o campo possui a propriedade upperOff
	 * 
	 * @param	array	$data	Atributo do Model contendo os valores do model, seguindo o modelo data[linha][Model][campo][valor]
	 * @return	boolean	Verdadeiro se o método salvar deve continuar
	 */
	public function beforeSave()
	{
		$_data 	= array();
		$m		= isset($this->esquema['modificado']) ? true : false;
		foreach($this->data as $_l => $_arrMods)
		{
			foreach($_arrMods as $_mod => $_arrCmps)
			{
				foreach($_arrCmps as $_cmp => $_vlr)
				{
					$v = $_vlr;
					$p = $this->esquema[$_cmp];
					if (!is_array($_vlr))
					{
						// tudo maiúsculo
						if (!isset($p['upperOff']) && in_array($p['type'],array('text','varchar'))) $v = mb_strtoupper($v,'UTF8');

						// removendo a máscara
						if (isset($p['mascara']))
						{
							$v = str_replace(array('-','_','(',')','/','\\','.'),'',$v);
						}
					}
					$_data[$_l][$_mod][$_cmp] = $v;
				}
			}
			// campo modificado
			if ($m) $_data[$_l][$_mod]['modificado'] = date($this->dateFormatBD);
		}
		$this->data = $_data;
		return true;
	}

	/**
	 * Executa o código antes do método find
	 * 
	 * @param	array	$queryData	Parâmetros da operação find, tal como where, table, fields, order e etc.
	 * @return	array	$params		Parâmetros possivelmente alterados
	 */
	public function beforeFind($params=array())
	{
		
		return $params;
	}

	/**
	 * Executa código depois do método save
	 * 
	 * @return	void
	 */
	public function afterFind($data = array()) 
	{
		$resultado = array();
		foreach($data as $_l => $_arrMods)
		{
			foreach($_arrMods as $_mod => $_arrCmps)
			{
				foreach($_arrCmps as $_cmp => $_vlr)
				{
					$v = $_vlr;
					if (!is_array($_vlr))
					{
						$p = isset($this->esquema[$_cmp]) ? $this->esquema[$_cmp] : array();
						$t = isset($p['type']) ? $p['type'] : 'text';
						switch($t)
						{
							case 'datetime':
								$v = date($this->dateFormat,strtotime($v));
								break;
						}
					}
					$resultado[$_l][$_mod][$_cmp] = $v;
				}
			}
		}
		return $resultado;
	}

	/**
	 * Retorna o valor data substituindo o título pelo o nome do campo
	 * - Esta funcionalidade foi apalicada por questões de segurança, não se pode 
	 *   jogar o nome do campo na view, então jogas-se o nome do campo criptografado
	 *   e depois substitui a cripotografia pelo nome ral do campo.
	 *
	 * @param 	array 	Matriz com o data oriundo do controller
	 * @return 	array 	Matriz data remasterizada.
	 */
	public function getData($data=array())
	{
		/*$_data = array();
		foreach($data as $_l => $_arrMods)
		{
			foreach($_arrMods as $_mod => $_arrCmps)
			{
				foreach($_arrCmps as $_tit => $_vlr)
				{
					foreach($this->esquema as $_cmp => $_arrProp)
					{
						$cmp = null;
						if ($_arrProp['tit']==$_tit)
						{
							$cmp = $_cmp;
							break;
						}
					}
					if (empty($cmp))
					{
						die('Erro ao localizar nome do campo !!! não se esqueça de utilizar o título do campo e não o seu nome nos formulários e/ou controller.');
					}
					$_data[$_l][$_mod][$cmp] = $_vlr;
				}
			}
		}*/
		return $data;
	}
}
