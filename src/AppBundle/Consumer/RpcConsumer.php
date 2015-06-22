<?php

namespace AppBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class RpcConsumer implements ConsumerInterface
{
    protected $filepath;

    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    public function execute(AMQPMessage $msg)
    {
        $isUploadSuccess = $this->Rpc($msg);

        if (!$isUploadSuccess) {
            return false;
        }
    }

    public function Rpc(AMQPMessage $msg)
    {
        if ($session_id = json_decode($msg->body, true)) {
            if (is_file(__DIR__.$this->filepath.$session_id.'.yml')) {
                try {
                    $yaml = Yaml::parse(file_get_contents(__DIR__.$this->filepath.$session_id.'.yml'));
                } catch (ParseException $e) {
                    printf('Unable to parse the YAML string: %s', $e->getMessage());
                }

                $response_data = [
                    'filename' => $yaml['filename'],
                    'newExt' => $yaml['newExt'],
                    'percentage' => $yaml['percentage'],
                ];
            } else {
                $response_data = null;
            }

            $response = new AMQPMessage(json_encode($response_data), ['correlation_id' => $msg->get('correlation_id')]);

            $msg->delivery_info['channel']->basic_publish(
                $response, '', $msg->get('reply_to'));

            return true;
        } else {
            return false;
        }
    }
}
