<?php
/**
 * Database Config
 * 
 * @package		app
 * @subpackage	app.database
 */
class Database_Config {
	/**
	 * Banco de Desenvolvimento
	 * 
	 * @var		array
	 * @acccess	public
	 */
	public $default = array
	(
		'driver'	=> 'mysql',
		'persistent'=> false,
		'host' 		=> 'localhost',
		'user' 		=> 'phpg_us',
		'password' 	=> 'phpg_67',
		'database' 	=> 'phpg_bd',
		'encoding' 	=> 'utf8'
	);

	/**
	 * Banco de Tests
	 * 
	 * @var		array
	 * @access	public
	 */
	public $test = array
	(
		'driver'	=> 'mysql',
		'persistent'=> false,
		'host' 		=> 'localhost',
		'user' 		=> 'phpg_test_us',
		'password' 	=> 'phpg_test_67',
		'database' 	=> 'phpg_test_bd',
		'encoding' 	=> 'utf8'
	);

	/**
	 * Executa código no start da classe DatabaseConfig
	 *
	 * define o banco do SGE conforme a máquina local
	 *
	 * @return void
	 */
	public function __construct($banco='default')
	{
		$this->default = $this->$banco;
	}
}
