<?php
/**
 * Class Perfil
 * 
 * @package		Sistema
 * @package		Sistema.Model
 */
/**
 * Include files
 */
require_once(APP.'Modules/Sistema/Model/SistemaAppModel.php');
class Perfil extends SistemaAppModel {
	/**
	 * Nome da tabela de cidades
	 * 
	 * @var		string	
	 * @access	public
	 */
	public $tabela		= 'perfis';

	/**
	 * Chave primária do model usuários
	 * 
	 * @var		array
	 * @access	public
	 */
	public $primaryKey 	= array('id');

	/**
	 * Nickname para a tabela usuarios
	 * 
	 * @var		string
	 * @access	public
	 */
	public $alias		= 'Perfil';

	/**
	 * Propriedade de cada campo da tabela usuários
	 * 
	 * @var		array
	 * @acess	public
	 */
	public $esquema 	= array
	(
		'id'			=> array
		(
			'tit'		=> 'Id',
		),
		'nome'			=> array
		(
			'tit'		=> 'Nome',
		)
	);

	/**
	 * Executa código antes de excluir um perfil no banco
	 *
	 * - Perfil ADMINISTRADOR não pode ser excluído
	 * 
	 * @return boolean
	 */
	public function beforeExclude()
	{
		if (isset($this->data['id']) && $this->data['id']==1)
		{
			$this->erro = 'O Perfil ADMINISTRADOR não pode ser excluído !!!';
			return false;
		}
		return parent::beforeExclude();
	}
}
