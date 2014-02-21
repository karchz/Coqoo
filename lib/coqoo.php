<?php 
require(LIB_DIR .'pico.php');

/**
 * Coqoo
 *
 * @author Geeks-dev
 * @link http://www.geeks-dev.com
 * @license http://opensource.org/licenses/MIT
 * @version 1.0
 */
class Coqoo extends Pico{
	private $is_extract;
	private $current_extract;
	/**
	 * The constructor carries out all the processing in Pico.
	 * Does URL routing, Markdown processing and Twig processing.
	 */
	public function __construct()
	{
		// Load plugins
		$this->load_plugins();
		$this->run_hooks('plugins_loaded');

		// Load the settings
		$settings = $this->get_config();
		$this->run_hooks('config_loaded', array(&$settings));

		// Get request url and script url
		$url = '';
		$request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
		$script_url  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';

		$request_url = urldecode($request_url);
		// $$script_url = urldecode($script_url);
		// Get our url path and trim the / of the left and the right
		if($request_url != $script_url) $url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
		$url = preg_replace('/\?.*/', '', $url); // Strip query string

		if($url == 'sitemap.xml'){
			$this->get_sitemap();
		}elseif(substr($url, 0, 4) == "tag/"){
			$this->is_extract = "tag";
			$this->current_extract = substr($url, 4);
		}elseif(substr($url, 0, 9) == "category/"){

			$this->is_extract = "category";
			$this->current_extract = substr($url, 9);
		}

		$this->run_hooks('request_url', array(&$url));

		// Get the file path
		if($url) $file = CONTENT_DIR . $url;
		else $file = CONTENT_DIR .'index';

		// Load the file
		if(is_dir($file)) $file = CONTENT_DIR . $url .'/index'. CONTENT_EXT;
		else $file .= CONTENT_EXT;

		$this->run_hooks('before_load_content', array(&$file));
		if(file_exists($file)){
			$content = file_get_contents($file);
		}else {
			$this->run_hooks('before_404_load_content', array(&$file));
			$content = file_get_contents(CONTENT_DIR .'404'. CONTENT_EXT);
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
			$this->run_hooks('after_404_load_content', array(&$file, &$content));
		}
		$this->run_hooks('after_load_content', array(&$file, &$content));

		$meta = $this->read_file_meta($content);
		// Skip Ignore status
        foreach($settings['ignore_status'] as $value){
            if(strtolower($meta['status']) == strtolower($value)){
                $this->run_hooks('before_404_load_content', array(&$file));
				$content = file_get_contents(CONTENT_DIR .'404'. CONTENT_EXT);
				header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
				$this->run_hooks('after_404_load_content', array(&$file, &$content));
				$meta = $this->read_file_meta($content);
				$this->run_hooks('after_load_content', array(&$file, &$content));
            }
        }
		$this->run_hooks('file_meta', array(&$meta));

		$this->run_hooks('before_parse_content', array(&$content));
		$content = $this->parse_content($content);
		$this->run_hooks('after_parse_content', array(&$content));
		$this->run_hooks('content_parsed', array(&$content)); // Depreciated @ v0.8
		
		// Get all the pages
		$pages = $this->get_pages($settings['base_url'], $settings['pages_order_by'], $settings['pages_order'], $settings['excerpt_length']);
		$prev_page = array();
		$current_page = array();
		$next_page = array();
		$end_page = end($pages);
		$all_pages = $pages;
		reset($pages);
		while($current_page = current($pages)){
			if(((isset($meta['title'])) && ($meta['title'] == $current_page['title'])) && strtotime($meta['date']) == strtotime($current_page['date'])){
				break;
			}
			next($pages);
		}
		if($current_page != $end_page){
			$prev_page = next($pages);
			prev($pages);
		}else{
			$prev_page = null;
		}
		$next_page = prev($pages);
		reset($pages);
        if($this->is_extract == "tag"){
        	foreach ($pages as $key => $value) {
        		if(!in_array($this->current_extract,$value['tags'])){
        			unset($pages[$key]);
        		}
        	}
        }elseif($this->is_extract == "category"){
        	foreach ($pages as $key => $value) {
	        	if($this->current_extract != $value['category']){
	        		unset($pages[$key]);
	        	}
        	}
        }


        

		$this->run_hooks('get_pages', array(&$pages, &$current_page, &$prev_page, &$next_page));

		// Load the theme
		$this->run_hooks('before_twig_register');
		Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem(THEMES_DIR . $settings['theme']);
		$twig = new Twig_Environment($loader, $settings['twig_config']);
		$twig->addExtension(new Twig_Extension_Debug());
		$twig_vars = array(
			'config' => $settings,
			'base_dir' => rtrim(ROOT_DIR, '/'),
			'base_url' => $settings['base_url'],
			'theme_dir' => THEMES_DIR . $settings['theme'],
			'theme_url' => $settings['base_url'] .'/'. basename(THEMES_DIR) .'/'. $settings['theme'],
			'site_title' => $settings['site_title'],
			'meta' => $meta,
			'content' => $content,
			'pages' => $pages,
			'all_pages' => $all_pages,
			'prev_page' => $prev_page,
			'current_page' => $current_page,
			'next_page' => $next_page,
			'is_front_page' => $url ? false : true,
		);

		$template_file = $url?'sub':'index';
		
		$template = (isset($meta['template']) && $meta['template']) ? $meta['template'] : $template_file;

		if ($this->is_extract == 'tag') {
			// override 404 header
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			// set as front page, allows using the same navigation for index and tag pages
			$twig_vars["is_front_page"] = true;
			// sets page title to tag
			$twig_vars["meta"]["title"] = $settings['tag_prefix'] . $this->current_extract;
			if($pages){
				$template = $template_file;
			}
		}elseif($this->is_extract == 'category'){
			// override 404 header
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			// set as front page, allows using the same navigation for index and tag pages
			$twig_vars["is_front_page"] = true;
			// sets page title to category
			$twig_vars["meta"]["title"] = $settings['category_prefix'] . $this->current_extract;
			if($pages){
				$template = $template_file;
			}
		}

		

		if(!$twig_vars["is_front_page"]){
			//	Get Same Category
			$same_category = array();
			foreach($pages as $p){
				if($p['category'] == $meta['category'] && ($p['title'] != $meta['title'] || strtotime($meta['date']) != strtotime($p['date']))){
					array_push($same_category,$p);
				}

			}
			$twig_vars['same_category'] = $same_category;
		}else{
			// List Pagenation
			$page_indicator = isset($settings['page_indicator'])?$settings['page_indicator']:'page';
			$always_paging = isset($settings['always_paging'])?$settings['always_paging']:false;
			if (isset($_GET[$page_indicator])||$always_paging){
				$page_limit = isset($settings['page_limit'])?$settings['page_limit']:10;
				if(is_null($_GET[$page_indicator])){
					$page_number = 1;
				}else{
					$page_number = $_GET[$page_indicator];
				}
				$offset = ($page_number-1) * $page_limit;
				$total_pages = ceil(count($twig_vars["pages"]) / $page_limit);
				if ($page_number > 1) {
					$twig_vars['prev_page'] =  $settings['base_url'].'/'.$url.'?'.$page_indicator.'='.($page_number - 1);
				}else{
					$twig_vars['prev_page'] = false;
				}
				if ($page_number < $total_pages) {
					$twig_vars['next_page'] =  $settings['base_url'].'/'.$url.'?'.$page_indicator.'='.($page_number + 1);
				}else{
					$twig_vars['next_page'] = false;
				}
				$twig_vars['page_list'] = array();
				for($p=1;$p<=$total_pages;$p++){
					$twig_vars['page_list'][$p-1]['link'] = $settings['base_url'].'/'.$url.'?'.$page_indicator.'='.$p;
					$twig_vars['page_list'][$p-1]['num'] = $p;
				}
				$twig_vars["is_page"] = true;
				$twig_vars["total_pages"] = $total_pages;
				$twig_vars["current_page"] = $settings['base_url'].'/'.$url.'?'.$page_indicator.'='.$page_number;
				$twig_vars["current_page_num"] = $page_number;
				$twig_vars["pages"] = array_slice($twig_vars["pages"], $offset, $page_limit);
			}
		}

		$this->run_hooks('before_render', array(&$twig_vars, &$twig, &$template));
		$output = $twig->render($template .$settings['template_extension'], $twig_vars);
		$this->run_hooks('after_render', array(&$output));
		echo $output;
	}
    /**
     * Loads the config
     *
     * @return array $config an array of config values
     */
    protected function get_config()
    {
            global $config;
            @include_once(ROOT_DIR .'config.php');

            $defaults = array(
                    'site_title' => 'Coqoo',
                    'base_url' => $this->base_url(),
                    'theme' => 'default',
                    'date_format' => 'jS M Y',
                    'twig_config' => array('cache' => false, 'autoescape' => false, 'debug' => false),
                    'pages_order_by' => 'date',
                    'pages_order' => 'desc',
                    'excerpt_length' => 50,
                    'page_indicator' => 'page'
            );

            if(is_array($config)) $config = array_merge($defaults, $config);
            else $config = $defaults;

            return $config;
    }
	/**
     * Parses the file meta from the txt file header
     *
     * @param string $content the raw txt content
     * @return array $headers an array of meta values
     */
    protected function read_file_meta($content)
    {
            global $config;
            
            $headers = array(
                    'title'         => 'Title',
                    'description'   => 'Description',
                    'keywards' 		=> 'Keywards',
                    'author'        => 'Author',
                    'date'          => 'Date',
                    'robots'        => 'Robots',
                    'template'      => 'Template',
                    'slug' 			=> 'Slug',
	                'category' 		=> 'Category',
	                'status' 		=> 'Status',
	                'type' 			=> 'Type',
	                'thumbnail' 	=> 'Thumbnail',
	                'icon' 			=> 'Icon',
	                'tags'			=> 'Tags'
            );

            // Add support for custom headers by hooking into the headers array
            $this->run_hooks('before_read_file_meta', array(&$headers));

            foreach ($headers as $field => $regex){
                if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $content, $match) && $match[1]){
                        $headers[ $field ] = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $match[1]));
                } else {
                        $headers[ $field ] = '';
                }
            }

            if (strlen($headers['tags']) > 1){
            	$headers['tags'] = explode(',', $headers['tags']);
			}else{
				$headers['tags'] = NULL;
			}
			
			if(strlen($headers['date']) > 1){
	            $temp = explode(' ',$headers['date']);
				if(!isset($temp[1])){
					$headers['date'] = $headers['date'].' 00:00:00';
				}
			}
			// if thumbnail is none, auto set thumbnail
			$disable_array = array('none','false','blank','no','space');
			if(in_array($headers['thumbnail'],$disable_array) || ($headers['thumbnail'] == "" && $config['autoset_thumbnail'][0] == "false")){
        		$headers['thumbnail'] == '';
            	unset($headers['thumbnail']);
	        }else{
	            if(strlen($headers['thumbnail']) < 3){
		            switch ($config['autoset_thumbnail'][0]) {
						case 'logo':
							$headers['thumbnail'] = $config['base_url'].'/'.$config['images_dir'].'/logo.'.$config['autoset_thumbnail'][1];
							break;
						case 'category':
							if(strlen($headers['category']) < 1){
								if($config['autoset_thumbnail'][2]){
									$headers['thumbnail'] = $config['base_url'].'/'.$config['images_dir'].'/logo.'.$config['autoset_thumbnail'][1];
								}
							}else{
								if(file_exists(ROOT_DIR.'/'.$config['images_dir'].'/'.urlencode($headers['category']).'.'.$config['autoset_thumbnail'][1])){
									$headers['thumbnail'] = $config['base_url'].'/'.$config['images_dir'].'/'.urlencode($headers['category']).'.'.$config['autoset_thumbnail'][1];
								}else{
									$headers['thumbnail'] = $config['base_url'].'/'.$config['images_dir'].'/logo.'.$config['autoset_thumbnail'][1];
								}
							}
							break;
						default :
							break;
		            }
	            }else{
	            	if(strpos($headers['thumbnail'],'://') === false){
	            		$headers['thumbnail'] = $config['base_url'].'/'.$config['images_dir'].'/'.$headers['thumbnail'];
	            	}
	            }
            }
            if(isset($headers['date'])) $headers['date_formatted'] = date($config['date_format'], strtotime($headers['date']));

            return $headers;
    }
	

	/**
	 * Parses the content using Markdown
	 *
	 * @param string $content the raw txt content
	 * @return string $content the Markdown formatted content
	 */
	protected function parse_content($content)
	{
		$content = preg_replace_callback('#^(`{3}.*)?(\s*)?/\*.+?\*/(\s*)?(`{3})?#s', function($matches){
			if(substr($matches[0],0,3) == '```'){
				return $matches[0];
			}else{
				return '';
			}
		}, $content); // Remove comments and meta
		$content = str_replace('%base_url%', $this->base_url(), $content);
		$content = MarkdownExtended($content);
		return $content;
	}

    /**
     * Get a list of pages
     *
     * @param string $base_url the base URL of the site
     * @param string $order_by order by "alpha" or "date"
     * @param string $order order "asc" or "desc"
     * @return array $sorted_pages an array of pages
     */
    protected function get_pages($base_url, $order_by = 'date', $order = 'desc', $excerpt_length = 50)
    {
        global $config;
        
        $pages = $this->get_files(CONTENT_DIR, CONTENT_EXT);
        $sorted_pages = array();
        $date_id = 0;
        foreach($pages as $key=>$page){
            // Skip 404
            if(basename($page) == '404'. CONTENT_EXT){
                    unset($pages[$key]);
                    continue;
            }
            // Skip Ignore dir
            foreach($config['skip_dir'] as $value){
	            if(strpos($page,CONTENT_DIR.$value.'/') === 0){
	                unset($pages[$key]);
	                continue 2;
	            }
	        }
            // Ignore Emacs (and Nano) temp files
            if (in_array(substr($page, -1), array('~','#'))) {
                    unset($pages[$key]);
                    continue;
            }                        
            // Get title and format $page
            $page_content = file_get_contents($page);
            $page_meta = $this->read_file_meta($page_content);

            if(strtolower($page_meta['status']) == $config['skip_list_status']){
            	unset($pages[$key]);
	            continue;
            }
	        // Skip Ignore status
            foreach($config['ignore_status'] as $value){
	            if(strtolower($page_meta['status']) == strtolower($value)){
	                unset($pages[$key]);
	                continue 2;
	            }
	        }


            $page_content = $this->parse_content($page_content);
            $url = str_replace(CONTENT_DIR, $base_url .'/', $page);
            $url = str_replace('index'. CONTENT_EXT, '', $url);
            $url = str_replace(CONTENT_EXT, '', $url);
            $data = array(
                    'title' 			=> isset($page_meta['title']) ? $page_meta['title'] : '',
                    'url' 				=> $url,
                    'author' 			=> isset($page_meta['author']) ? $page_meta['author'] : '',
                    'date' 				=> isset($page_meta['date']) ? $page_meta['date'] : '',
                    'date_formatted'	=> isset($page_meta['date']) ? date($config['date_format'], strtotime($page_meta['date'])) : '',
                    'template'     		=> isset($page_meta['template']) ? $page_meta['template'] : '',
                    'slug' 				=> isset($page_meta['slug']) ? $page_meta['slug'] : '',
	                'category' 			=> isset($page_meta['category']) ? $page_meta['category'] : '',
	                'status' 			=> isset($page_meta['status']) ? $page_meta['status'] : '',
	                'type' 				=> isset($page_meta['type']) ? $page_meta['type'] : '',
	                'thumbnail' 		=> isset($page_meta['thumbnail']) ? $page_meta['thumbnail'] : '',
	                'icon' 				=> isset($page_meta['icon']) ? $page_meta['icon'] : '',
	                'tags' 				=> isset($page_meta['tags']) ? $page_meta['tags'] : '',
                    'content' => $page_content,
                    'excerpt' => $this->limit_words(strip_tags($page_content), $excerpt_length)
            );

            // Extend the data provided with each page by hooking into the data array
            $this->run_hooks('get_page_data', array(&$data, $page_meta));

            if($order_by == 'date' && isset($page_meta['date'])){
                    // $sorted_pages[strtotime($page_meta['date']).$date_id] = $data;
                    $sorted_pages[strtotime($page_meta['date'])] = $data;
                    $date_id++;
            }
            else $sorted_pages[] = $data;
        }
        
        if($order == 'desc') krsort($sorted_pages,SORT_NUMERIC);
        else ksort($sorted_pages,SORT_NUMERIC);
        
        return $sorted_pages;
    }


    //Take in Sitemap
    public function get_sitemap(){
    	global $config;
        
        $pages = $this->get_files(CONTENT_DIR, CONTENT_EXT);
        $sorted_pages = array();
        $date_id = 0;
        foreach($pages as $key=>$page){
            // Skip 404
            if(basename($page) == '404'. CONTENT_EXT){
                    unset($pages[$key]);
                    continue;
            }
            // Skip Ignore sitemap
            foreach($config['ignore_sitemap'] as $value){
            	if(strpos($page,CONTENT_DIR.$value.'/') === 0){
	                unset($pages[$key]);
	                continue 2;
            	}
            }

            // Ignore Emacs (and Nano) temp files
            if (in_array(substr($page, -1), array('~','#'))) {
                    unset($pages[$key]);
                    continue;
            }                        
            // Get title and format $page
            $page_content = file_get_contents($page);
            $page_meta = $this->read_file_meta($page_content);
            
            // Skip Ignore status
            foreach($config['ignore_status'] as $value){
	            if(strtolower($page_meta['status']) == strtolower($value)){
	                unset($pages[$key]);
	                continue 2;
	            }
	        }

            $page_content = $this->parse_content($page_content);
            $url = str_replace(CONTENT_DIR, $config['base_url'] .'/', $page);
            $url = str_replace('index'. CONTENT_EXT, '', $url);
            $url = str_replace(CONTENT_EXT, '', $url);
            $data = array(
                    'title' => isset($page_meta['title']) ? $page_meta['title'] : '',
                    'url' => $url,
                    'author' => isset($page_meta['author']) ? $page_meta['author'] : '',
                    'date' => isset($page_meta['date']) ? $page_meta['date'] : '',
                    'date_formatted' => isset($page_meta['date']) ? date($config['date_format'], strtotime($page_meta['date'])) : '',
                    'content' => $page_content,
                    'excerpt' => $this->limit_words(strip_tags($page_content), $config["excerpt_length"])
            );

            // Extend the data provided with each page by hooking into the data array
            $this->run_hooks('get_page_data', array(&$data, $page_meta));

            if($config["pages_order_by"] == 'date' && isset($page_meta['date'])){
                $sorted_pages[$page_meta['date'].$date_id] = $data;
                $date_id++;
            }
            else $sorted_pages[] = $data;

            
        }
        
        if($config['pages_order'] == 'desc') krsort($sorted_pages);
        else ksort($sorted_pages);

		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header('Content-Type: application/xml; charset=UTF-8');
		$xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		foreach( $sorted_pages as $page ){
			$xml .= '<url><loc>'.htmlspecialchars($page['url']).'</loc></url>';
		}	
		$xml .= '</urlset>';
		header('Content-Type: text/xml');
		die($xml);
		
	}

	/**
	* Helper function to limit the words in a string
	*
	* @param string $string the given string
	* @param int $lenght the lenght of string to limit to
	* @return string the limited string
	*/ 
	protected function limit_words($string, $length)
	{
		$excerpt = strip_tags($string);
		$excerpt = mb_substr($excerpt, 0 ,$length, 'UTF-8');
		if(mb_strlen($string) > $length){
			$excerpt .= ' &hellip;';
		} 
		return $excerpt;
	}

}

?>