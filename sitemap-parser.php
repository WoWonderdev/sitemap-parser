<?php

$url = array("http://glazovmebel.ru/sitemap.xml", "https://alicia-mebel.ru/sitemap.xml" ,"https://btsmebel.ru/sitemap.xml","https://izhmebel.com/sitemap.xml","https://mebelony.ru/sitemap.xml","https://www.zarechye.ru/sitemap.xml","http://mebel-kmk.by/sitemap_index.xml","http://mlk-mebel.ru/wp-sitemap.xml");

$rootpath = __DIR__;
for($folderNumber = 0; $folderNumber < count($url); $folderNumber++){
    $rootpath = __DIR__ . "/" . mb_substr(mb_substr($url[$folderNumber], strpos($url[$folderNumber], "//") + 2), 0, strpos(mb_substr($url[$folderNumber], strpos($url[$folderNumber], "//") + 2), "/"));
    mkdir($rootpath);

    $filepath = $rootpath . "/" . basename($url[$folderNumber]);
    file_put_contents($filepath, file_get_contents($url[$folderNumber]));
    $xml_file = simplexml_load_file($rootpath . "/" . basename($url[$folderNumber]));
    $urls = array();

    if(count($xml_file->sitemap) > 0){
        for($i = 0; $i < count($xml_file->sitemap); $i++){
            for($j = 0; $j < count($xml_file->sitemap[$i]->loc); $j++){
                $loc = file_put_contents($rootpath . "/" . basename($xml_file->sitemap[$i]->loc[$j]), file_get_contents($xml_file->sitemap[$i]->loc[$j]));
                $url_file = simplexml_load_file($rootpath . "/" . basename($xml_file->sitemap[$i]->loc[$j]));
                for($h = 0; $h < count($url_file->url); $h++){
                    array_push($urls, (string)$url_file->url[$h]->loc);
                }
            }
        }   
    }
    else if(count($xml_file->url) > 0){
        for($i = 0; $i < count($xml_file->url); $i++){
            array_push($urls, (string)$xml_file->url[$i]->loc);
        }
    }

    $old_sitemap_txt = fopen($rootpath . "/" . "save_sitemap.txt", "r");
    $old_sitemap = explode("\n", fread($old_sitemap_txt, filesize($rootpath . "/" . "save_sitemap.txt")));
    fclose($old_sitemap_txt);

    if($old_sitemap != $urls){
        $message = "<html><body>";
        $differens = array_values(array_diff($urls, $old_sitemap));
        for($i = 0; $i < count($differens); $i++){
            $message .= "<a href=" . $differens[$i] . " target=\"_blank\">" . $differens[$i] . "</a><br>";
        }
        $message .= "</body></html>";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        mail("email", "Новые страницы " . mb_substr(mb_substr($url[$folderNumber], strpos($url[$folderNumber], "//") + 2), 0, strpos(mb_substr($url[$folderNumber], strpos($url[$folderNumber], "//") + 2), "/")), $message, $headers);
        $save_sitemap = array_merge($old_sitemap, $urls);
        array_unique($save_sitemap);
    }
    
    $save_sitemap = fopen($rootpath . "/" . "save_sitemap.txt", "w");
    fwrite($save_sitemap, implode("\n", $urls));
    fclose($save_sitemap);
    unset($urls);
}

?>