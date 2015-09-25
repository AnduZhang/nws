<?php
namespace app\components;

use Yii;
use yii\base\Component;

class CSVParser extends Component {
    public $url;



    public function getContent() {
        $returnArray = [];
        $returnArray['error'] = false;
        $returnArray['errorMessage'] = '';

        if (!$this->url) {
            throw new \Exception('Invalid URL', 503);
        }

        try {

            $fp = fopen($this->url, 'r');
            $line = 0;
            $parsedData = array();
            $headers = array();
            while ($row = fgetcsv($fp)) {


                if(!isset($row[1])) {
                    $parsedData['info'] = $row[0];
                    continue;
                }

                if (!$headers) {
                    $headers = $row;
                    continue;
                }
                $line++;
                foreach ($row as $key=>$data) {
                    $parsedData[$line][$headers[$key]] = $data;
                }

            }
            fclose($fp);
        } catch (\Exception $e) {
            $returnArray['error'] = true;
            $returnArray['errorMessage'] = $e->getMessage();
        }
        $returnArray['parsedData'] = $parsedData;
        return $returnArray;
    }

}
?>
