<?php

$chemin = $_SERVER["DOCUMENT_ROOT"]; 
echo $chemin."<br>";
$filename = $chemin.'/fichiers/fichier.txt';

if (file_exists($filename)) {
    echo "Le fichier $filename existe.";
	//unlink('test.html');//efface le fichier
} else {
    echo "Le fichier $filename n'existe pas.";
}



// on place le contenu dans une variable. (exemple hein ^^)
$contenu = 'test';
//$contenu .= $_POST['nom']."\r\n";
//$contenu .= $_POST['prenom'];
 
// on ouvre le fichier en écriture avec l'option a
// il place aussi le pointeur en fin de fichier (il tentera de créer aussi le fichier si non existant)
$h = fopen($chemin."/fichier.txt", "a");
fwrite($h, $contenu);
fclose($h);
?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script> 
<script type="text/javascript">
function initURLTextarea(){
	$("#outter input").autocomplete({
		wordCount:1,
		mode: "outter",
		on: {
			query: function(text,cb){
				var words = [];
				for( var i=0; i<urls.length; i++ ){
					if( urls[i].toLowerCase().indexOf(text.toLowerCase()) == 0 ) words.push(urls[i]);
				}
				cb(words);								
			}
		}
	});
}
var countries = [];
function initContriesTextarea(){
	$.ajax('http://aerocanada-industries.com/adminaero/pages/fichier.txt',{
		success: function(data, textStatus, jqXHR){
		countries = data.replace(/\r/g, "" ).split("\n"); 					
		$("input#ville").autocomplete({
			wordCount:1,
			on: {
				query: function(text,cb){
					var words = [];
					for( var i=0; i<countries.length; i++ ){
						if( countries[i].toLowerCase().indexOf(text.toLowerCase()) == 0 ) words.push(countries2[i]);
						if( words.length > 5 ) break;
					}
					cb(words);								
				}
			}
			});											
		}
	});
}
$(document).ready(function(){
	initContriesTextarea();
	initURLTextarea();
});
</script>

<style type="text/css">
	ul.auto-list{
		display:none;
		position:absolute;
		top:0px;
		left:0px;
		background: none repeat scroll 0 0 #F6F6F6;
		border: 1px solid #E5E5E5;
		padding:0;margin:0;
		list-style:none;
	}
	ul.auto-list>li:hover,ul.auto-list>li[data-selected=true]{
		background-color:#319FFF;
		color:#fff;
	}
	ul.auto-list>li{
		cursor:default;
		padding:2px;
	}
	mark{
		background: none repeat scroll 0 0 transparent;
		font-weight: bold;
		text-decoration: underline;
	}
</style>


<br><br>
    <input name="ville" id="ville" type="text" size="40"  />
    <div id="outter"></div>
