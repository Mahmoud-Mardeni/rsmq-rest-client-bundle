<?php

namespace Redis\RSMQRESTClientBundle\RestfulClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Symfony\Component\Form\Exception\LogicException;

class MessageAPI
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send($qname, $message, $delay=0)
    {
        try
        {
        $response = $this->client->post(
            '/messages/'.$qname,
            ['form_params'=>['message'=>$message,'delay'=>$delay]]
        );
        return \GuzzleHttp\json_decode($response->getBody(),true);
        } catch (TransferException $e) {
            //catches all 4xx and 5xx status codes
            throw new LogicException(sprintf('RSMQ REST server error name: (%s), with message: %s.',$e->getCode(),$e->getMessage()));
        }
    }

    public function delete($qname,$msgId)
    {
        try
        {
        $response = $this->client->delete(
            '/messages/'.$qname.'/'.$msgId
        );
        return \GuzzleHttp\json_decode($response->getBody(),true);
        } catch (TransferException $e) {
            //catches all 4xx and 5xx status codes
            throw new LogicException(sprintf('RSMQ REST server error name: (%s), with message: %s.',$e->getCode(),$e->getMessage()));
        }
    }

    public function changeMessageVisibility($qname,$msgId,$vt)
    {
        try
        {
        $response = $this->client->put(
            '/messages/'.$qname.'/'.$msgId,
            ['form_params'=>['vt'=>$vt]]
        );
        return \GuzzleHttp\json_decode($response->getBody(),true);
        } catch (TransferException $e) {
            //catches all 4xx and 5xx status codes
            throw new LogicException(sprintf('RSMQ REST server error name: (%s), with message: %s.',$e->getCode(),$e->getMessage()));
        }
    }

    public function receiveMessage($qname,$vt=30)
    {
        try
        {
        $response = $this->client->get(
            '/messages/'.$qname,
            ['form_params'=>['vt'=>$vt]]
        );
        return \GuzzleHttp\json_decode($response->getBody(),true);
        } catch (TransferException $e) {
            //catches all 4xx and 5xx status codes
            throw new LogicException(sprintf('RSMQ REST server error name: (%s), with message: %s.',$e->getCode(),$e->getMessage()));
        }
    }

}