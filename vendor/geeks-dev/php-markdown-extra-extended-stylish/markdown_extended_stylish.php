<?php
require_once('markdown.php');
define( 'MARKDOWNEXTRAEXTENDED_VERSION',  "0.3" );

function MarkdownExtended($text, $default_claases = array()){
  $parser = new MarkdownExtraExtended_Parser($default_claases);
  return $parser->transform($text);
}

class MarkdownExtraExtended_Parser extends MarkdownExtra_Parser {
	# Tags that are always treated as block tags:
	var $block_tags_re = 'figure|figcaption|p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend';
	var $default_classes;
		
	function MarkdownExtraExtended_Parser($default_classes = array()) {
	    $default_classes = $default_classes;
		
		$this->block_gamut += array(
			"doFencedFigures" => 7,
			"doTodoLists" => 35,
			"doIconLists" => 10,
			"doIcons" => 10
		);
		
		parent::MarkdownExtra_Parser();
	}
	
	function transform($text) {	
		$text = parent::transform($text);				
		return $text;		
	}

	function doBlockQuotes($text) {
		$text = preg_replace_callback('/
			(?>^[ ]*>[ ]?
				(?:\((.+?)\))?
				[ ]*(.+\n(?:.+\n)*)
			)+	
			/xm',
			array(&$this, '_doBlockQuotes_callback'), $text);

		return $text;
	}
	
	function _doBlockQuotes_callback($matches) {
		$cite = $matches[1];
		$bq = '> ' . $matches[2];
		# trim one level of quoting - trim whitespace-only lines
		$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
		$bq = $this->runBlockGamut($bq);		# recurse

		$bq = preg_replace('/^/m', "  ", $bq);
		# These leading spaces cause problem with <pre> content, 
		# so we need to fix that:
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', 
			array(&$this, '_doBlockQuotes_callback2'), $bq);
		
