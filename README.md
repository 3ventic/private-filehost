# private-filehost
The code behind i.3v.fi

# Nginx server block configuration

(Apache/other webserver equivalent should do too, perhaps even in .htaccess. Feel free to create a pull request if you create one)

```
	
	location ~ ^/raw(/[^/]*)?$ {
		rewrite ^/raw(/[^/]*)?$ $1;
		root /home/filehost;
		location ~ \.php$ {
			return 404;
		}
		try_files $uri =404;
	}
	
	rewrite ^/([^/]+)\.((?:(?!php).)+)$ /index.php?file=$1&type=$2 break;
	
	location ~ \.php$ {
		try_files $uri =403;
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		include fastcgi_params;
	}

```

# 3rd party requirements

- [highlight.js](http://highlightjs.org/) (uses obsidian style)
- [markdown-js](https://github.com/evilstreak/markdown-js/releases)

# Optional cronjob

Generates mp4 videos for all gif-files uploaded and vice-versa, checks every 2 minutes.
This requires ffmpeg and imagemagick installed. I had to build ffmpeg from source myself to get it to work.

```
*/2 * * * * /path/to/convertgif.sh > /dev/null
```
