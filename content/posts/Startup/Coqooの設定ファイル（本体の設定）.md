	/*
		Title:Coqooの設定ファイル（本体の設定）
		Description: Coqooの設定ファイルについて解説します。ここではCoqooのコアに関する設定を行います。
		Keywards: Markdown CMS,虚空,Coqoo,Markdown ブログ,PHP CMS
		Thumbnail: logo.png
		Status:publish
		Category:Startup
		Date:2014/02/02 18:42
		Tags:Coqoo,Config,Markdown
	*/

[クイックスタート]:http://coqoo.net/posts/Startup/クイックスタート "クイックスタート"  
[twig]:http://twig.sensiolabs.org/
[coqoo_file]:http://coqoo.net/images/ss/coqooのファイル構成.png
[拡張設定]:http://coqoo.net/posts/Startup/Coqooの設定ファイル（拡張設定） "Coqooの設定ファイル（拡張設定）" 

Coqooの設置は完了しましたか?  
ここからはCoqooの設定についてさらに深く解説していきます。  
適切に設定する事によってさらに思い通りにブログやWebサイトを運用する事が可能です。  


## 設定のおさらい

[クイックスタート]でも触れましたがCoqooは`config.php`ですべての設定を行います。  
[クイックスタート]で省略した箇所も含めて再度config.phpを見てみましょう。  


	```php,1,[0],config.phpの中身
	<?php 
	
	//Coqoo CMS 設定ファイル
	
	/*本体の設定*/
	$config['base_url']					= 'http://coqoo.net';				//サイトルートのURL
	$config['site_title']				= 'Markdown CMS Coqoo ―虚空―';		//サイトのタイトル
	// $config['theme']					= 'default';						//テーマディレクトリ名
	$config['template_extension']		= '.html.twig';						//テンプレートファイルの拡張子
	$config['excerpt_length'] 			= 180;								//省略表示時の文字数
	$config['page_limit']				= 5;								//ページングする時の1ページの表示量
	// $config['page_indicator']		= 'page';							//ページングする時のクエリストリング
	// $config['always_paging']			= false;							//常にページングするかどうか
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

	```


`$config['base_url']`と`$config['site_title']`は[クイックスタート]で設定したので省略します。  

## テーマの設定

Coqooにおいて、テーマとはいくつかのテンプレートファイル・css・javascript・image等が含まれたディレクトリを指します。  
テンプレートファイルは記事の一覧ページや、単一の記事ページなどをどのように出力するかが記述されています。  
テンプレートは[twig]というとても簡単なテンプレートエンジンの記述法が使用できます。

	```php,8,[0],テーマに関する設定				
	// $config['theme']					= 'default';		//テーマディレクトリ名
	$config['template_extension']       = '.html.twig';		//テンプレートファイルの拡張子
	```
### theme

テーマの設定は標準で`default`というテーマが選択されます。  
Coqooのシステム内を覗いてみてください。  

![Coqooのファイル群][coqoo_file]   

`themes`というディレクトリの中に`default`ディレクトリがあります。  
この`default`ディレクトリがテーマです。  
別のテーマを追加する場合は`theme`ディレクトリに設置し、`config.php`を以下のように書き換えます。
```php,8,[8],テーマに関する設定 		
	$config['theme']					= 'テーマのディレクトリ名';		//テーマディレクトリ名
	$config['template_extension']       = '.html.twig';		//テンプレートファイルの拡張子
```

### template_extension
`$config['template_extension']`は設置、または作成したテーマ内のテンプレートファイルに合わせて下さい。
テンプレートファイルの拡張子が`.html`ならば、以下のように。
```php,8,[9],テーマに関する設定  		
	$config['theme']					= 'テーマのディレクトリ名';		//テーマディレクトリ名
	$config['template_extension']       = '.html';		//テンプレートファイルの拡張子
```

