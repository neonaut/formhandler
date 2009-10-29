<?php
/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *
 * $Id$
 *                                                                        */

/**
 * A finisher showing the content of ###TEMPLATE_CONFIRMATION### replacing all common Formhandler markers
 * plus ###PRINT_LINK###, ###PDF_LINK### and ###CSV_LINK###.
 *
 * The finisher sets a flag in $_SESSION, so that Formhandler will only call this finisher and nothing else if the user reloads the page.
 *
 * A sample configuration looks like this:
 * <code>
 * finishers.3.class = Tx_Formhandler_Finisher_Confirmation
 * finishers.3.config.returns = 1
 * finishers.3.config.pdf.class = Tx_Formhandler_Generator_PDF
 * finishers.3.config.pdf.exportFields = firstname,lastname,interests,pid,ip,submission_date
 * finishers.3.config.pdf.export2File = 1
 * finishers.3.config.csv.class = Tx_Formhandler_Generator_CSV
 * finishers.3.config.csv.exportFields = firstname,lastname,interests
 * </code>
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Finisher
 */
class Tx_Formhandler_Finisher_Confirmation extends Tx_Formhandler_AbstractFinisher {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		
		//set session value to prevent another validation or finisher circle. Formhandler will call only this Finisher if the user reloads the page.
		session_start();
		$_SESSION['submitted_ok'] = 1;
		
		$action = $this->gp['action'];
		if($action) {
			$this->processAction($action);
		}

		//read template file
		$this->templateFile = Tx_Formhandler_Globals::$templateCode;
		
		//set view
		$view = $this->componentManager->getComponent('Tx_Formhandler_View_Confirmation');
			
		//show TEMPLATE_CONFIRMATION
		$view->setTemplate($this->templateFile, ('CONFIRMATION' . $this->settings['templateSuffix']));
		if(!$view->hasTemplate()) {
			$view->setTemplate($this->templateFile, 'CONFIRMATION');
			if(!$view->hasTemplate()) {
				Tx_Formhandler_StaticFuncs::debugMessage('no_confirmation_template');
			}
		}
		
		$view->setSettings($_SESSION['formhandlerSettings']['settings']);
		return $view->render($this->gp, array());
	}
	
	protected function processAction($action) {
		if(is_array($this->settings['actions.'])) {
			foreach($this->settings['actions.'] as $key=>$options) {
				if(strpos($key, '.') !== FALSE) {
					$currentAction = str_replace('.', '', $key);
					if($currentAction === $action) {
						$class = $options['class'];
						if($class) {
							$class = Tx_Formhandler_StaticFuncs::prepareClassName($class);
							$object = $this->componentManager->getComponent($class);
							$object->init($this->gp, $options['config.']);
							$object->process();
						}
					}
				}
			}
		}
	}

}
?>
