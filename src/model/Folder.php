<?php

namespace model;

class Folder {

	private $folderPath;

	public function __construct($folderPath) {

		
		if(is_string($folderPath) == false) {
			throw new \Exception("wrong argument");
		}
		if(is_object($folderPath) == true) {
			throw new \Exception("wrong argument type");
		}
		$this->folderPath = $folderPath;
	}
	
	//@return array of String filenames
	public function getFiles() {
		
		if ($this->isDirectory() === false) {
			throw new \Exception("not a folder");
		}


		
		return $this->getChildren();
	}
	

	public function getName() {
		return substr($this->folderPath, strrpos($this->folderPath, "/")+1);
	}
	
	public function getFullName() {
		return $this->folderPath;
	}
	
	
	public function save($content, File $file) {

		$fileName = $file->getFullName();
		$this->createDirectories($file);

		assert(file_put_contents($this->folderPath . "/". $fileName, $content) !== FALSE);
	}
	
	public function rename($content, File $source, File $destination) {
		$this->save($content, $destination);
		$this->delete($source);
	}

	public function delete(File $source) {
		$fromFile = $this->folderPath . "/". $source->getFullName();
		unlink($fromFile);
	}
	
	private function createDirectories(File $file) {
		$fileName = $file->getFullName();
		$parts = explode("/", $fileName);

		if (count($parts) > 1) {
			$path = $this->folderPath;
			for($i = 0; $i < count($parts)-1; $i++) {
				$path .= "/" . $parts[$i];
				if (is_dir($path) == false) {
					mkdir($path);
				}
			}
		}
	}
	
	public function isDirectory() {
		return is_dir($this -> folderPath);
	}
	
	public function getFile(File $path) {
		
		return new File($this->folderPath . "/" .  $path->getFullName());
	}
	
	private function getChildren() {


		if (is_dir($this ->folderPath)) {
			
			
			$children = array();
			if ($dh = opendir($this->folderPath)) {


				while (($file = readdir($dh)) !== false) {


					if ($file == "." || $file == "..")
						continue;
					
					$childNode = $this->folderPath . "/" . $file;
					
					if (is_dir($childNode)) {					
						$children["0" . $childNode] = new Folder($childNode);
					} else {
						$children["1" . $childNode] = new File($childNode);
					}
				}
				closedir($dh);
			}
			ksort($children);
			return $children;
		} else {
			return array();
		}
	}
	
	
}
