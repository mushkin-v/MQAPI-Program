#MQAPI
---
It is a demo version of the MQAPI program which converts audio files.
Only "mp3" to "wav" format conversion is now suported.

>Author [Mushkin Vitaliy].  
>Thank you.

#####Version
Demo version (not for professional use).

#####MQAPI instruction:

1. Use console command './bin/start_rabbitmq --workers=5' to start RabbitMQ workers.
Use option --workers to select how much workers will work on conversion asynchronously, default is 3 workers.
Also one worker for client RPC will always starts automatically.
> Example of console command:  
>  ./bin/start_rabbitmq --workers=5
2. Use status page with session Id to verify conversion progress.
> Example of console command:  
>  http://your_host/status/yourSessionId1234567
3. If your file conversion is ready, you also can download it at status page.
A download link will appear when conversion progress is finished.
> Example of console command:  
>  http://your-host/status/yourSessionId1234567

#####Config:
Your can change different options of MQAPI program in:
>app/config/parameters.yml

#####Tech

MQAPI program uses a number of open source projects to work properly:

* [RabbitMQ] - Robust messaging for applications.
* [PHP FFmpeg] -An Object Oriented library to convert video/audio files with FFmpeg / AVConv.
* [Symfony] - The standard foundation on which the best PHP applications are built.

#####License
MIT

[RabbitMQ]:https://www.rabbitmq.com
[PHP FFmpeg]: https://github.com/PHP-FFMpeg/PHP-FFMpeg
[Symfony]: http://symfony.com
[Mushkin Vitaliy]:https://github.com/mushkin-v
