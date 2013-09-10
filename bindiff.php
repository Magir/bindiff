<?
	echo 'Binary diff v.0.1 by Magir.'."\n";
	if($argc<3){
		echo 'Usage: '.$argv[0].' <file1> <file2>'."\n"."\n";
		die;
	}
	
	$file1=$argv[1];
	$file2=$argv[2];

	$f1=fopen($file1,'rb');
	$f2=fopen($file2,'rb');

	$b1=fread($f1,filesize($file1));
	$b2=fread($f2,filesize($file2));

	fclose($f1);
	fclose($f2);

	define('forward_search_length',1024);
	define('backward_search_length',1024);
	define('block_size',16);
	define('debug',0);

	if (debug==1) echo 'b1: '.$b1."\n";
	if (debug==1) echo 'b2: '.$b2."\n";

	echo 'Comparing '.$file1.' ['.filesize($file1).' bytes] to '.$file2.' ['.filesize($file2).' bytes].'."\n";
	if (filesize($file1)!=filesize($file2)){
		echo 'Warning! Different filesize!'."\n";
	}
	echo 'Forward deep:'.forward_search_length."\n";
	echo 'Backward deep:'.backward_search_length."\n";
	echo 'Block size:'.block_size."\n";


	$errors=0;
	$warnings=0;
	for ($i=0;$i<strlen($b1);$i++){
		echo $i.'/'.strlen($b1).' ['.(ceil($i*100/strlen($b1))).'%] errors: '.$errors.' ['.(ceil($errors*100/filesize($file1))).'%], warnings: '.$warnings."\r";
		if (debug==1) echo 'Position: '.$i.':'."\n";
		if ($b1[$i]==$b2[$i]){
			if (debug==1) echo 'Position: '.$i.' ['.$b1[$i].'] is ok!'."\n";
			continue;
		}
		$f=0;
		for ($y=-backward_search_length;$y<forward_search_length;$y++){
			$c=1;
			for ($t=0;$t<block_size;$t++){
				if ($b1[$i+$t]!==$b2[$i+$y+$t]){
					$c=0;
					if (debug==1) echo 'Position: '.$i.'+y'.$y.'+t'.$t.' ['.$b1[$i+$t].'!='.$b2[$i+$y+$t].'] block not found!'."\n";
					break;
				}else{
					if (debug==1) echo 'Position: '.$i.'+y'.$y.'+t'.$t.' ['.$b1[$i+$t].'=='.$b2[$i+$y+$t].'] continue block search!'."\n";
				}
			}
			if ($c==1){	
				if (debug==1) echo 'Position: '.$i.'+'.$y.' block found!'."\n";
				if ($y>0){
					$tmp=$b2;
					if (debug==1) echo 'Old b1: '.$b1."\n";
					if (debug==1) echo 'Old b2: '.$b2."\n";
					$b2=substr($tmp,0,$i).substr($tmp,$i+$y);
					if (debug==1) echo 'New b2: '.$b2."\n";
					$i--;
					$warnings+=$y;
				}else{
					$tmp=$b1;
					if (debug==1) echo 'Old b1: '.$b1."\n";
					if (debug==1) echo 'Old b2: '.$b2."\n";
					$b1=substr($tmp,0,$i+$y).substr($tmp,$i);
					if (debug==1) echo 'New b1: '.$b1."\n";
					$i+=$y;
					$warnings+=-$y;
				}
				$f=1;
			}
			if ($f==1) break;
		}
		if ($f==1) continue;
		$errors++;
		if (debug==1) echo 'Position: '.$i.' error - not found.'."\n";
	}

	echo "\n\n".'Errors: '.$errors.'/'.filesize($file1).' ['.(ceil($errors*100/filesize($file1))).'%]';
	echo "\n".'Warnings: '.$warnings.'/'.filesize($file1).' ['.(ceil($warnings*100/filesize($file1))).'%]';
?>
