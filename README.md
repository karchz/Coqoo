Coqoo 
====

[Coqoo](http://coqoo.net/) is NEXT Markdown CMS .  
This is Revolution Forked Pico . 

CoqooはMarkdown CMS Pico を速度的、機能的に魔改造したCMSです。  
Coqooへの投稿はマークダウンファイルを配置することで行います。  
ログインや投稿編集の画面、さらにはデータベースすら存在しません。  
好きなエディタで記事を書き、FTPなどでCoqooの`content`ディレクトリに配置するだけです。  

マークダウンファイルの先頭にはメタ情報を記述します。   
例えば以下の様なものです。  

```
/*
	Title:クイックスタート	
	Description: Coqooの設定ファイルについて解説します。
	Keywards: Markdown CMS,虚空,Coqoo,Markdown ブログ,PHP CMS
	Status:publish
	Category:Startup
	Date:2014/02/02
	Tags:Coqoo,Config,Markdown
*/
```

通常はファイルの頭に記述します。  
Mouなどのリアルタイムプレビュー付きエディタをお使いの場合はTabを挿入するとプレビューが綺麗になります。  
`/*　*/`の外には記事の本文を記述します。 

Coqooの公式サイトは[コチラ](http://coqoo.net/)です。


Coqooは現存するPHPマークダウンパーサの中でも最も先進的で便利なパーサを内蔵しています。  
旧来型のマークダウンパーサと違い以下のプロジェクトを思い通りに操る事ができます。

- Google Code Prettify
- Syntax Highlighter
- Bootstrap glyphicons
- Font Awesome & this options
- Custom Icons
- Todo List
- Icon List

[こちらのデモ](http://demo.geeks-dev.com/markdown_e2_stylish/demo/)でお試し頂けます。  

PHP5.5 で動作確認済み  
PHP5.4以下は`composer.json`に"rhumsaa/array_column": "~1.1"を追加すると良いかもしれません。  
それができない場合は`plugin`内の`Coqoo-Logs-Plugin`を削除してください。  
削除した場合は人気記事が取得できなくなります。  

