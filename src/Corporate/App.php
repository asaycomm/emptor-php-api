<?php

namespace Emptor\Corporate;

use GuzzleHttp\Cookie\CookieJar;


class App
{
    private $baseUrl = "/";
    private $username = "";
    private $password = "";
    private $client;
    private $guzzleClient;

    /**
     * @param $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param $baseUrl
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param $username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $client
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $client;
    }

    /**
     * @param $client
     */
    public function setGuzzleClient($client)
    {
        $this->guzzleClient = $client;
        return $client;
    }

    /**
     * @return bool
     */
    public function startSession()
    {
        $crawler = $this->client->request('GET', $this->baseUrl . "Loginpage.aspx");
        $form = $crawler->selectButton('Giriş')->form();
        $submit = $this->client->submit($form, array('ctrlLogin$txtLogonName' => $this->username, 'ctrlLogin$txtPassword' => $this->password));

        if ($this->loginControl($submit->getUri())) {
            $this->saveSession($submit->getUri());
        }

    }

    /**
     * @param $redirected_uri
     * @return bool
     */
    public function loginControl($redirected_uri)
    {
        $parsed_url = $this->_parse_url($redirected_uri);

        if (!isset($parsed_url["ReturnUrl"])) {
            return true;
        }

        return false;
    }

    /**
     * Check Session
     */
    public function sessionCheck()
    {
        $file = @file_get_contents(__DIR__."/cookie.txt");
        if( $file ){
            return true;
        }
        return false;
    }

    /**
     * Start Cookie
     */
    public function initialize()
    {
        //return $this->sessionCheck() ? $this->sessionCheck() : $this->startSession();
        return $this->startSession();
    }

    /**
     * Storage the Session
     */
    private function saveSession()
    {
        $cookies = $this->client->getCookieJar()->all();
        $_SESSION["cookies"] = $cookies;
        $data = "";
        foreach ($cookies as $c) {
            $data .= $c->getName()."=".$c->getValue().";";
        }

        $write_file = file_put_contents(__DIR__."/cookie.txt",$data);
        return $write_file;
    }

    /**
     * @param $url
     */
    private function _parse_url($url)
    {
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        return $query;
    }


    /**
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getPoolItems($page = 1, $limit = 20)
    {

        $havuzQuery = $this->client->request('GET', $this->baseUrl . "screen.aspx?xml=FWFInbox.xml&menuID=FMenu1.1&ctrInd=0");

        $form = $havuzQuery->filter('#ctl01')->form();
        $nextPageButton = $havuzQuery->filter('#ScreenPartInbox_PAGEINDEX')->eq(0);
        $totalPageCount = $havuzQuery->filter('#ScreenPartInbox_CURRENTRECORDCOUNT')->eq(0);
        $totalPageCount = $totalPageCount->extract(["value"])[0];

        $ScreenPartInbox_PAGEINDEX = (int)$nextPageButton->extract(["value"])[0];
        $submit = $this->client->submit($form, array('ScreenPartInbox_PAGEINDEX' => (int)$page - 1, 'ScreenPartInbox_X_PAGESIZE' => $limit));

        $header = [
            'id',
            'akis_no',
            'akis_adi',
            'form_adi',
            'adim',
            'marka',
            'aciklama',
            'status',
            'baslangic',
            'atanma_zamani',
        ];

        $havuzItems = $submit->filter('#WFInbox_ScreenPartInbox_ScreenPartInbox_DXMainTable .dxgvDataRow')->each(function ($node) use ($header) {
            $idValue = $node->filter("td")->eq(1)->filter("img")->extract(["idvalue"])[0];

            $clearTags = strip_tags($node->html());
            $parse = explode("\n", $clearTags);
            $parse[0] = $idValue;

            unset($parse[1]);
            unset($parse[2]);
            unset($parse[3]);
            unset($parse[13]);
            unset($parse[14]);
            unset($parse[15]);

            $parse = array_values($parse);
            $combined = array_combine($header, $parse);
            return $combined;

        });

        return [
            'data' => $havuzItems,
            'totalPage' => $totalPageCount,
            'currentPage' => $page
        ];
    }


    /**
     * @param int $page
     * @return array
     */
    public function getSelfItems($page = 1, $limit = 20)
    {

        $havuzQuery = $this->client->request('GET', $this->baseUrl . "screen.aspx?xml=FWFInbox.xml&menuID=FMenu1.1&ctrInd=0");
        $form = $havuzQuery->filter('#ctl01')->form();
        $nextPageButton = $havuzQuery->filter('#grdInboxTaken_PAGEINDEX')->eq(0);
        $totalPageCount = $havuzQuery->filter('#grdInboxTaken_CURRENTRECORDCOUNT')->eq(0);
        $totalPageCount = $totalPageCount->extract(["value"])[0];

        if ($page > $totalPageCount) {
            return [];
        }

        $ScreenPartInbox_PAGEINDEX = (int)$nextPageButton->extract(["value"])[0];
        $submit = $this->client->submit($form, array('grdInboxTaken_PAGEINDEX' => (int)$page - 1, 'grdInboxTaken_X_PAGESIZE' => $limit));

        $header = [
            'akis_no',
            'akis_adi',
            'form_adi',
            'adim',
            'marka',
            'aciklama',
            'status',
            'baslangic',
            'atanma_zamani',
            'gonderen',
        ];

        $havuzItems = $submit->filter('#WFInbox_ScreenPartTaken_grdInboxTaken_DXMainTable .dxgvDataRow')->each(function ($node) use ($header) {
            $clearTags = strip_tags($node->html());
            $parse = explode("\n", $clearTags);
            unset($parse[0]);
            unset($parse[1]);
            unset($parse[2]);
            unset($parse[9]);
            unset($parse[14]);
            unset($parse[15]);
            $combined = array_combine($header, $parse);
            return $combined;
        });

        return [
            'data' => $havuzItems,
            'totalPage' => $totalPageCount,
            'currentPage' => $page
        ];
    }


