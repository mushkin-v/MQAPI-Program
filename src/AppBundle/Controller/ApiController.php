<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Yaml\Exception\ParseException;

class ApiController extends Controller
{
    /**
     * @Route("/api/upload", name="upload page")
     */
    public function uploadAction(Request $request)
    {
        $container = $this->container;

        if (
            $request->files->count() > 0
            && in_array($request->query->get('ext'), $container->getParameter('file_types'), true)
        ) {
            $file = $request->files->get('file');

            $rabbitSesId = uniqid($request->query->get('filename'), false);

            $filename = $rabbitSesId.'.'.$request->query->get('ext');

            $filepath = __DIR__.$container->getParameter('path_to_save_files');

            $file->move($filepath, $filename);
            chmod($filepath.$filename, 0666);

            if (!is_file($filepath.$rabbitSesId.'.yml')) {
                $yaml = Yaml::dump([
                    'filename' => $request->query->get('filename'),
                    'ext' => $request->query->get('ext'),
                    'newExt' => 'wav',
                    'filepath' => $filepath,
                    'percentage' => 0,
                ], 1);

                file_put_contents($filepath.$rabbitSesId.'.yml', $yaml);
                chmod($filepath.$rabbitSesId.'.yml', 0666);
            }

            $message = json_encode([
                    'originFilename' => $request->query->get('filename'),
                    'originExt' => $request->query->get('ext'),
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'rabbitSesId' => $rabbitSesId,
                ]
            );

            $this
                ->get('old_sound_rabbit_mq.convert_audio_file_producer')
                ->publish($message, $container->getParameter('mq.routing_key'));

            $response = new Response(' ',
                Response::HTTP_OK,
                [
                    'content-type' => 'text/html',
                    'rabbitSesId' => $rabbitSesId,
                ]
            );

            $response->prepare($request);

            return $response->sendHeaders();
        } else {
            return $this->render('default/error404.html.twig');
        }
    }

    /**
     * @Route("/api/download/{session_id}", name="download page", defaults={"session_id" = null})
     */
    public function downloadAction($session_id)
    {
        $filepath = __DIR__.$this->container->getParameter('path_to_save_files');

        if (is_file($filepath.$session_id.'.yml')) {
            try {
                $yaml = Yaml::parse(file_get_contents($filepath . $session_id . '.yml'));

                $converted_file = $filepath . $session_id . '.' . $yaml['newExt'];

                if (is_file($converted_file)) {
                    $response = new BinaryFileResponse($converted_file);
                    $response->headers->set('Content-Disposition', 'filename=' . $yaml['filename'] . '.' . $yaml['newExt']);

                    return $response;
                }
            } catch (ParseException $e) {
                printf('Unable to parse the YAML string: %s', $e->getMessage());
            }
        }

        return $this->render('default/error404.html.twig');
    }
}