`.twig`ならば以下のようにしましょう。

	```php,8,[9],テーマに関する設定
	$config['theme']					= 'テーマのディレクトリ名';		//テーマディレクトリ名
	$config['template_extension']       = '.twig';		//テンプレートファイルの拡張子
	```


## 省略表示やのページング動作
少しレベルアップして細かな設定を行っていきます。  
以下の項目はブログやWebサイトの構造によっては重要な設定項目です。  


	```php,10,[0],省略表示やページングに関する設定
	$config['excerpt_length']           = 180;    //省略表示時の文字数
	$config['page_limit']               = 5;      //ページングする時の1ページの表示量
	$config['page_indicator']        	= 'page'; //ページングする時のクエリストリング
	$config['always_paging']         	= false;  //常にページングするかどうか
	```
	
### excerpt_length

`$config['excerpt_length']`は記事の一覧表示を行う際の省略文字数となります。   
twigテンプレート側でも強引に文字数を制限できますが、こちらで設定した方が負荷が少なくなります。   

### page_limit

`$config['page_limit']`は1ページの表示記事数です。  
お好みで設定してください。  

### page_indicator

`$config['page_indicator']`はページング時にページ番号を受け取るURL引数となります。
標準だと任意のページへのアクセス先は`http://coqoo.net/tag/pico?page=ページ番号`となります。  
以下の様な設定にすると、`http://coqoo.net/tag/pico?p=ページ番号`となります。  
	```php,12,[0],ページインジケータを変更
	$config['page_indicator']        	= 'p';
	```
	
### always_pageing

`$config['always_paging']`は`true`にすると1ページ目のURL引数が不要になり強制的にページングが行われます。  
`false`にすると`?page=ページ番号`といった引数を与えない限りページングが実行されなくなります。  
なお`page`の部分は上述した`$config['page_indicator']`によって変動します。  

## カテゴリーやタグ
カテゴリー名やタグ名には接頭辞をつけることができます。  
これは単純にtwig側でカテゴリー名やタグ名を表示する際に働きます。  
どちらにも接頭辞を付けず、twigテンプレートで直接表示させても構いません。  


### category_prefix

カテゴリーの接頭辞を$にするには以下のように設定します。

	```php,14,[14],カテゴリーの設定
	$config['category_prefix']          = '$';	//カテゴリーの接頭辞
	```
### tag_prefix	

タグの接頭辞を@にするには以下のように設定します。
	```php,15,[15],タグの設定
	$config['tag_prefix']               = '@';	//タグの接頭辞
	```
	
## 記事の下書きやスキップ

Coqooでは除外ステータスや一覧表示から除外するフォルダなどを設定することができます。  
この動作を利用して、記事の下書きにするステータスを作成したり、WordPressで言うところの固定ページを作成したりできます。

### ignore_status
初期設定だと記事のメタ情報の`Status`を`draft`にするとアクセス不可能になります。  
	
	```plain,1,[5],ステータスを書き込む位置
	/*
		Title:記事のタイトル	
		Description: 記事の説明
		Keywards: Coqoo,Markdown,ブログ,PHP
		Status:draft
		Category:カテゴリー1
		Thumbnail:photo.jpg
		Date:2014/02/02
		Tags:Coqoo,Config,meta
	*/
	```

この状態は一覧表示にも現れませんし、記事そのもののURLへ直接アクセスしても404エラーを返します。  
これを利用して記事の下書きを行うことが可能になります。  
除外ステータスは複数設定することも可能です。  
この除外ステータスを追加・編集するには以下の項目を編集します。  
	```php,16,[16],除外ステータスの設定
	$config['ignore_status']			= array('draft','out');	 //除外ステータス
	```

### skip_dir

