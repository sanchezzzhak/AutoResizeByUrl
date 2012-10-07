<?php
/*
* @TODO  Ресайз картинок с сохранением в фаил
* 
* Автор Тutik Alexsandr
* Мыло  sanchezzzhak@ya.ru
* Пос.Редактирование  02.06.2012
* github  https://github.com/sanchezzzhak/AutoResizeByUrl
* License: Использование бесплатно с сохранением выше указаного копирайта включая лицензию.
* 
* 
* Инструкция как это все работает:
* Вы запрашиваете не существующий файл через браузер пример /storage/80_1.jpg  htaccess отлавливает событие на отсуствие файла на сервере
* и пересылает запрос на скрипт resize.php. 
* Далее скрипт ищит наличия файла оригинала в папке как /storage/1.jpg  
* Если фаил найден то происходит ресайз с дальнейщим сохранением в туже папку но уже с новым именим, которое было запрошено через браузер
* 
* Доступные парамеры в названии файла   _ - Разделитель
* Первый символ h или w это означает ресайз по: h - Высоте,  w - Ширине этот параметр не обезателен 
* 		в случае его отсутствия, этого параметра размеры будут браться из соответствия по карте размеров.
* Двух-Трех значное число размера - это размер изображения который ищется в карте соответствия размеров.
* _ - Это разделитель, после этого знака идет оригинальное название файла.
* 
* Краткие примеры:
*  /storage/80_1.jpg  ресайз происходит по карте размеров 80х60
*  /storage/h80_1.jpg ресайз происходит по карте размеров по высоте 80
*  /storage/w80_1.jpg ресайз происходит по карте размеров по ширине 80
* 
*/



ini_set('memory_limit', '80M');

/**
 * Карта размеров 
 **/
$arrSize = array(
//	'40' => array('40','30'),	
	'60' => array('60','45'),
	'80' => array('80','60'),
	'138'=> array('138','104'),
	'168'=> array('168','126'),
	'180'=> array('180','135'),
	'245'=> array('245','184'),
	'257'=> array('257','193'),
	'322'=> array('322','242'),
	'360'=> array('360','270'),
	'384'=> array('384','288'),
	'400'=> array('400','300'),
	'400'=> array('420','315'),
	'600'=> array('600','450'),
);



$path      = pathinfo($_SERVER['REQUEST_URI']);
$preg_rule = '#^(h|w|p){0,1}?([0-9]){2,4}_#ixs';
preg_match($preg_rule ,$path['filename'] , $matcher);
$is_error = true;

if(count($matcher)>0):
	$is_error = false;
	$size = str_replace(array('w','h','_'),'', strtolower($matcher[0]) );
	$type = $matcher[1];

	// проверка если сапоставление размеров в карте
	if(!isset($arrSize[$size]) && $type!='p') $is_error = true;
	
	// если ошибок нет то ресайзим
	if(!$is_error):
	
		$pathInOut  = $_SERVER['DOCUMENT_ROOT'] . $path['dirname'] . "/";
		$pathToFile = $pathInOut . str_replace( $matcher[0], '', $path['filename'] ) . "." .$path['extension'];
		$pathToSave = $pathInOut . $path['filename'] . "." .$path['extension'];
	
		//pre( $pathToSave );	
	
		switch($type):			
		
			// в процентном соотношении * не доделано
			case 'p':
				$is_error = true;
				/*
				if( ( list($w,$h) = getimagesize($pathToFile) )==false):
					$is_error = true;
				else:
					$w = round($w * (float)"0.".$size); 
					$h = round($h * (float)"0.".$size); 	
					if(!resize($pathToFile, $pathToSave, $w , $h , 100 ,true) ) $is_error = true;	
				endif;*/
			break;	
		
			
			// ресайз по высоте
			case 'h':
				if(!resize($pathToFile, $pathToSave, false, $size , 100 , true) ) $is_error = true	;	
			break;
			// ресайз по ширине
			case 'w':
				if(!resize($pathToFile, $pathToSave, $size, false , 100 , true) ) $is_error = true;	
			break;
			// ресайз по отведенным рамкам
			default:
				if(!resize($pathToFile, $pathToSave, $arrSize[$size][0],  $arrSize[$size][1] , 100, true) ) $is_error = true;
			break;
		endswitch;
	endif;
	
