<?php

namespace model;

require_once("ClassParser.php");
require_once("File.php");
require_once("Folder.php");
require_once("ClassNode.php");



class ProjectParser {
	
	
	public function __construct(Folder $path) {
		$this->path = $path;	

	}
	
	public function getClasses() {
		$ret = array();
		
		
		
		$files = $this->path->getFiles();


		
		foreach($files as $file)  {
			
			if ($file->isDirectory() == false ) {
				try {

					if ($file->isPHPFile()) {
					
						
						$classParser = new ClassParser($file->getCode());
						$namespace = $classParser->getNamespace();
						
						$classes = $classParser->getClasses();
						$fanout = $classParser->getDependencies();
						$stringconstants = $classParser->getStringConstants();


						foreach($classes as $class) {
							$ret[] = new ClassNode($namespace, $class, $fanout, $stringconstants);
						}
					}
				}catch(\Exception $e) {
					var_dump($e);
				}
				
			}
			if ($file->isDirectory()) {
				$pp = new ProjectParser($file);
				
				$ret = array_merge($ret, $pp->getClasses());
				
			}
		}


		//find hidden dependencies


		return $ret;
	}
}