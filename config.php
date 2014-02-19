<?php 

//Coqoo CMS 設定ファイル

/*本体の設定*/
$config['base_url']					= 'http://localhost/coqoo';				//サイトルートのURL
$config['site_title']				= 'Markdown CMS Coqoo ―虚空―';		//サイトのタイトル
// $config['theme']					= 'default';						//テーマディレクトリ名
$config['template_extension']		= '.html.twig';						//テンプレートファイルの拡張子
$config['excerpt_length'] 			= 180;								//省略表示時の文字数
$config['page_limit']				= 5;								//ページングする時の1ページの表示量
// $config['page_indicator']		= 'page';							//ページングする時のクエリストリング
$config['always_paging']			= true;							//常にページングするかどうか
$config['category_prefix']			= '';								//カテゴリーの接頭辞
$config['tag_prefix']				= '# ';								//タグの接頭辞
$config['ignore_status']			= array('draft');					//除外ステータス
$config['skip_dir']					= array('pages');					//一覧取得時にスキップさせるディレクトリ(カンマ区切り複数可)
$config['skip_list_status']			= 'skip';							//一覧取得時にスキップさせるステータス
$config['ignore_sitemap']			= array();							//サイトマップ取得時にスキップさせるディレクトリ
$config['images_dir']				= 'images';							//画像ファイルの設置ディレクトリ
$config['autoset_thumbnail']		= array('category','png',true);		//サムネイル未設定時に自動補完するサムネイルの設定
																		//('category' or 'logo',拡張子,true or false)

// $config['pages_order_by'] 			= 'date';						//date or alpha
// $config['pages_order']				= 'desc';						//desc or asc
// $config['date_format'] = 'jS M Y';									//日付のフォーマット

// $config['twig_config'] = array(										// Twig settings
// 	'cache' => 'lib/cache',												// To enable Twig caching change this to CACHE_DIR
// 	'autoescape' => false,												// Autoescape Twig vars
// 	'debug' => false													// Enable Twig debug
// );


/*coqoo-logs-plugin*/
$config['timezone']					= 'Asia/Tokyo';						//タイムゾーン
$config['hour_limit']				= 90;								//1時間あたりの同一ページアクセス許可数
$config['rank_mode']				= 'm';								//ランキングの集計期間 y or m or d
$config['rank_limit']				= 20;								//ランキングの出力数
$config['redirect_url']				= $config['base_url'];				//アクセス許可数を超えた場合のリダイレクト先

/*テーマオプション*/
$config['navbar_fix']				= true;
$config['header_title']				= "Coqoo";

// 拡張設定を追加する事もできます

//$config['custom_setting'] = 'Hello'; 	 
// テンプレート内で {{ config.custom_setting }} 
// このように記述するとHelloと出力されます。