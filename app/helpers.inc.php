<?php

Class Helpers {
	
	static function rglob($pattern, $flags = 0, $path = '') {
		if (!$path && ($dir = dirname($pattern)) != '.') {
			if ($dir == '\\' || $dir == '/') $dir = '';
			return self::rglob(basename($pattern), $flags, $dir . '/');
		}
		$paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
		$files = glob($path . $pattern, $flags);
		foreach ($paths as $p) $files = array_merge($files, self::rglob($pattern, $flags, $p . '/'));
		return $files;
	}
	
	static function sort_by_length($a,$b){
		if($a == $b) return 0;
		return (strlen($a) > strlen($b) ? -1 : 1);
	}
	
	static function file_path_to_url($file_path) {
		return preg_replace(array('/\d+?\./', '/\.\/content(\/)?/'), '', $file_path);
	}
	
	static function url_to_file_path($url) {
		$file_path = './content';
		# Split the url and recursively unclean the parts into folder names
		$url_parts = explode('/', $url);
		foreach($url_parts as $u) {
				# Look for a folder at the current $path
				$matches = array_keys(Helpers::list_files($file_path, '/^\d+?\.'.$u.'$/', true));
				# No matches means a bad url
				if(empty($matches)) return false; 
				else $file_path .=  '/'.$matches[0];
		}
		return $file_path;
	}
	
	static function has_children($dir) {
		# check if this folder contains inner folders - if it does, then it is a category
		$inner_folders = Helpers::list_files($dir, '/.*/', true);
		return !empty($inner_folders);
	}

	static function list_files($dir, $regex, $folders_only = false) {
		if(!is_dir($dir)) return array();
		$glob = ($folders_only) ? glob($dir."/*", GLOB_ONLYDIR) : glob($dir."/*");
		if(!$glob) return array();
		# loop through each glob result and push it to $dirs if it matches the passed regexp
		$files = array();
		foreach($glob as $file) {
			# strip out just the filename
			preg_match('/\/([^\/]+?)$/', $file, $slug);
			if(preg_match($regex, $slug[1])) $files[$slug[1]] = $file;
		}
		# sort list in reverse-numeric order
		krsort($files, SORT_NUMERIC);
		return $files;
	}
	
	static function relative_root_path($file_path) {
		$link_path = '';
		if(!preg_match('/index/', $file_path)) {
			$link_path .= '../';
			preg_match_all('/\//', $file_path, $slashes);
			for($i = 1; $i < count($slashes[0]); $i++) $link_path .= '../';
		}
		return $link_path;
	}
	
}

?>