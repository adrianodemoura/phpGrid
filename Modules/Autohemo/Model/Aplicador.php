<?php
/**
 * Class Aplicador
 * 
 * @package		Aplicador
 * @package		Autohemo.Model
 */
appUses('Model','AutohemoApp');
class Aplicador extends AutohemoApp {
	/**
	 * Nome da tabela de cidades
	 * 
	 * @var		string	
	 * @access	public
	 */
	public $tabela		= 'aplicadores';

	/**
	 * Chave primária do model usuários
	 * 
	 * @var		array
	 * @access	public
	 */
	public $primaryKey 	= array('id');

	/**
	 * Propriedade de cada campo da tabela salas
	 * 
	 * @var		array
	 * @acess	public
	 */
	public $esquema = array
	(
		'id'				=> array
		(
			'tit'			=> 'Id',
		),
		'nome'				=> array
		(
			'tit'			=> 'Nome',
			'notEmpty'		=> true,
			'pesquisar'		=> '&'
		),
		'email'	=> array
		(
			'tit'			=> 'e-mail',
			'upperOff'		=> true,
			'pesquisar'		=> '&',
			'unique'		=> true,
		),
		'tele_resi'			=> array
		(
			'tit'			=> 'Telefone',
			'mascara'		=> '(99)9999-9999'
		),
		'celular'			=> array
		(
			'tit'			=> 'Celular',
			'mascara'		=> '(99)9999-9999'
		),
		'aniversario'			=> array
		(
			'tit'			=> 'Aniversário',
			'mascara'		=> '99/99'
		),
		'cidade_id'			=> array
		(
			'tit'			=> 'Cidade',
			'belongsTo' 	=> array
			(
				'Sistema.Cidade'	=> array
				(
					'key'	=> 'id',
					'fields'=> array('id','nome','uf'),
					'order'	=> array('nome','uf'),
					'ajax'	=> 'sistema/cidades/get_options/',
					'txtPesquisa' => 'Digite o nome da cidade para pesquisar ...',
				),
			),
		),
	);
}
