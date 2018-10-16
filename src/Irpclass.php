<?php

namespace Irpclass;

/**
 * This is the IRP class. Old GNIB. Today the name is IRP.
 *
 * This class use guzzlehttp for CURL and Carbon for Dates.
 * What this class do, simple, go to Burghquay Registration Office website and check if any
 * available dates to make appointment.
 * This class don't make appointment, just check if some date is available.
 *
 * Feel free to help me if you find some problem or you want to improve
 */
class Irpclass
{

    private $url = "https://burghquayregistrationoffice.inis.gov.ie/Website/AMSREG/AMSRegWeb.nsf/(getAppsNear)";
    private $urlForm = "https://burghquayregistrationoffice.inis.gov.ie/Website/AMSREG/AMSRegWeb.nsf/AppSelect";

    public $params = [
        'cat' => 'Study', // 'Study', 'Work'
        'sbcat' => 'All',
        'typ' => 'Renewal', // New or Renewal
    ];

    public $defaultParams = [
        'readform' => '',
        'p' => '',
        'k' => '',
        '_' => '',
    ];

    /**
     * Create a new Irpclass instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * This class join your params with default params to make CURL
     *
     * @param array|null $params
     * @return string
     */
    private function joinArrays(Array $params = null)
    {
        if(empty($params))
        {
            $this->params = array_merge($this->params, $this->defaultParams);
        }
        else
        {
            foreach ($params as $key => $value)
            {
                if(!array_key_exists($key,$this->params))
                    return 'Error! You need to set up cat, sbcat and typ';
            }

            $this->params = array_merge($params, $this->defaultParams);
        }
    }

    /**
     * Function to transform string date in carbon date
     *
     * @param array $slots
     *
     * @return array
     */

    private function datesTransform(Array $slots)
    {
        $return = [];
        foreach ($slots as $key => $slot)
        {
            $time = new \Carbon\Carbon(str_replace(' -','',$slot['time']));
            $return[$key]['time'] = $time;
            $return[$key]['id'] = $slot['id'];
        }
        return $return;
    }

    /**
     * Function to get data from server
     *
     * @param array|null $params if you send null, the class use default params
     * @return array
     */
    public function get(Array $params = null)
    {

        $this->getTokens();

        $this->joinArrays($params);

        $client = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false ),'verify' => false));
        $res = $client->request('GET', $this->url, [
            'query' => $this->params
        ]);

        $return = [
            'success' => false,
            'error' => null,
            'message' => null,
            'results' => []
        ];

        if($res->getStatusCode() == 200)
        {
            $return['success'] = true;
            $response = \GuzzleHttp\json_decode($res->getBody(),TRUE);
            if(isset($response['empty'])){
                if($response['empty']){
                    $return['message'] = 'Empty. Don\'t have any dates available!';
                }
                else
                {
                    $return['message'] = $res->getBody();
                }
            }

            if(isset($response['slots']))
            {
                $return['message'] = 'You have available appoiments';
                $return['results'] = $this->datesTransform($response['slots']);
            }

        } else {
            $return['error'] = $res->getStatusCode();
            $return['message'] = $res->getBody();
        }

        return $return;

    }

    private function getTokens(){

        $client = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false ),'verify' => false));
        $response = $client->request('GET', $this->urlForm, [
            'OpenForm' => ''
        ]);

        $return = [
            'success' => false,
            'error' => null,
            'message' => null,
            'results' => []
        ];

        if($response->getStatusCode() == 200) {

            $html = $response->getBody()->getContents();

            $newDom = new \domDocument;
            libxml_use_internal_errors(true);
            $newDom->loadHTML($html);
            libxml_use_internal_errors(false);
            $newDom->preserveWhiteSpace = false;
            $newDom->validateOnParse = true;

            $this->defaultParams['p'] = $newDom->getElementById('p')->getAttribute('value');
            $this->defaultParams['k'] = $newDom->getElementById('k')->getAttribute('value');
            $this->defaultParams['_'] = (string)round(microtime(true) * 1000);

            $return['success'] = true;
            $return['message'] = 'Get tokens success';

        } else {
            $return['error'] = $response->getStatusCode();
            $return['message'] = $response->getBody();
        }

        return $return;

    }

}
