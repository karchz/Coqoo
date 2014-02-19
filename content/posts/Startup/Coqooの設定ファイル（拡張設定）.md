	/*
		Title:Coqooの設定ファイル（拡張設定）
		Description: Coqooの設定ファイルについて解説します。ここでは主にプラグイン用の設定やテーマ用の設定に関して解説します。
		Keywards: Markdown CMS,虚空,Coqoo,Markdown ブログ,PHP CMS
		Thumbnail: logo.png
		Status:publish
		Category:Startup
		Date:2014/02/02 19:42
		Tags:Coqoo,Config,Markdown
	*/

[クイックスタート]:http://coqoo.net/posts/Startup/クイックスタート "クイックスタート"  
[本体の設定]:http://coqoo.net/posts/Startup/Coqooの設定ファイル（本体設定） "Coqooの設定ファイル（本体の設定）"  
[twig]:http://twig.sensiolabs.org/
[coqoo_file]:http://coqoo.net/images/ss/coqooのファイル構成.png
[拡張設定]:http://coqoo.net/posts/Startup/Coqooの設定ファイル（拡張設定） "Coqooの設定ファイル（拡張設定）" 

Coqooはプラグインやテンプレート内で自由に使用できる設定値をも`config.php`に記述できます。  
これを**拡張設定**と呼称します。  
ここではその拡張設定の記述方法とテンプレート内での使用方法を解説します。  


## 設定のおさらい

[本体の設定]で解説を行わなかった箇所がまさに拡張設定です。  
初期状態では同梱されている`Coqoo-Logs-Plugin`と`default`テーマの設定が記述されています。　　


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
  

## プラグインの設定について

`Coqoo-Logs-Plugin`の設定を例に上げます。  

	```php,34,[0],config.phpの中身
	/*coqoo-logs-plugin*/
	$config['timezone']					= 'asia/tokyo';						//タイムゾーン
	$config['hour_limit']				= 30;								//1時間あたりの同一ページアクセス許可数
	$config['rank_mode']				= 'm';								//ランキングの集計期間 y or m or d
	$config['rank_limit']				= 20;								//ランキングの出力数
	$config['redirect_url']				= $config['base_url'];				//アクセス許可数を超えた場合のリダイレクト先
	```

`Coqoo-Logs-Plugin`プラグインはアクセスのロギングを行って、ランキングを出力したり、悪意のある膨大なアクセスを拒否するプラグインです。  
上記の様にこのプラグインは以下の5つの設定項目がああります。  

- timezone
- hour_limit
- rank_mode
- rank_limit
- redirect_url

設定名称はプラグイン開発者が自由に決める事ができるので、その設定名称がお互いにコンフリクトしないよう留意が必要です。  
プラグインの作成方法は`Coqoo-Logs-Plugin`を参考にしてください。  
また、プラグインの使用方法等はプラグイン内のREADMEなどに記述するようにして下さい。  


## テーマの設定について

テーマ内では、ユーザーのニーズに応える為に複数のオプションを設定する事が可能です。  
これらのテーマオプションはテンプレート内に`{{ config.オプション名 }} `と記述すると出力することができます。  

	```php,41,[0],config.phpの中身
	
	/*テーマオプション*/
	$config['navbar_fix']				= true;
	$config['header_title']				= "Coqoo";
	
	// 拡張設定を追加する事もできます
	
	//$config['custom_setting'] = 'Hello'; 	 
	// テンプレート内で {{ config.custom_setting }} 
	// このように記述するとHelloと出力されます。
	

	```

