<?php

class AdminImportsPage extends BasePageController {
	public function defaultAction() {
		
		$this->handlePost();
		
		$em = $this->getEntityManager();
		$imports = $em->getRepository('ImportEntity')->findBy(array(), array('dateRun' =>  'DESC'), 50);
		
		$this->twigVars['imports'] = $imports;
		
		$this->twig->display('admin-imports.twig', $this->twigVars);
	}
	
	private function handlePost() {
		if (!empty($this->post) && !empty($this->post['action'])) {
			switch ($this->post['action']) {
				case 'get-log':
					$logFile = $this->post['logFile'];
					$content = @file_get_contents(LOG_DIR . '/' . $logFile);
					if ($content !== false) {
						echo($content);
					}
					else {
						echo("Log file not found.");
					}
					
					exit();
					break;
			}
		}
	}
}