<?php

/**
 * Coqoo plugin
 * access-log & page-rank & view-limit
 * composer add to monolog
 *
 * @author kaz from Geeks-Dev
 * @link http://www.geeks-dev.com
 * @license http://opensource.org/licenses/MIT
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Coqoo_Logs {
	private $base_url;
	private $timezone;
	private $hour_limit;
	private $rank_mode;
	private $rank_limit;
	private $redirect_url;
	
	public function plugins_loaded() {

	}

	public function request_url(&$url) {
		
	}

	public function before_load_content(&$file) {

	}

	public function after_load_content(&$file, &$content) {
		
	}

	public function before_404_load_content(&$file) {

	}

	public function after_404_load_content(&$file, &$content) {

	}

	public function config_loaded(&$settings) {
		$this->timezone = isset($settings['timezone'])?$settings['timezone']:ini_get('date.timezone');
		$this->hour_limit = isset($settings['hour_limit'])?$settings['hour_limit']:30;
		$this->rank_mode = isset($settings['rank_mode'])?$settings['rank_mode']:'m';
		$this->rank_limit = isset($settings['rank_limit'])?$settings['rank_limit']:20;
		$this->base_url = $settings['base_url'];
		$this->redirect_url = isset($settings['redirect_url'])?$settings['redirect_url']:$settings['base_url'];
	}

	public function file_meta(&$meta) {

	}

	public function content_parsed(&$content) {

	}

	public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page) {
		if($current_page){
			$filename = $this->setup();
			$this->countup($current_page,$filename);
		}
	}

	public function before_twig_register() {

	}

	public function before_render(&$twig_vars, &$twig) {
		$twig_vars['rank'] = $this->get_ranking($this->rank_mode);
	}

	public function after_render(&$output) {

	}

	public function setup()
	{
		
		date_default_timezone_set($this->timezone);
		$rootpath = 'logs/'.date('Y').'/';
		$filepath = 'logs/'.date('Y/m').'/';
		$filename = $filepath.date('d').'.log';
		try
		{
			if ( !is_dir($rootpath))
			{
				mkdir($rootpath, 0777, true);
				chmod($rootpath, 0777);
			}
			if ( !is_dir($filepath))
			{
				mkdir($filepath, 0777, true);
				chmod($filepath, 0777);
			}

			$handle = fopen($filename, 'a');
		}
		catch (\Exception $e)
		{
			throw new Exception('Unable to create or write to the log file. Check the permissions on logs dir');
		}

		if ( !filesize($filename))
		{
			chmod($filename, 0666);
		}
		fclose($handle);

		return $filename;

	}

	public function dump_log($filename){
		$result = array();
		$pattern = '/\[(?P<date>.*)\] (?P<logger>.+)\.(?P<level>[A-Z]+): (?P<message>.*) (?P<context>[^ ]+) (?P<extra>[^ ]+)$/';
		$data = file_get_contents($filename);
		$data = explode( PHP_EOL, $data);
		if(strlen($data[count($data)-1]) == 0){
			unset($data[count($data)-1]);
		}

		foreach ($data as $key => $value) {
			preg_match($pattern, $value, $log);
			$context = json_decode($log['context'], true);
			$message = json_decode($log['message'], true);
			$result[] = array(
				'date' => $log['date'],
				'logger' => $log['logger'],
				'level' => $log['level'],
				'url' => $message[0],
				'title' => $message[1],
				'category' => $message[2],
				'thumbnail' => $message[3],
				'date' => $message[4],
				'context0' => $context[0],
				'context1' => $context[1],
			);
		}

		return $result;
	}

	public function countup($current_page,$filename){

		$dump = array_reverse($this->dump_log($filename));
		$this->check_limit($current_page['url'],$dump);
		$count = 0;
		foreach ($dump as $key => $value) {
			if($value['url'] == $current_page['url'] && $value['context0'] == $_SERVER["REMOTE_ADDR"]){
				$count = $value['context1'] + 1;
				break;
			}
		}
		$log = new Logger('pico-logs');
		$log->pushHandler(new StreamHandler($filename, Logger::INFO));
		$log->addInfo('["'.$current_page['url'].'","'.$current_page['title'].'","'.$current_page['category'].'","'.$current_page['thumbnail'].'","'.$current_page['date'].'"]',array($_SERVER["REMOTE_ADDR"],$count));
	}

	public function rank_count($filename){
		$dump = $this->dump_log($filename);
		$rank = array();
		foreach ($dump as $key => $value) {
			if($value['context1'] == 0){
				$rankkey = array_search($value['url'], array_column($rank, 'url'));
				if($rankkey === false){
					$rank[] = array('count' => 1,'url' => $value['url'],'title' => $value['title'],'category' => $value['category'],'thumbnail'=>$value['thumbnail'],'date' => $value['date']);
				}else{
					$rank[$rankkey]['count']++;
				}
			}
		}
		return $rank;
	}

	public function check_limit($url,$dump){
		$same_addr = array();
		$count = 0;
		foreach($dump as $value){
			$datetime = strtotime($value['date']);
			if($url == $value['url'] && $value['context0'] == $_SERVER['REMOTE_ADDR'] && $datetime < strtotime('now') && $datetime >= strtotime( "-1 hour" )){
				$count++;
			}
		}
		if($count > $this->hour_limit){
			header("Location: ".$this->redirect_url."");
		}
		
	}

	public function get_ranking($mode){
		$filename = 'logs/'.date('Y/m').'/'.date('d').'.log';
		$rank = array();
		switch ($mode) {
			case 'd':
				$rank = $this->rank_count($filename);
				break;
			case 'y':
				$filedir = 'logs/'.date('Y').'/';
			case 'm':
				$filedir = 'logs/'.date('Y/m').'/';
			default:
				$file_list = $this->list_files($filedir);
				foreach($file_list as $key => $file){
					$daily_rank = $this->rank_count($file);
					foreach ($daily_rank as $value) {
						$rankkey = array_search($value['url'], array_column($rank, 'url'));
						if($rankkey === false){
							$rank[] = array('count' => $value['count'],'url' => $value['url'],'title' => $value['title'],'category' => $value['category'],'thumbnail'=>$value['thumbnail'],'date' => $value['date']);
						}else{
							$rank[$rankkey]['count'] += $value['count'];
						}
					}
				}
				break;
		}
		usort($rank,array($this,"rank_sort"));
		$rank = array_slice($rank, 0, $this->rank_limit);
		return $rank;
	}

	public function rank_sort($a, $b){
		return $a['count'] < $b['count'];
	}

	public function list_files($dir){
		$files = array();
		$list = scandir($dir);
		foreach($list as $file){
			if($file == '.' || $file == '..'){
				continue;
			} else if (is_file($dir . $file)){
				$files[] = $dir . $file;
			} else if( is_dir($dir . $file) ) {
				$files = array_merge($files, list_files($dir . $file . '/'));
			}
		}
		return $files;
	}
}

?>
