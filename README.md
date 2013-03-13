@TODO  Ресайз картинок с сохранением в фаил
# Основная идея сохранить отресайзеную картинку в кэш ngnix, тем самым не копить картинки пачками.

# Конфиг для Ngnix {
location ~ ^/storage/(.*)/(.*)_(.*)\.(jpg|jpeg|gif|png|bmp)$ {
   # proxy_intercept_errors      on;
   # proxy_cache                 image-resize;
   # proxy_cache_key             "$host$document_uri";
   # proxy_cache_valid           200 1d;
   # proxy_cache_valid           any 1m;
  if (!-e $request_filename){
		rewrite ^(.*)$ /storage/resize.php;
		proxy_pass http://$host$uri; 
	  }
	}
# } Конфиг для Ngnix


# Конфиг для апача если используется как бекенд или основной сервер.
# Файл сохранить в деректорию рядом с resize.php
<IfModule mod_rewrite.c>
  Options +FollowSymLinks
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ resize.php [L]
</IfModule>

# Как это работает 
# В теле документа создаем картинку <img src="/storage/photo/92/fb/68/14/35/322_92fb68143595f5677cfa6632fb6d29cb.jpg">
# Где 322_ это сигнал для скрипта что картинка по Ширине[=]Высоте если файла не существует.

# Если задано h322_ то по высоте аналогично и по ширине W322_
# Cписок настроек хранится в самом скрепти в виде массива.