    /**
     * @param $flow_id
     * @return string
     */
    public function getRecord($flow_id)
    {
        $flowLink = $this->getRecordLink($flow_id);
        $detail = $this->client->request('GET', htmlspecialchars_decode($flowLink));

        $form = $detail->filter('#ctl01')->form();

        return [
            'html' => $detail->html(),
            'form' => $form->getValues()
        ];
    }


    /**
     * @param $flow_id
     * @return string
     */
    public function getRecordLink($flow_id)
    {
        $url = $this->baseUrl . "AjaxFunctions.aspx";
        $post["Function"] = "GetWFStepURL";
        $post["WFStepID"] = $flow_id;
        $post["TakeOn"] = 0;
        $post["PositionId"] = '';
        $flowLink = $this->client->request('POST', $url, $post);
        $link = $this->baseUrl . $flowLink->html();
        return $link;
    }


    public function mapRecordData($type, $data)
    {
        $source = json_decode(file_get_contents(__DIR__ . "/Map/" . $type . ".json"));
        foreach ($source as $localkey => $emptorkey) {
            $result[$localkey] = isset($data[$emptorkey]) ? $this->filterValue($data[$emptorkey]) : null;
        }

        return $result;
    }

    /**
     * @param $value
     * @return string
     */
    public function filterValue($value)
    {
        if( $value == "True" || $value == "Evet" ){
            $value = 1;
        }

        if( $value == "False" || $value == "Hayır" || $value == "Hayir" ){
            $value = 0;
        }

        if( $this->isDate($value) ){
            $value = date("Y-m-d H:i:s",strtotime($value));
        }

        return trim($value);
    }

    /**
     * @param $date
     * @return bool
     */
    function isDate($date)
    {
        return date('d.m.Y H:i:s', strtotime($date)) === $date;
    }


    /**
     * @param $hash
     * @param $Title
     */
    public function showAttachment($hash,$Title)
    {
        $url = $this->baseUrl . "ShowAttachment.aspx?Title=".$Title."&TicketString=".$this->encodeURIComponent($hash);

        return $url;
        $param = [
            'Title' => $Title,
            'TicketString' => $this->encodeURIComponent($hash)
        ];
        $getAttachment = $this->client->request('GET', $url, [ 'headers' => ['Accept-Encoding' => 'gzip'], 'decode_content' => false ]);
        return $getAttachment->getResponse()->getContent();

    }

    /**
     * @param $str
     * @return string
     */
    private function encodeURIComponent($str) {
        $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
        return strtr(rawurlencode($str), $revert);
    }

    public function test()
    {
        $url = $this->baseUrl . "AjaxFunctions.aspx";
        $post["Function"] = "GetWFStepURL";
        $post["WFStepID"] = $flow_id;
        $post["TakeOn"] = 0;
        $post["PositionId"] = '';
        $flowLink = $this->client->request('POST', $url, $post);
        $link = $this->baseUrl . $flowLink->html();
        return $link;
    }

}