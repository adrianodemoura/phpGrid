<?php
/**
 * Class LocacaoApp
 * 
 * Classe Pai de todos os controllers do módulo locação
 * 
 * @package		Locacao
 * @package		Locacao.Controller
 */
appUses('Controller','');
class LocacaoAppController extends Controller {
	/**
	 * 
	 */
	public function beforeRender()
	{
		$this->viewVars['tituloModule'] = 'Locação';
		parent::beforeRender();
	}
}
