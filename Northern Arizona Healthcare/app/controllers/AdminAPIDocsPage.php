<?php

Use \Documen\Documen;

class AdminAPIDocsPage extends BasePageController {
	public function defaultAction() {
		$this->twig->display('admin-apidocs.twig', $this->twigVars);
	}
}