`content`ディレクトリ内には自由にmdファイルを配置できるという事は[クイックスタート]でも触れましたが、この中の特定ディレクトリ内の記事を一覧取得時に表示しないように設定することも可能です。  
ただし、前述した`ignore_status`とは違い記事そのものへのアクセスは可能なため、URLへの直接アクセスは拒否されません。  
この設定を利用することによって固定ページを作成します。  
標準では以下のように設定されています。  
 
	```php,17,[17],スキップディレクトリ
	$config['skip_dir']					= array('pages');	//一覧取得時にスキップさせるディレクトリ(カンマ区切り複数可)
	```

この設定も複数指定することが可能です。 
現在の状態だと`content/pages`以下のファイルは一覧表示内に現れません。 

### skip_list_status

この設定は`ignore_status`と似ています。  
違うのは記事自体へのアクセスが可能だということです。  
`skip_dir`と同じようにこのステータスを利用して固定ページを作成することも可能です。  

	```php,18,[18],スキップステータスの設定
	$config['skip_list_status']			= 'skip';			//一覧取得時にスキップさせるステータス
	```

ステータスはの設定箇所は[ignore_status](#ignore_status)を参照してください。  

### ignore_sitemap

Coqooはsitmap.xmlを出力しています。  
sitemapから除外するディレクトリは以下の設定で行います。

	```php,19,[19],サイトマップの除外ディレクトリ設定
	$config['ignore_sitemap']			= array('nomap');			//サイトマップ取得時にスキップさせるディレクトリ
	```

この設定も複数指定が可能です。
上記のような設定を行うと`content/nomap`内の記事がsitemap.xml出力時にのみスキップされます。  
sitmap.xmlは

	[base_urlで指定したアドレス]/sitmap.xml

でアクセス可能です。  


## 画像に関する設定
	
Coqooでは記事のアイキャッチなどに使用する画像を指定しなかった際にカテゴリーによって自動的に補完をサポートする機能が実装されています。  
ただしこの機能は2つの設定を正しく行った状態でないと動作しません。  

### images_dir

この設定は記事に使用する画像ファイルを格納するディレクトリの指定です。  

	```php,20,[0],画像を格納するディレクトリ
	$config['images_dir']               = 'images';		//画像ファイルの設置ディレクトリ
	```

が、実際には画像ファイルを格納するディレクトリに制限はありません。  
この設定は自動補完用のパスを宣言しているだけです。  
下記の`Thumbnail`項目を御覧ください。  

	```plain,1,[7],サムネイルの指定位置
	/*
		Title:記事のタイトル	
		Description: 記事の説明
		Keywards: Coqoo,Markdown,ブログ,PHP
		Status:draft
		Category:カテゴリー1
		Thumbnail:photo.jpg
		Date:2014/02/02
		Tags:Coqoo,Config,meta
	*/
	```

`Thumbnail`の項目が俗にいうアイキャッチ画像となります。  
この項目は通常`http://example.com/photo.jpg`のようにフルパスで指定するのですが、  
何度も何度も記述するのは大変面倒です。  
少なくとも自分のサイト内の画像くらいは省略記述したいことでしょう。  
`images_dir`の設定を行うことで、フルパス指定されなかった`Thumbnail`項目は、

	[base_urlで指定したアドレス]/[image_dirで指定したディレクトリ]/[Thumbnailに指定した画像ファイル名]

へと置換されます。  
`Thumbnail`は`smartphone/iphone.png`のようにディレクトリ名から記述することも可能です。  
また、`Thumbnail`に`none`,`false`,`no`,`blank`,`space`などと記述すると`Thumbnail`の自動補完は行われず、テンプレート内で`Thumbnail`の値を受け取れなくすることも、それを利用して出力されるHTMLを変動させる事も可能です。

### autoset_thumbnail

たまにはアイキャッチ画像を探すのをサボりたい時だってあります。  
そんな方は事前に`autoset_thumbnail`を設定しておくことをオススメします。  
この設定は少し独特の動きとなりますので注意しましょう。  
以下を御覧ください。  
	
	```php,21,[0],例1)サムネイル自動補完の設定
	$config['autoset_thumbnail']        = array('category','png',true);		//サムネイル未設定時に自動補完するサムネイルの設定
																			//('category' or 'logo',拡張子,true or false)
	```	
配列の1つ目に`category`、配列の2つ目に`png`と指定されています。  
こうしておくとカテゴリー名と同じファイル名の`.png`ファイルを前述した`images_dir`の直下より探しだし、発見できればそのファイルをサムネイルとして表示します。  
そして配列の3つ目に`true`と設定されているのに注目してください。  
仮にカテゴリー名と同一の`.png`ファイルが`images_dir`直下に存在しなかった場合、`images_dir`直下の`logo.png`というファイルを探し出しサムネイルとして表示します。  
配列の3つ目に`false`とすると`logo.png`ファイルを探しません。  

配列の1つ目は`category`の他に`logo`が指定できます。  
今度は以下の様な設定を施してみます。  

	```php,21,[0],例2)サムネイル自動補完の設定
	$config['autoset_thumbnail']        = array('logo','jpg');				//サムネイル未設定時に自動補完するサムネイルの設定
																			//('category' or 'logo',拡張子,true or false)
	```	

この設定の時の動作は、`images_dir`内の`logo.jpg`を探し出しサムネイルとして表示します。
配列の1つ目が`logo`の場合は3つ目の`true`or`false`の指定を行う必要はありません。

## 記事の並び順
	
極めて簡素な機能でしかありませんが、Coqooもデフォルトで出力される記事の順番を変更することができます。  
[twig]テンプレート内でソートし直すことも可能です。  
複雑なソートは[twig]テンプレート内で実装してみて下さい。  

### pages_order_by

記事の順番の基順となるデータを選択します。
選択できるのは`date`と`alpha`です。  

	```php,22,[0],記事の並びの基準
	$config['pages_order_by'] 			= 'date';	/date or alpha
	```

### pages_order
`acs`(昇順)と`desc`(降順)が選択できます。

	```php,23,[0],基準によって並べ替える順番
	$config['pages_order']				= 'desc';	//desc or asc
	```

## 日付フォーマット

フォーマットされた日付を出力することもできます。

### date_format

	```php,24,[0],日付フォーマットの設定箇所
	$config['date_format'] = 'jS M Y';		//日付のフォーマット
	```
以下の様な記述をテンプレート内に行うことで整形済みの日付を出力できます。  

	{{ meta.date_formatted }}

これも[twig]テンプレート内で整形することが可能です。  

## twigの設定
このCMSは[twig]というテンプレートエンジンを利用しています。
[twig]の設定も同じように`config.php`で行います。  

### twig_config

設定できる項目は`cache`と`autoescape`と`debug`の3つです。  
`cache`はキャッシュファイルを貯めこむディレクトリへのパスを記述します。  
`autoescape`は値が[twig]テンプレートに嵌め込まれる際に値のエスケープを行うかどうかです。  
`debug`はデバッグモードのON/OFFです。  

通常は以下の様な設定で良いかと思います。  
ただし、テンプレートの開発中などは`twig_config`の設定項目をコメントアウトしておいた方が良いかもしれません。

	```php,25,[0],twigの設定
	$config['twig_config'] = array(			// Twig settings
		'cache' => 'lib/cache',					// To enable Twig caching change this to CACHE_DIR
		'autoescape' => false,					// Autoescape Twig vars
		'debug' => false						// Enable Twig debug
	);
	```


## 拡張設定について

Coqooで使用できる設定項目はこれだけではありません。  
例えば以下の様な拡張的な設定項目もあります。  

	```php,34,[0],config.phpの中身
	/*coqoo-logs-plugin*/
	$config['timezone']					= 'asia/tokyo';						//タイムゾーン
	$config['hour_limit']				= 30;								//1時間あたりの同一ページアクセス許可数
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

	```

これらの設定の記述方法は[拡張設定]を御覧ください。
