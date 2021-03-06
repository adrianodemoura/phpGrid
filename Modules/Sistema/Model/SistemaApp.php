<?php
/**
 * Class SistemaAppModel do módulo sistema
 * 
 * Classe Pai de todos os Models do módulo sistema
 * 
 * @package			Sistema
 * @subpackage		Sistema.Model
 */
appUses('Model','Model');
class SistemaApp extends Model {
	/**
	 * Prefixo para as tabelas do módulo sistema
	 * 
	 * @var		string
	 * @access	public
	 */
	public $prefixo = 'sis_';
}
