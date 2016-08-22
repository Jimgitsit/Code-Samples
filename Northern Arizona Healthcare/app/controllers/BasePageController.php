<?php

use \Gozer\Core\CoreController;

class BasePageController extends CoreController {
	
	protected $twigVars = array();
	
	public function __construct() {
		parent::__construct();
		
		$this->initTwig();
		
		// Add custom filters
		$this->twig->addFilter(new \Twig_SimpleFilter('ucfirst', 'ucfirst'));
		$this->twig->addFilter(new \Twig_SimpleFilter('joinMetaDataValues', array('AdminMetaDataPage', 'joinMetaDataValues')));
		$this->twig->addFilter(new Twig_SimpleFilter('truncate', array('AdminMetaDataPage', 'truncate')));
		
		$this->twigVars['env'] = ENV;
	}
}