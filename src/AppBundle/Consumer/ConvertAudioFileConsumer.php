<?php

namespace AppBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use FFMpeg\Format\Audio\Wav as Wav;
use Symfony\Component\Yaml\Yaml;

class ConvertAudioFileConsumer implements ConsumerInterface
{
    protected $ffmpeg;

    public function __construct($ffmpeg)
    {
        $this->ffmpeg = $ffmpeg;
    }

    public function execute(AMQPMessage $msg)
    {
        $isUploadSuccess = $this->ConvertAudioFileToWav($msg);

        if (!$isUploadSuccess) {
            return false;
        }
    }

    public function ConvertAudioFileToWav(AMQPMessage $msg)
    {
        if ($message = json_decode($msg->body, true)) {
            $originFilename = $message['originFilename'];
            $originExt = $message['originExt'];
            $filename = $message['filename'];
            $filepath = $message['filepath'];
            $rabbitSesId = $message['rabbitSesId'];

            $audio = $this->ffmpeg->open($filepath.$filename);

            $format = new Wav();

            $format
                ->setAudioChannels(2)
                ->setAudioKiloBitrate(128);

            $format->on('progress', function ($audio, $format, $percentage) use ($filepath, $rabbitSesId, $originFilename, $originExt) {
                $yaml = Yaml::dump([
                    'filename' => $originFilename,
                    'ext' => $originExt,
                    'newExt' => 'wav',
                    'filepath' => $filepath,
                    'percentage' => (int) $percentage,
                    ], 1);
                file_put_contents($filepath.$rabbitSesId.'.yml', $yaml);

            });

            $audio->save($format, $filepath.$rabbitSesId.'.wav');

            $yaml = Yaml::dump([
                'filename' => $originFilename,
                'ext' => $originExt,
                'newExt' => 'wav',
                'filepath' => $filepath,
                'percentage' => 100,
            ], 1);

            file_put_contents($filepath.$rabbitSesId.'.yml', $yaml);

            return true;
        } else {
            return false;
        }
    }
}