		$res = "<blockquote";
		$res .= empty($cite) ? ">" : " cite=\"$cite\">";
		$res .= "\n$bq\n</blockquote>";
		return "\n". $this->hashBlock($res)."\n\n";
	}

	function doFencedCodeBlocks($text) {
		$less_than_tab = $this->tab_width;
		
		$text = preg_replace_callback('{
				(?:\n|\A)
				# 1: Opening marker
				(
					\s*~{3,}|\s*`{3,} # Marker: three tilde or more.
				)
				
				[ ]?(\w+)?(?:,[ ]?(\d+))?(?:,[ ]?(\[.+\]))?(?:,[ ]?(.+))?[ ]* \n # Whitespace and newline following marker.
				\n*
				# 3: Content
				(
					(?>
						(?!\1 [ ]* \n)	# Not a closing marker.
						.*\n+
					)+
				)
				
				# Closing marker.
				\1 [ ]* \n
			}xm',
			array(&$this, '_doFencedCodeBlocks_callback'), $text);

		return $text;
	}
	
	function _doFencedCodeBlocks_callback($matches) {
		
		$codeblock = $matches[6];
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		$codeblock = preg_replace_callback('/^\n+/',array(&$this, '_doFencedCodeBlocks_newlines'), $codeblock);

		if(preg_match('/\s*`{3,}/', $matches[1])){
			$cb = empty($matches[2]) ? "<pre>\n" : "<pre class=\"brush:$matches[2] highlight:$matches[4] first-line:$matches[3]\" title=\"$matches[5]\">\n"; 
			$cb .= "$codeblock</pre>";
		}elseif(preg_match('/\s*~{3,}/', $matches[1])){
			$less_than_tab = $this->tab_width;
			if(preg_match('/^\s{'.$less_than_tab.','.$less_than_tab.'}/',$codeblock)){
				$codeblock = preg_replace('/^\s{'.$less_than_tab.','.$less_than_tab.'}/m', "", $codeblock);
			}

			if(empty($matches[2])){
				$cb = "<pre>\n";
			}else{
				if(preg_match("/^[0-9]+$/",$matches[2])){
					$cb = "<pre class=\"prettyprint linenums:$matches[2]\">\n";
				}else{
					if(empty($matches[3])){
						$cb = "<pre class=\"prettyprint lang-$matches[2]\">\n";
					}else{
						$cb = "<pre class=\"prettyprint lang-$matches[2] linenums:$matches[3]\">\n";
					}
				}
			}
			$cb .= "$codeblock</pre>";
		}
		return "\n\n".$this->hashBlock($cb)."\n\n";
	}

	function doFencedFigures($text){
		$text = preg_replace_callback('{
			(?:\n|\A)
			# 1: Opening marker
			(
				={3,} # Marker: equal sign.
			)
			
			[ ]?(?:\[([^\]]+)\])?[ ]* \n # Whitespace and newline following marker.
			
			# 3: Content
			(
				(?>
					(?!\1 [ ]?(?:\[([^\]]+)\])?[ ]* \n)	# Not a closing marker.
					.*\n+
				)+
			)
			
			# Closing marker.
			\1 [ ]?(?:\[([^\]]+)\])?[ ]* \n
		}xm', array(&$this, '_doFencedFigures_callback'), $text);		
		
		return $text;	
	}
	
	function _doFencedFigures_callback($matches) {
		# get figcaption
		$topcaption = empty($matches[2]) ? null : $this->runBlockGamut($matches[2]);
		$bottomcaption = empty($matches[5]) ? null : $this->runBlockGamut($matches[5]);
		$figure = $matches[3];
		$figure = $this->runBlockGamut($figure); # recurse

		$figure = preg_replace('/^/m', "  ", $figure);
		# These leading spaces cause problem with <pre> content, 
		# so we need to fix that - reuse blockqoute code to handle this:
		$figure = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', 
			array(&$this, '_doBlockQuotes_callback2'), $figure);
		
		$res = "<figure>";
		if(!empty($topcaption)){
			$res .= "\n<figcaption>$topcaption</figcaption>";
		}
		$res .= "\n$figure\n";
		if(!empty($bottomcaption) && empty($topcaption)){
			$res .= "<figcaption>$bottomcaption</figcaption>";
		}
		$res .= "</figure>";		
		return "\n". $this->hashBlock($res)."\n\n";
	}


	/* to do list add*/
	function doTodoLists($text) {
	#
	# Form HTML todo lists
	#
		$less_than_tab = $this->tab_width - 1;

		# Re-usable patterns to match todo list
		$marker_re  = '-\s\[[\*\sx]\]';

		# Re-usable pattern to match any entire todo list:
		$whole_list_re = '
			(					# $1 = whole list
			  (					# $2
				([ ]{0,'.$less_than_tab.'})	# $3 = number of spaces
				('.$marker_re.')		# $4 = first list item marker
				[ ]+
			  )
			  (?s:.+?)
			  (					# $5
				  \z
				|
				  \n{2,}
				  (?=\S)
			  )
			)
		';

		$text = preg_replace_callback('{
				^
				'.$whole_list_re.'
			}mx',
			array(&$this, '_doTodoLists_callback'), $text);

		return $text;
	}
	function _doTodoLists_callback($matches) {
		$list = $matches[1];

		$list .= "\n";
		$result = $this->processTodoListItems($list);

		$result = $this->hashBlock("<div class=\"todo\">\n" . $result . "</div>");
		return "\n". $result ."\n\n";
	}
	function processTodoListItems($list_str) {
		# Re-usable pattern to match todo list items
		$marker_re  = '-\s\[[\*\sx]\]';

		# trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		$list_str = preg_replace_callback('{
			(\n)?				# leading line = $1
			(^[ ]*)				# leading whitespace = $2
			('.$marker_re.'			# list marker and space = $3
				(?:[ ]+|(?=\n))	# space only required if item is not empty
			)
			((?s:.*?))			# list item text   = $4
			(?:(\n+(?=\n))|\n)		# tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_re.') (?:[ ]+|(?=\n))))
			}xm',
			array(&$this, '_processTodoListItems_callback'), $list_str);
		return $list_str;
	}

	function _processTodoListItems_callback($matches) {
		static $item_id;

		$item_id = (!isset($item_id)?1:$item_id+1);
		$item = $matches[4];
		$leading_line =& $matches[1];
		$leading_space =& $matches[2];
		$marker_space = $matches[3];
		$tailing_blank_line =& $matches[5];

		if ($leading_line || $tailing_blank_line || 
			preg_match('/\n{2,}/', $item))
		{
			# Replace marker with the appropriate whitespace indentation
			$item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
			$item = $this->runBlockGamut($this->outdent($item)."\n");
		}

		$item = $this->doItalicsAndBold($item);
		$item = $this->doImages($item);
		$item = $this->doAnchors($item);
		$item = $this->doIcons($item);

		if (preg_match('/\[[\*x]\]/', $marker_space))
			return '<div class="checkbox">  <label><input type="checkbox" checked />  ' . $item . "</label></div>\n";
		else
			return '<div class="checkbox">  <label><input type="checkbox" />  ' . $item . "</label></div>\n";
	}



	/* to do list add*/
	function doIconLists($text) {
	#
	# Form HTML todo lists
	#
		$less_than_tab = $this->tab_width - 1;

		# Re-usable patterns to match todo list
		$marker_re  = '-\s(\[\(+[a-zA-Z0-9,\-]{2,}\)+\])';

		# Re-usable pattern to match any entire todo list:
		$whole_list_re = '
			(					# $1 = whole list
			  (					# $2
				([ ]{0,'.$less_than_tab.'})	# $3 = number of spaces
				('.$marker_re.')		# $4 = first list item marker
				[ ]+
			  )
			  (?s:.+?)
			  (					# $5
				  \z
				|
				  \n{2,}
				  (?=\S)
			  )
			)
		';

		$text = preg_replace_callback('{
				^
				'.$whole_list_re.'
			}mx',
			array(&$this, '_doIconLists_callback'), $text);

		return $text;
	}
	function _doIconLists_callback($matches) {
		$list = $matches[1];
		
		$list .= "\n";
		$result = $this->processIconListItems($list);

		$result = $this->hashBlock("<ul class=\"fa-ul\">\n" . $result . "</ul>");
		return "\n". $result ."\n\n";
	}
	function processIconListItems($list_str) {
		# Re-usable pattern to match todo list items
		$marker_re  = '-\s(\[\(+[a-zA-Z0-9,\-]{2,}\)+\])';

		# trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		$list_str = preg_replace_callback('{
			(\n)?				# leading line = $1
			(^[ ]*)				# leading whitespace = $2
			('.$marker_re.'			# list marker and space = $3
				(?:[ ]+|(?=\n))	# space only required if item is not empty
			)
			((?s:.*?))			# list item text   = $4
			(?:(\n+(?=\n))|\n)		# tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_re.') (?:[ ]+|(?=\n))))
			}xm',
			array(&$this, '_processIconListItems_callback'), $list_str);
		return $list_str;
	}

	function _processIconListItems_callback($matches) {
		static $item_id;
		
		$item_id = (!isset($item_id)?1:$item_id+1);
		$item = $matches[5];
		$leading_line =& $matches[2];
		$leading_space =& $matches[3];
		$marker_space = $matches[4];
		$tailing_blank_line =& $matches[6];

		$marker_space = preg_replace_callback('/[^)]\)/', function($hit){
			return $hit[0][0].',li)';
		}, $marker_space);

		$marker_space = $this->doIcons($marker_space);

		if ($leading_line || $tailing_blank_line || preg_match('/\n{2,}/', $item)){
			# Replace marker with the appropriate whitespace indentation
			$item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;

			$item = $this->runBlockGamut($this->outdent($item)."\n");
		}

		$item = $this->doItalicsAndBold($item);
		$item = $this->doImages($item);
		$item = $this->doAnchors($item);
		$item = $this->doIcons($item);

		
		return '<li>  ' . $marker_space.$item . "</li>\n";
		

	}

	function doIcons($text) {
		# atx-style headers:
		#	[(normal)]
		#	[((fa-lg))]
		#	[(((fa-2x)))]
		#	[((((fa-3x))))]
		#	[(((((fa-3x)))))]
		#	[((((((fa-4x))))))]
		#
		$open = '[\[:]';
		$close = '[\]:]';
		#	[(flag)]
		# output <i class="fa fa-flag"></i>
		#	:(send):
		# output <span class="glyphicon glyphicon-send"></span>
		$text = $this->runSpanGamut($text);
		$text = preg_replace_callback('#'.$open.'\({1,6}[ ]*(.+?)[ ]*\){1,6}'.$close.'#xm',array(&$this, '_doIcons_callback_atx'), $text);

		return $text;
	}


	function _doIcons_callback_atx($matches) {
		$level = substr_count($matches[0],'(');
		switch ($level) {
			case 1:
				$level = '';
				break;
			case 2:
				$level = ' fa-lg';
				break;
			case 3:
				$level = ' fa-2x';
				break;
			case 4:
				$level = ' fa-3x';
				break;
			case 5:
				$level = ' fa-4x';
				break;
			case 6:
				$level = ' fa-5x';
				break;
		}
		$content = explode(',',$matches[1]);
		$icon = $content[0];


		if(strpos($icon,'|')){
			$icons = explode('|',$icon);
			$class = $icons[0];
			$icon = $icons[1];
		}

		$icon = preg_replace('/(^fa-)|(^glyphicon-)/','',$icon);
		unset($content[0]);
		$option = '';
		foreach ($content as $value) {
			$option .= ' fa-'.$value;
		}
		$icon = $icon.$level.$option;
		switch ($matches[0][0]) {
			case '[':
				if(empty($class)){
					$class = 'fa';
				}
				$block = "<i class='".$class." ".$class."-".$icon."'></i>";
				break;
			case ':':
				if(empty($class)){
					$class = 'glyphicon';
				}
				$block = "<span class='".$class." ".$class."-".$icon."'></span>";
				break;
		}

		return ' '.$this->hashBlock($block).' ';
	}
}
?>
