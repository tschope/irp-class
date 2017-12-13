<?php

namespace Irpclass;

/**
 * This is the dotenv class.
 *
 * It's responsible for loading a `.env` file in the given directory and
 * setting the environment vars.
 */
class Irpclass
{

    private $url = "https://burghquayregistrationoffice.inis.gov.ie/Website/AMSREG/AMSRegWeb.nsf/(getAppsNear)";

    public $params = [
        'cat' => 'Study', // 'Study', 'Work'
        'sbcat' => 'All',
        'typ' => 'New',
    ];

    public $defaultParams = [
        'verify' => false,
        'openpage' => false,
        '_' => 1,
    ];

    /**
     * Create a new Irpclass instance.
     *
     * @param array $params
     *
     * @return void
     */
    public function __construct()
    {

    }

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
     * Function to transform retrive date string in carbon date
     *
     * @param array $slots
     *
     * @return array
     */

    private function datesTransform(Array $slots)
    {
        $return = [];
        foreach ($slots as $slot)
        {
            $time = new \Carbon\Carbon(str_replace(' -','',$slot['time']));
            $return['time'] = $time;
            $return['id'] = $slot['id'];
        }
        return $return;
    }

    /**
     * Function to get data from server
     *
     * @return array
     */
    public function get(Array $params = null)
    {

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

}
