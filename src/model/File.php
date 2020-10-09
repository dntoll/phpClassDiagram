<?php

namespace model;

class File {

	private $m_fileName;

	public function __construct($filename) {

		
		if(is_string($filename) == false) {
			throw new \Exception("wrong argument");
		}
		if(is_object($filename) == true) {
			throw new \Exception("wrong argument type");
		}
		$this->m_fileName = $filename;
		//$parentFolder->parentFolder = $parentFolder;
	}
	
	
	public function getName() {
		return substr($this->m_fileName, strrpos($this->m_fileName, "/")+1);
	}
	
	public function getFullName() {
		return $this->m_fileName;
	}
	
	public function isDirectory() {
		return false;
	}
	
	public function isPHPFile() {
		
		return 	is_file($this->m_fileName) && 
				strrpos($this->m_fileName, ".php") !== FALSE ||
				strrpos($this->m_fileName, ".inc") !== FALSE;
	}
	
	public function getFile(Folder $path) {
		
		return new File($this->m_fileName . "/" .  $path->m_fileName);
	}

	public function getCode() {
		if (is_file($this->m_fileName) == FALSE) {
			throw new \Exception("file not found $this->m_fileName");
		}

		return new PHPCode(file_get_contents($this->m_fileName));
	}
	
	
	
	
}
