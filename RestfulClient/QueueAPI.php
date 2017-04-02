<?php
/**
 * Created by PhpStorm.
 * User: mmardini
 * Date: 31/10/16
 * Time: 10:38 ص
 */

namespace Redis\RSMQRESTClientBundle\RestfulClient;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Symfony\Component\Form\Exception\LogicException;

class QueueAPI
{

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function create($qname, $vt = 30, $delay = 0, $maxSize = 65536)
    {
        try
        {
            $response = $this->client->post(
                '/queues/' . $qname,
                ['form_params' => ['vt' => $vt, 'delay' => $delay, 'maxsize' => $maxSize], ['exceptions' => false]]
            );
            return \GuzzleHttp\json_decode($response->getBody(), true);

        } catch (TransferException $e) {
            //catches all 4xx and 5xx status codes
            throw new LogicException(sprintf('RSMQ REST server error name: (%s), with message: %s.',$e->getCode(),$e->getMessage()));
        }
    }

    public function delete($qname)
    {
        try
        {
            $response = $this->client->delete(
                '/queues/' . $qname
            );
            return \GuzzleHttp\json_decode($response->getBody(), true);

        } catch (TransferException $e) {
            //catches all 4xx and 5xx status codes
            throw new LogicException(sprintf('RSMQ REST server error name: (%s), with message: %s.',$e->getCode(),$e->getMessage()));
        }
    }

    public function getQueueAttributes($qname)
    {
        try
        {
            $response = $this->client->get(
                '/queues/' . $qname
            );
            return \GuzzleHttp\json_decode($response->getBody(), true);

        } catch (TransferException $e) {
            //catches all 4xx and 5xx status codes
            throw new LogicException(sprintf('RSMQ REST server error name: (%s), with message: %s.',$e->getCode(),$e->getMessage()));
        }
    }

    public function listQueues()
    {
        try
        {
            $response = $this->client->get(
                '/queues/'
            );
            return \GuzzleHttp\json_decode($response->getBody(), true)['queues'];

        } catch (TransferException $e) {
            //catches all 4xx and 5xx status codes
            throw new LogicException(sprintf('RSMQ REST server error name: (%s), with message: %s.',$e->getCode(),$e->getMessage()));
        }
    }

} 