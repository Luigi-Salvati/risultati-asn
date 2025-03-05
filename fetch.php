<?php

$settori = array(
	'13/A1', '13/A2', '13/A3', '13/A4', '13/A5'
);

$quadrimestre = '3';


function get_page($url)
{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
        $output = curl_exec($ch);
	curl_close($ch);

	return $output;
}

$cache = file_get_contents("README.md");
if(!$cache) {
	file_put_contents("README.md", "\n");
	$cache = file_get_contents("README.md");
	if(!$cache) {
		die("Cannot access README.md file");
	}
}
$cache = implode("\n", array_slice(explode("\n", $cache), 6));

$new_found = "";
$usciti = 0;
$usciti_nuovi = 0;

foreach($settori as $settore) {
	
	if(strstr($cache, $settore) != FALSE) {
		echo "$settore: SÌ (cached)\n";
		$usciti++;
		continue;
	}

	$url = "https://asn23.cineca.it/pubblico/miur/esito/".str_replace("/", "%252F",$settore)."/1/".$quadrimestre;
	$page = get_page($url);
	
	if($page === FALSE)
		exit(1);

	$pubblicato = (strstr($page, "Sessione Principale") != FALSE);
	if($pubblicato) {
		echo $page;
	}

	echo "$settore: " . ($pubblicato ? "SÌ" : "NO") . "\n";

	if($pubblicato) {
		$usciti++;
		$usciti_nuovi++;
		$new_found = "- " . date("d/m/Y") . ": " . $settore .
			" ([I Fascia](https://asn23.cineca.it/pubblico/miur/esito/" . str_replace("/", "%252F", $settore) . "/1/".$quadrimestre."), " .
			"[II Fascia](https://asn23.cineca.it/pubblico/miur/esito/" . str_replace("/", "%252F", $settore) . "/2/".$quadrimestre."))\n" .
			$new_found;
		file_put_contents("README.md", $new_found . $cache);
	}

	usleep(500000);
}

echo "\n$usciti_nuovi nuovi settori pubblicati.\n";
echo "Usciti $usciti settori su " . count($settori) . ".\n";
$new_found = "Usciti " . $usciti . " settori su " . count($settori) . ".\n\n" . $new_found;
$new_found = "# Risultati III Quadrimestre ASN 2023 - Area 13 A\n\n" . $new_found;
$new_found = "![logo](img/logo-2023.png)\n\n" . $new_found;
file_put_contents("README.md", $new_found . $cache);