endif;	

// если ошибка то говорим, что нет такого файла
if($is_error):
	header("HTTP/1.0 404 Not Found"); 
	header("HTTP/1.1 404 Not Found"); 
	header("Status: 404 Not Found"); 
	die(); 
endif;

	// header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime(__FILE__) ).' GMT', true, 304);

	/************ ************ ************
	 * 				Функции               *
	 ************ ************ ************/

	/**
	* TODO Изменение размеров изображения
	*
	* $width, $height: Оба принимаемых параметра необязательны.
	* 	Если переданы и ширина и высота, изображение ужмётся в рамки.
	* 	Если передана лишь ширина - изображение сжимается по ней (аналогично и с высотой)
	* $quality Качество
	* $dispay  Выводить ресайз на экран
	* @return true / false 
	*/
	function resize($src , $desc  ,  $width = false, $height = false , $quality =100 , $dispay = true){
		if (!file_exists($src)) return false;
			$size = getimagesize($src);	
		if ($size === false) return false;
			$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
			$icfunc = "imagecreatefrom" . $format;
		if (!function_exists($icfunc)) return false;
		
		$info = array();
		
		
		$srcWidth = $size[0]; $srcHeight = $size[1]; // исходный размер 
		
		/**** Если задали высоту и ширину ****/
		if(is_numeric($width) && is_numeric($height) && $width > 0 && $height > 0){
			/** Определяем размеры нового изображения при вписывании его в рамки */ 
			if($srcWidth  <= $width && $srcHeight <= $height){
				$newSize = array($srcWidth , $srcHeight);
			}elseif($srcWidth / $width > $srcHeight / $height){
				$newSize[0] = $width;
				$newSize[1] = round($srcHeight * $width / $srcWidth);
			}else{
				$newSize[1] = $height;
				$newSize[0] = round($srcWidth * $height / $srcHeight);
			}
		/**** Если указали только ширину ****/
		}elseif(is_numeric($width) && $width > 0){
			/** Определяем размеры нового изображения при сжатии по ширине */
			if($width >= $srcWidth ){ 
				$newSize = array($srcWidth, $srcHeight);
			}else{
				$newSize[0] = $width;
				$newSize[1] = round($srcHeight * $width / $srcWidth);
			}
		/**** Если указали только высоту ****/
		}elseif(is_numeric($height) && $height > 0){
			/** Определяем размеры нового изображения при сжатии по высоте */
			if($height >= $srcHeight){ 
				$newSize = array($srcWidth, $srcHeight);
			}else{
				$newSize[1] = $height;
				$newSize[0] = round($srcWidth * $height / $srcHeight);
			}
		}else $newSize = array($srcWidth, $srcHeight);
		
		$src = $icfunc($src);
		$newImage = imagecreatetruecolor($newSize[0], $newSize[1]);
		imagecopyresampled($newImage, $src , 0, 0, 0, 0, $newSize[0], $newSize[1], $srcWidth, $srcHeight);
		$icfunc2  = "image" .$format; // ilove lamda функция
		//fix 1
		ob_start();
		if($format=='jpeg'){ imagejpeg($newImage, null , $quality); 
			//readfile($dest);	//этот метод работает если никс есть или если быстрый hdd винт
		}else{	$icfunc2( $newImage, null);	}
		
		$buffer = ob_get_clean();
		imagedestroy($src);
		imagedestroy($newImage);
		
		$fp = fopen ($desc,'w');
		fwrite ($fp, $buffer);
		fclose ($fp);
		
		if($dispay==true && !Empty($desc)){
			header("Content-Type: image/".$format );
			echo $buffer;
		}
		return true;
		
	}



?>