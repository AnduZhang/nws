<?php
namespace app\components;

use Yii;
use yii\base\Component;

class LSRParser extends Component {
    public $url;
    public $fileName;

    public function getContent() {
        $returnInfo = [];

        if (!$this->url) {
            throw new \Exception('Invalid URL', 503);
        }

        try {
            $generalInformation = array();
            $generalInformation['file'] = $this->fileName;
            $fp = fopen($this->url, 'r');

            $countLsrGroups = preg_match_all('/####(.*)####/', file_get_contents($this->url), $matches); //TODO : unused, remove?

            $currentLsrGroup = 0;
            $lineNumber = 0;
            $headersEnd = false;
            $usefullLineNumber = 0;
            $usefullSubLineNumber = 0; //Unite this lines
            $isNextLineIsHeaderGeneralInfo = false;
            if ($fp) {
                while (($line = fgets($fp)) !== false) {
                    if (!$headersEnd && $isNextLineIsHeaderGeneralInfo) {
                        $lineElements = explode(' ',$line);
                        $isNextLineIsHeaderGeneralInfo = false;

                        $generalInformation['time'] = trim(implode(' ',[$lineElements[0],$lineElements[1],$lineElements[2]]));
                        $generalInformation['date'] = trim(implode(' ',[$lineElements[3],$lineElements[4],$lineElements[5],$lineElements[6]]));
                        continue;
                    }
                    if (preg_match('/NATIONAL WEATHER SERVICE/',$line)) {
                        $cityAndStateInfo = explode(' ',trim(str_replace('NATIONAL WEATHER SERVICE','',trim($line))));

                        $generalInformation['state'] = $cityAndStateInfo[1];
                        $generalInformation['city'] = $cityAndStateInfo[0];

                        if (count($cityAndStateInfo)==3) {
                            $generalInformation['state'] = $cityAndStateInfo[2];
                            $generalInformation['city'] = $cityAndStateInfo[0].' '.$cityAndStateInfo[1];
                        }

                        $isNextLineIsHeaderGeneralInfo = true;
                        continue;
                    }
                    if (preg_match('/####(.*)####/', $line, $matches)) {
                        // It's a new record
                        $currentLsrGroup++;
                    }

                    $lineNumber++;
                    if ($headersEnd==true) {
                        if (!trim($line)) { //Next line
                            $usefullLineNumber++;
                            $usefullSubLineNumber = 1;
                            continue;
                        } else {
                            //Check if we need to decrement line number
                            preg_match("/(\d{4}) (AM|PM)/", substr($line,0,7), $matches);
                            if (!$matches && $usefullSubLineNumber==1) {
                                $usefullSubLineNumber = 3;
                                $usefullLineNumber--;
                            }

                            $returnInfo[$usefullLineNumber][$usefullSubLineNumber] = $this->_getContentFromLine($line,$usefullSubLineNumber);
                            $usefullSubLineNumber++;

                        }


                    } else {
                        if ($this->_checkIfHeadersLine($line)) {
                            $headersEnd = true;
                        }
                    }
                }

                //formatting the array
                foreach ($returnInfo as $key=>$arr) {

                    if (!isset($arr[1])) {

                        unset($returnInfo[$key]);
                        continue;
                    }

                    $newArray = array_merge($arr[1],$arr[2]);

                    $newArray['commonInformation'] = '';
                    if (isset($arr['3']['info1'])) {
                        $newArray['commonInformation'].= $arr['3']['info1'];

                        if (isset($arr['4']['info2'])) {
                            $newArray['commonInformation'].= ' '.$arr['4']['info2'];
                        }
                    }

                    $returnInfo[$key] = $newArray;
                }
            } else {
                // error opening the file.
            }
            fclose($fp);
        } catch (\Exception $e) {
            //TODO: what to do here
        }

        $generalInformation['alerts'] = $this->_changeFormatOfArray(array_values($returnInfo));
        return $generalInformation;
    }

    protected function _checkIfHeadersLine($line) {
        return strripos($line,'..REMARKS..');
    }

    protected function _getContentFromLine($line,$n) {
        $r = array();
        switch ($n) {
            case 1:
                $r['time'] = substr($line,0,7);
                $r['event'] = substr($line,12,16);
                $r['distDirCity'] = substr($line,29,23);
                $r['latLon'] = substr($line,53,14);

            break;
            case 2:
                $r['mdy'] = substr($line,0,10);
                $r['emagUnit'] = substr($line,12,13);
                $r['county'] = substr($line,29,18);
                $r['st'] = substr($line,48,2);
                $r['sourse'] = substr($line,53,16);
                break;
            case 3:
                $r['info1'] = trim($line);
                break;
            case 4:
                $r['info2'] = trim($line);
                break;
        }
        return preg_replace('/\s+/', ' ',$r);
    }

    private function _changeFormatOfArray($fileArray) {
        foreach ($fileArray as $key=>$alert) {
            if (!isset($alert['mdy'])) {
                continue; // I found that sometimes we don't have such value
            }
            $fileArray[$key]['date'] = $alert['mdy'];
            unset($fileArray[$key]['mdy']);

            $latLon = explode(' ',$alert['latLon']);


            $fileArray[$key]['latitude'] = preg_replace("/[^0-9-.,]+/", "", $latLon[0]);
            if (strripos($latLon[0],'S')) {
                $fileArray[$key]['latitude'] = -(float)$fileArray[$key]['latitude'];
            }

            $fileArray[$key]['longitude'] = preg_replace("/[^0-9-.,]+/", "", $latLon[1]);
            if (strripos($latLon[1],'W')) {
                $fileArray[$key]['longitude'] = -(float)$fileArray[$key]['longitude'];
            }

            $magn = explode(' ', trim($alert['emagUnit']));

            $fileArray[$key]['magnitude'] = preg_replace("/[^0-9.,]+/", "", @$magn[0]);
            $fileArray[$key]['magnitudeUnit'] = @$magn[1];
            $fileArray[$key]['event'] = trim($fileArray[$key]['event']);

            unset($fileArray[$key]['latLon']);
            unset($fileArray[$key]['distDirCity']);
            unset($fileArray[$key]['emagUnit']);
            unset($fileArray[$key]['county']);
            unset($fileArray[$key]['st']);
            unset($fileArray[$key]['sourse']);
            unset($fileArray[$key]['commonInformation']);
        }

        return $fileArray;
    }


}
?>
