<?php

namespace Redis\RSMQRESTClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        //create queue
        $qname = 'TestQ';
        /*$result = $this->get('rsmq.rest.queue_api')->create($qname);
        dump($result);*/

        //send message
        $message = 'Hello from RSMQ';
        $delay = 0;
        $messageId = $this->get('rsmq.rest.message_api')->send($qname, $message, $delay);
        dump($messageId);

        //delete message
        $messageId='ejwj3ehfloec6UuIGOjpYbz5PzrIooes';
        $result = $this->get('rsmq.rest.message_api')->delete($qname, $messageId);
        dump($result);

        //change message visibility
        $messageId='ejwjdotqbkO7GvkxWjPOci4TcarBpqSA';
        $vt=5;//The length of time, in seconds, that a message received from a queue will be invisible to other receiving components when they ask to receive messages. Allowed values: 0-9999999
        $result = $this->get('rsmq.rest.message_api')->changeMessageVisibility($qname, $messageId,$vt);
        dump($result);

        //receive message
        $result = $this->get('rsmq.rest.message_api')->receiveMessage($qname);
        dump($result);

        //delete queue
        /*$qname = 'TestQ';
        $result = $this->get('rsmq.rest.queue_api')->delete($qname);
        dump($result);*/

        //get queue attributes
        $result = $this->get('rsmq.rest.queue_api')->getQueueAttributes($qname);
        dump($result);

        //list queues
        $result = $this->get('rsmq.rest.queue_api')->listQueues();
        dump($result);

        return $this->render('RSMQRESTClientBundle:Default:index.html.twig');
    }

}
