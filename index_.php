<?php
ini_set('post_max_size', '200M');
ini_set('upload_max_filesize', '200M');
ini_set('max_execution_time', '200M');
ini_set('max_input_time', '200M');
ini_set('memory_limit', '200M');
set_time_limit(65536);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "khunglongshop";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8');

include_once('simple_html_dom.php');
$links = array(
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=16',
        'cat' => 129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=15',
        'cat' => 129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=12',
        'cat' =>129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=11',
        'cat' =>129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=9',
        'cat' =>129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=8',
        'cat' => 129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=7',
        'cat' => 129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=6',
        'cat' => 129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=5',
        'cat' => 129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=4',
        'cat' => 129
    ),
    array(
        'link' =>'http://haicaubeo.com/Home.aspx?categoryId=3',
        'cat' => 129
    ),
);
$buildQuery = array();
foreach ($links as $l) {
    echo $l['link'] . '<br>';
    $html = file_get_html($l['link']);
    $data = $html->find('*[@id="ctl00_body_grid"]/tbody/tr/td/center/table/tbody/tr[1]/td/div/a'); //*[@id="ctl00_body_grid"]/tbody/tr[1]/td[1]/center/table/tbody/tr[1]/td/div/a
//    foreach ($data as $d) {
//        echo $d->href . '<br>';
//
//    };


    foreach ($data as $d) {
//        echo $d->href . '<br>';
        $child = file_get_html('http://haicaubeo.com/' . $d->href);
        $title_xpath = '//*[@id="ctl00_body_grid"]/tbody/tr/td/table/tbody/tr[1]/td/span';
        $price_xpath = '//*[@id="ctl00_body_grid"]/tbody/tr/td/table/tbody/tr[3]/td[2]/span';
        $sku_xpath = '//*[@id="ctl00_body_grid"]/tbody/tr/td/table/tbody/tr[4]/td[2]';
        $img_xpath = '//*[@id="ctl00_body_grid"]/tbody/tr/td/table/tbody/tr[2]/td/div/img';
        $content_xpath = '//*[@id="ctl00_body_grid"]/tbody/tr/td/table/tbody/tr[10]/td/table/tbody/tr/td';

        $title = $child->find($title_xpath, 0)->innertext;
        $price = $child->find($price_xpath, 0)->innertext;
        $sku = $child->find($sku_xpath, 0)->innertext;
        echo $sku . '<br>';
        $img = $child->find($img_xpath, 0)->src;
        $content = '';
        $content = $child->find($content_xpath, 0)->innertext;
//
        $title = trim($title);
        $price = str_replace(',', '', trim($price));
        $price = str_replace(' VND', '', $price);
        $sku = trim($sku);
        $img = 'http://haicaubeo.com' . str_replace(' ','%20', trim($img));
        $content = trim($content);
        $store_url = '/img/uploads/2015/06/' . strtolower(str_replace('%20','-',basename($img)));
//
        $sql = "INSERT INTO `products`(
        `sku`, `provider_id`,
        `name`, `slug`, `price`,
        `retail_price`, `source_price`, `excert`,
        `descriptions`,
        `category_id`, `type`)
        VALUES (
        " . $sku . ",1,
        '" . $title . "', '" . make_slug($title) . "'," . ($price + 20000) . ",
        " . $price . "," . $price . ",'',
        '" . $content . "',
        ".$l['cat'].",1)";
        $sql2 = "INSERT INTO `medias`(`ref`, `ref_id`, `file`, `position`) VALUES ('Product','[p_id]','{$store_url}',0)";
        $sql3 = "UPDATE `products` SET `media_id` = [media_id] WHERE `id` = [p_id] ";
        $buildQuery[$sku] = array(
            'product' => $sql,
            'media' => $sql2,
            'update' => $sql3
        );
        $imgSavePath = getcwd() . '\\img\\uploads\\2015\\06\\'. strtolower(str_replace('%20','-',basename($img)));
        file_put_contents($imgSavePath, file_get_contents($img));
    }
}
krsort($buildQuery);
foreach ($buildQuery as $query) {
    if (mysqli_query($conn, $query['product'])) {
        $product_id = $conn->insert_id;
        $query['media'] = str_replace('[p_id]', $product_id, $query['media']);
        if (mysqli_query($conn, $query['media'])) {
            $media_id = $conn->insert_id;
            $query['update'] = str_replace('[p_id]', $product_id, $query['update']);
            $query['update'] = str_replace('[media_id]', $media_id, $query['update']);
            if (!mysqli_query($conn, $query['update'])) {
                echo "Error: Update <br>" . mysqli_error($conn);
            };
        } else {
            echo "Error: Media <br>" . mysqli_error($conn);
        }
    } else {
        echo "Error: Product <br>" . mysqli_error($conn);
    }
}
echo 'completed !!<br>';
mysqli_close($conn);
die;

