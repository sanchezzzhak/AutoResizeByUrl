@TODO  Ресайз картинок с сохранением в фаил
@# Основная идея сохранить отресайзеную картинку в кэш ngnix, тем самым не копить картинки пачками.
<br>
\# Конфиг для Ngnix {
location ~ ^/storage/(.*)/(.*)_(.*)\.(jpg|jpeg|gif|png|bmp)$ {
   \# proxy_intercept_errors      on; <br>
   \# proxy_cache                 image-resize;<br>
   \# proxy_cache_key             "$host$document_uri";<br>
   \# proxy_cache_valid           200 1d;<br>
   \# proxy_cache_valid           any 1m;<br>
  if (!-e $request_filename){<br>
		rewrite ^(.*)$ /storage/resize.php;<br>
		proxy_pass http://$host$uri;<br> 
	  }<br>
	}<br>
\# } Конфиг для Ngnix<br>
<br>
<br>
\# Конфиг для апача если используется как бекенд или основной сервер.<br>
\# Файл сохранить в деректорию рядом с resize.php<br>
\<IfModule mod_rewrite.c\><br>
  Options +FollowSymLinks<br>
  RewriteEngine On<br>
  RewriteCond %{REQUEST_FILENAME} !-f<br>
  RewriteRule ^(.*)$ resize.php [L]<br>
\<\/IfModule\><br>
<br>
\# Как это работает <br>
\# В теле документа создаем картинку img src="/storage/photo/92/fb/68/14/35/322_92fb68143595f5677cfa6632fb6d29cb.jpg"<br>
\# Где 322_ это сигнал для скрипта что картинка по Ширине[=]Высоте если файла не существует.<br>
<br>
\# Если задано h322_ то по высоте аналогично и по ширине W322_<br>
\# Cписок настроек хранится в самом скрепти в виде массива.<br>
