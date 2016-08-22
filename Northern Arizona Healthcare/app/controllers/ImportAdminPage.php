<?php

use \Gozer\Core\CoreController;

class ImportAdminPage extends BasePageController {
	public function defaultAction() {
		$this->initTwig();
		
		$importLib = new Import();
		$imports = $importLib->getPreviousImports();
		
		$this->twigVars['imports'] = $imports;
		
		$this->twig->display('import_admin.twig', $this->twigVars);
	}
}