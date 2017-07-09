<?php
namespace Fefas\SPTrans\API\OlhoVivo;
require '../vendor/autoload.php';
use GuzzleHttp\Client as HttpClient;

class Client
{
    const BASE_URL = "http://api.olhovivo.sptrans.com.br/v2.1";

    private $apiToken      = null;
    private $apiCredential = null;

    public function __construct($token)
    {
        $this->apiToken = $token;
        $this->login($token);
    }

    public function getHost()
    {
        return trim(self::BASE_URL, '/');
    }

    private function login($token)
    {
        $response = $this->request('POST', "/Login/Autenticar", array("token" => $token));

        if (false === json_decode($response->getBody()))
            throw new \Exception('Authorization did not succeed.');
        if (false === $response->hasHeader('Set-Cookie'))
            throw new \Exception('Authorization succeed, but no credential was sent.');

        // $this->apiCredential = $response->getHeader('Set-Cookie')[0];
        $t = $response->getHeader('Set-Cookie');
        // $this->apiCredential = $response->getHeader('Set-Cookie')[0];
        $this->apiCredential = $t[0];
    }

    public function getBusLine($param)
    {
        $response = $this->request('GET', '/Linha/Buscar', [
            'termosBusca' => $param
        ]);
        return json_decode($response->getBody());
    }

    public function getBusPositionByLineCode($lineCode)
    {
        $response = $this->request('GET', '/Posicao/Linha', [
            'codigoLinha' => $lineCode
        ]);
        return json_decode($response->getBody());
    }

    public function getStopsByLineCode($lineCode)
    {
        $response = $this->request('GET', '/Parada/Buscar', [
            'termosBusca' => $lineCode
        ]);
        return json_decode($response->getBody());
        // return $response->getBody();
    }

    public function getPrevParadaLinha($parada, $linha){
        // $response = $this->request('GET', '/Previsao', ['codigoParada' => $parada, 'codigoLinha' => $linha]);
        $response = $this->request('GET', '/Previsao', array('codigoParada' => $parada, 'codigoLinha' => $linha));
        return json_decode($response->getBody());
    }

    public function empresa(){
        $response = $this->request('GET', 'Empresa', null);
        return json_decode($response->getBody());
    }

    private function request($method, $resource, $queryString)
    {
        $resource = trim($resource, '/');
        $url      = "{$this->getHost()}/$resource";

        $httpClient  = new \GuzzleHttp\Client();
        $httpRequest = new \GuzzleHttp\Psr7\Request($method, $url);

        // $params = ['query' => $queryString];
        $params = array('query' => $queryString);
        if ($this->apiCredential)
            $params['headers'] = array('Cookie' => $this->apiCredential);

        return $httpClient->send($httpRequest, $params);
    }
}

$client = new Client("ec115529f02e01674bea4bc808780c1013371a37b4848f7058e5d32073a6c1d3");
$linha = $client->getBusLine('129f');
// print_r($linha);

$pos = $client->getBusPositionByLineCode(35072);
echo "<pre>" ;
print_r($pos);
echo "</pre>";

// $emp = $client->empresa();
//print_r($emp);

// $paradaslinha = $client->getStopsByLineCode('8400');
// print_r($paradaslinha);

$prev = $client->getPrevParadaLinha(640001417,35072); //2304 
// $prev = $client->getPrevParadaLinha(640001417,2304); //2304 
// 480012878
echo "<br>PREV: <br>";
// print_r($prev);
echo "<pre>" ;
echo "<h2>";
echo json_encode($prev, JSON_PRETTY_PRINT);
echo "</h2>";
echo "</pre>";