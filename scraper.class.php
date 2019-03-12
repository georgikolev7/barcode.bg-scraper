<?php


class Scraper
{
    const BARCODE_URL = 'http://barcode.bg/barcode/BG/barcode-:barcode/Информация-за-баркод.htm';
    const BARCODE_NAME_URL = 'http://barcode.bg/barcode/BG/Информация-за-баркод.htm?barcode=:name';

	/*
	*  Проверява валидността на баркода
	*/
	
    public static function isValidBarcode($barcode)
    {
        $barcode = trim($barcode);
        if (!is_numeric($barcode))
        {
            return false;
        }

        if (strlen($barcode) < 10)
        {
            return false;
        }

        return true;
    }
	
	
	
	/*
	*  Проверява валидността на баркода
	*/

    public static function getHTMLContent($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_REFERER, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $str = curl_exec($curl);

        $status = curl_getinfo($curl);

        if (curl_errno($curl))
        {
            echo 'curl_errno';
            echo curl_error($curl);
        }

        curl_close($curl);

        return $str;
    }
	
	
	
	/*
	*  Търсене на баркод по име на артикул
	*/

    public function searchBarcodeName($name)
    {
        $name = trim($name);
        $name = mb_convert_case($name, MB_CASE_UPPER, "UTF-8");

        require_once 'libs/simple_html_dom.php';
        $url = str_replace(':name', urlencode($name) , self::BARCODE_NAME_URL);

        $htmlContent = self::getHTMLContent($url);

        $html = new simple_html_dom();
        $content = str_get_html($htmlContent);

        if (!$content)
        {
            return false;
        }

        if (($table = $content->find('table.randomBarcodes', 0)))
        {
            $theData = array();

            foreach ($table->find('tr') as $row)
            {
                $rowData = array();

                foreach ($row->find('td') as $cell)
                {
                    $rowData[] = $cell->plaintext;
                }

                $theData[] = $rowData;
            }

            $array = array_map('array_filter', $theData);
            $array = array_filter($array);

            $item = [];
            $similarity = 0;

            foreach ($array as $ar)
            {
                $res = similar_text($name, ltrim($ar[2], ' '));

                if ($res > $similarity)
                {
                    $similarity = $res;
                    $item = array(
                        'barcode' => trim($ar[1], ' ') ,
                        'name' => ltrim($ar[2], ' ') ,
                        'rating' => $ar[4],
                        'similarity' => $similarity
                    );
                }
            }

            return $item;
        }

        return false;
    }
	
	
	
	/*
	*  Търсене на артикул по баркод
	*/

    public function searchBarcode($barcode)
    {
        if (strlen($barcode) < 10)
        {
            return false;
        }

        require_once 'libs/simple_html_dom.php';
        $url = str_replace(':barcode', $barcode, self::BARCODE_URL);

        $html = new simple_html_dom();
        $content = file_get_html($url);

        if (($table = $content->find('table.randomBarcodes', 0)))
        {
            $theData = array();

            foreach ($table->find('tr') as $row)
            {
                $rowData = array();

                foreach ($row->find('td') as $cell)
                {
                    $rowData[] = $cell->plaintext;
                }

                $theData[] = $rowData;
            }

            $array = array_map('array_filter', $theData);
            $array = array_filter($array);

            $count = count($array);
            $array = $array[1];

            $verified = ($array[4] > 2) ? 1 : 0;

            $item = array(
                'barcode' => trim($array[1], ' ') ,
                'name' => ltrim($array[2], ' ') ,
                'rating' => $array[4],
            );

            return $item;
        }

        return false;
    }
}