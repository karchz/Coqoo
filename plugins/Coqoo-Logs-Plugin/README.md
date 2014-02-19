#Coqoo Logs Plugin

Provides tags functionality for [Coqoo](http://coqoo.net).

Adds `/tag/` url, that allows displaying pages with only given tag (e.g. `/tags/pico`), index page displays only pages with tags, which gives distinction between tagged pages (which can be use as blog posts), and untagged (which can be used as *static* pages).

To see **Pico Tags** in action you can visit [my website](http://treesmovethemost.com)!

##Installation

Copy `Coqoo_logs.php` to `plugins` folder inside pico installation.

##Usage

Add `Tags` filed to post meta:

```

/*
Title: Pico Tags Example
Date: 2013-08-03
Tags: php,pico,plugin
*/

```

Setup theme `index.html`, example:

```html

	{% if rank %}
	<div class="bs-sidenav">
		<h4 class="text-center">Popular Posts</h4>
		<hr />
		<ul class="nav">
		{% for item in rank|slice(0, 5) %}
			<li>
				<div>
					<div class="col-xs-4 text-center">
						{% if item.thumbnail %}
						<a href="{{item.url}}" class="thumbnail">
							<img class="img-responsive" src="{{item.thumbnail}}">
						</a>
						{% endif %}
					</div>
					<div class="col-xs-8">	
						<span>Date:&nbsp;{{item.date|split(' ')[0]}}</span><br />
						<span>Category:&nbsp;{{item.category}}</span>
						<a href="{{item.url}}" class="">
							<p>{{item.title}}</p>
						</a>
					</div>
					<div class="clearfix"></div>
					<div class="clearfix"></div>
				</div>
				<hr class="clearfix" />
			</li>
		{% endfor %}
		</ul>
	</div>
	{% endif %}
	
```
