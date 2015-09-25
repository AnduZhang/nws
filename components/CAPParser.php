<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\web\HttpException;

class CAPParser extends Component {
    public $url;



    public function getContent() {
        $returnArray = [];
        $returnArray['error'] = false;
        $returnArray['errorMessage'] = '';

        if (!$this->url) {
            throw new \Exception('Invalid URL', 503);
        }

        try {
            $entries = file_get_contents($this->url);
            $entries = new \SimpleXmlElement($entries);


            if(count($entries)) {
                $entries->registerXPathNamespace('prefix', 'http://www.w3.org/2005/Atom');
                $result = $entries->xpath("//prefix:entry");

                foreach ($result as $entry) {
                    $returnArray[(string)$entry->id]['general']['updated'] = (string)$entry->updated;
                    $returnArray[(string)$entry->id]['general']['published'] = (string)$entry->published;
                    $returnArray[(string)$entry->id]['general']['title'] = (string)$entry->title;
                    $returnArray[(string)$entry->id]['general']['summary'] = (string)$entry->summary;
                    $returnArray[(string)$entry->id]['cap'] = $entry->children('urn:oasis:names:tc:emergency:cap:1.1');
                }
            }
        } catch (\Exception $e) {
            $returnArray['error'] = true;
            $returnArray['errorMessage'] = $e->getMessage();
        }

        return $returnArray;

    }

    public function getAtomContent() {
        $returnArray = [];
        $returnArray['error'] = false;
        $returnArray['errorMessage'] = '';
        if (!$this->url) {
            throw new \Exception('Invalid URL', 503);
        }

        try {
            $contentFile = file_get_contents($this->url);
            $entries = new \SimpleXmlElement($contentFile);
            $objVars = get_object_vars($entries);

            if (count($entries)) {
                $returnArray['namespaces'] = $entries->getDocNamespaces();
                $returnArray['id'] = $objVars['id'];
                $returnArray['logo'] = $objVars['logo'];
                $returnArray['generator'] = $objVars['generator'];
                $returnArray['updated'] = $objVars['updated'];
                $returnArray['title'] = $objVars['title'];
                $returnArray['author'] = array('name'=>(string)$objVars['author']->name);
                $returnArray['href'] = get_object_vars($objVars['link'])['@attributes']['href'];
            }
        } catch (\Exception $e) {
            $returnArray['error'] = true;
            $returnArray['errorMessage'] = $e->getMessage();
        }
        return $returnArray;
    }

    //This functions added because of new login

    public function getAtomGeneralContent($url) {
        $returnArray = array();

        try {
            $contentFile = file_get_contents($url);
            $atomInfo = new \SimpleXmlElement($contentFile);
            $objVars = get_object_vars($atomInfo);

            if (count($atomInfo)) {
                $returnArray['id'] = $objVars['id'];
                $returnArray['updated'] = $objVars['updated'];
            }


        } catch (\Exception $e) {
            $returnArray['error'] = $e->getMessage();
        }
        return $returnArray;

    }

    public function getEntriesContent($url) {
        $returnArray = [];
        $returnArray['error'] = false;
        $returnArray['errorMessage'] = '';

        try {
            $entries = file_get_contents($url);
            $entries = new \SimpleXmlElement($entries);


            if(count($entries)) {
                $entries->registerXPathNamespace('prefix', 'http://www.w3.org/2005/Atom');
                $result = $entries->xpath("//prefix:entry");
//                var_dump($result);die;
                foreach ($result as $entry) {
                    $entryArray = [];
                    $entryArray[] = (string)$entry->id;
                    $entryArray[] = (string)$entry->updated;
                    $entryArray[] = (string)$entry->published;
                    $returnArray['entries'][]= $entryArray;
                }
            }
        } catch (\Exception $e) {
            $returnArray['error'] = true;
            $returnArray['errorMessage'] = $e->getMessage();
        }

        return $returnArray;
    }

    public function getCapContent($url) {
//        if ($url=='http://alerts.weather.gov/cap/wwacapget.php?x=GA125395C09CE8.FloodWarning.125395CFDF28GA.JAXFLSJAX.b7124fa53a46b1d1ab1baebb2c951017') {
//            $entries = file_get_contents('http://nres.dev/tmp/test.xml');
//
//        } else {
//            $entries = file_get_contents($url);
//
//        }
        $entries = file_get_contents($url);
        $entries = new \SimpleXmlElement($entries);
        return $entries;
    }
}
?>