function make_slug($text)
{
    $text = preg_replace('/ä|æ|ǽ/', 'ae', $text);
    $text = preg_replace('/ö|œ/', 'oe', $text);
    $text = preg_replace('/ü/', 'ue', $text);
    $text = preg_replace('/Ä/', 'Ae', $text);
    $text = preg_replace('/Ü/', 'Ue', $text);
    $text = preg_replace('/Ö/', 'Oe', $text);
    $text = preg_replace('/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/', 'A', $text);
    $text = preg_replace('/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/', 'a', $text);
    $text = preg_replace('/Ç|Ć|Ĉ|Ċ|Č/', 'C', $text);
    $text = preg_replace('/ç|ć|ĉ|ċ|č/', 'c', $text);
    $text = preg_replace('/Ð|Ď|Đ/', 'D', $text);
    $text = preg_replace('/ð|ď|đ/', 'd', $text);
    $text = preg_replace('/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/', 'E', $text);
    $text = preg_replace('/è|é|ê|ë|ē|ĕ|ė|ę|ě|è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/', 'e', $text);
    $text = preg_replace('/Ĝ|Ğ|Ġ|Ģ/', 'G', $text);
    $text = preg_replace('/ĝ|ğ|ġ|ģ/', 'g', $text);
    $text = preg_replace('/Ĥ|Ħ/', 'H', $text);
    $text = preg_replace('/ĥ|ħ/', 'h', $text);
    $text = preg_replace('/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|Ì|Í|Ị|Ỉ|Ĩ/', 'I', $text);
    $text = preg_replace('/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|ì|í|ị|ỉ|ĩ/', 'i', $text);
    $text = preg_replace('/Ĵ/', 'J', $text);
    $text = preg_replace('/ĵ/', 'j', $text);
    $text = preg_replace('/Ķ/', 'K', $text);
    $text = preg_replace('/ķ/', 'k', $text);
    $text = preg_replace('/Ĺ|Ļ|Ľ|Ŀ|Ł/', 'L', $text);
    $text = preg_replace('/ĺ|ļ|ľ|ŀ|ł/', 'l', $text);
    $text = preg_replace('/Ñ|Ń|Ņ|Ň/', 'N', $text);
    $text = preg_replace('/ñ|ń|ņ|ň|ŉ/', 'n', $text);
    $text = preg_replace('/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/', 'O', $text);
    $text = preg_replace('/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/', 'o', $text);
    $text = preg_replace('/Ŕ|Ŗ|Ř/', 'R', $text);
    $text = preg_replace('/ŕ|ŗ|ř/', 'r', $text);
    $text = preg_replace('/Ś|Ŝ|Ş|Š/', 'S', $text);
    $text = preg_replace('/ś|ŝ|ş|š|ſ/', 's', $text);
    $text = preg_replace('/Ţ|Ť|Ŧ/', 'T', $text);
    $text = preg_replace('/ţ|ť|ŧ/', 't', $text);
    $text = preg_replace('/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/', 'U', $text);
    $text = preg_replace('/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/', 'u', $text);
    $text = preg_replace('/Ý|Ÿ|Ŷ|Ỳ|Ý|Ỵ|Ỷ|Ỹ/', 'Y', $text);
    $text = preg_replace('/ý|ÿ|ŷ|ỳ|ý|ỵ|ỷ|ỹ/', 'y', $text);
    $text = preg_replace('/Ŵ/', 'W', $text);
    $text = preg_replace('/ŵ/', 'w', $text);
    $text = preg_replace('/Ź|Ż|Ž/', 'Z', $text);
    $text = preg_replace('/ź|ż|ž/', 'z', $text);
    $text = preg_replace('/Æ|Ǽ/', 'AE', $text);
    $text = preg_replace('/ß/', 'ss', $text);
    $text = preg_replace('/Ĳ/', 'IJ', $text);
    $text = preg_replace('/ĳ/', 'ij', $text);
    $text = preg_replace('/Œ/', 'OE', $text);
    $text = preg_replace('/ƒ/', 'f', $text);
    // Cyrillic Letters
    $text = preg_replace('/А/', 'A', $text);
    $text = preg_replace('/Б/', 'B', $text);
    $text = preg_replace('/В/', 'V', $text);
    $text = preg_replace('/Г/', 'G', $text);
    $text = preg_replace('/Д/', 'D', $text);
    $text = preg_replace('/Е/', 'E', $text);
    $text = preg_replace('/Ё/', 'YO', $text);
    $text = preg_replace('/Ж/', 'ZH', $text);
    $text = preg_replace('/З/', 'Z', $text);
    $text = preg_replace('/И/', 'I', $text);
    $text = preg_replace('/Й/', 'Y', $text);
    $text = preg_replace('/К/', 'K', $text);
    $text = preg_replace('/Л/', 'L', $text);
    $text = preg_replace('/М/', 'M', $text);
    $text = preg_replace('/Н/', 'N', $text);
    $text = preg_replace('/О/', 'O', $text);
    $text = preg_replace('/П/', 'P', $text);
    $text = preg_replace('/Р/', 'R', $text);
    $text = preg_replace('/С/', 'S', $text);
    $text = preg_replace('/Т/', 'T', $text);
    $text = preg_replace('/У/', 'U', $text);
    $text = preg_replace('/Ф/', 'F', $text);
    $text = preg_replace('/Х/', 'H', $text);
    $text = preg_replace('/Ц/', 'TS', $text);
    $text = preg_replace('/Ч/', 'CH', $text);
    $text = preg_replace('/Ш/', 'SH', $text);
    $text = preg_replace('/Щ/', 'SH', $text);
    $text = preg_replace('/Ъ/', '', $text);
    $text = preg_replace('/Ы/', 'Y', $text);
    $text = preg_replace('/Ь/', '', $text);
    $text = preg_replace('/Э/', 'E', $text);
    $text = preg_replace('/Ю/', 'YU', $text);
    $text = preg_replace('/Я/', 'YA', $text);
    $text = preg_replace('/а/', 'a', $text);
    $text = preg_replace('/б/', 'b', $text);
    $text = preg_replace('/в/', 'v', $text);
    $text = preg_replace('/г/', 'g', $text);
    $text = preg_replace('/д/', 'd', $text);
    $text = preg_replace('/е/', 'e', $text);
    $text = preg_replace('/ё/', 'yo', $text);
    $text = preg_replace('/ж/', 'zh', $text);
    $text = preg_replace('/з/', 'z', $text);
    $text = preg_replace('/и/', 'i', $text);
    $text = preg_replace('/й/', 'y', $text);
    $text = preg_replace('/к/', 'k', $text);
    $text = preg_replace('/л/', 'l', $text);
    $text = preg_replace('/м/', 'm', $text);
    $text = preg_replace('/н/', 'n', $text);
    $text = preg_replace('/о/', 'o', $text);
    $text = preg_replace('/п/', 'p', $text);
    $text = preg_replace('/р/', 'r', $text);
    $text = preg_replace('/с/', 's', $text);
    $text = preg_replace('/т/', 't', $text);
    $text = preg_replace('/у/', 'u', $text);
    $text = preg_replace('/ф/', 'f', $text);
    $text = preg_replace('/х/', 'h', $text);
    $text = preg_replace('/ц/', 'ts', $text);
    $text = preg_replace('/ч/', 'ch', $text);
    $text = preg_replace('/ш/', 'sh', $text);
    $text = preg_replace('/щ/', 'sh', $text);
    $text = preg_replace('/ъ/', '', $text);
    $text = preg_replace('/ы/', 'y', $text);
    $text = preg_replace('/ь/', '', $text);
    $text = preg_replace('/э/', 'e', $text);
    $text = preg_replace('/ю/', 'yu', $text);
    $text = preg_replace('/я/', 'ya', $text);

    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/[^a-zA-Z0-9\-]/', '', $text);
    $text = preg_replace('/\-{2,}/', '-', $text);
    $text = preg_replace('/\-$/', '', $text);
    $text = preg_replace('/^\-/', '', $text);
    $text = strtolower($text);

    return $text;
}