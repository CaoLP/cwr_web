<?php
include_once('simple_html_dom.php');
$html = file_get_html('http://thegioiskinfood.com/');
$data = $html->find('//*[@id="column-left"]/div/div/ul');
$link = array();
foreach ($data as $d) {
    $menulist = $d->find('a');
    foreach($menulist as $menu){
        $link[] = array(
            'href'=>$menu->href,
            'tag' => $menu->innertext
        );
    }
}
echo '<pre>';
print_r($link);
echo '</pre>';
