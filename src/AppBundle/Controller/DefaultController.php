<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class DefaultController extends Controller
{
    /**
     * @Route("/percentage/{session_id}", name="percentage page", defaults={"session_id" = null})
     */
    public function percentageAction($session_id)
    {
        $filepath = __DIR__.$this->container->getParameter('path_to_save_files').'/';

        if ($session_id && is_file($filepath.$session_id.'.yml')) {
            try {
                $yaml = Yaml::parse(file_get_contents($filepath.$session_id.'.yml'));

                return $this->render('default/percentage.html.twig', [
                    'percentage' => $yaml['percentage'],
                ]);
            } catch (ParseException $e) {
                printf('Unable to parse the YAML string: %s', $e->getMessage());
            }
        }

        return $this->render('default/percentage.html.twig', [
            'percentage' => 0,
        ]);
    }

    /**
     * @Route("/status/{session_id}", name="status page", defaults={"session_id" = null})
     */
    public function statusAction($session_id)
    {
        $filepath = __DIR__.$this->container->getParameter('path_to_save_files').'/';

        if ($session_id && is_file($filepath.$session_id.'.yml')) {
            try {
                $yaml = Yaml::parse(file_get_contents($filepath.$session_id.'.yml'));

                return $this->render('default/status.html.twig', [
                    'filename' => $yaml['filename'],
                    'ext' => $yaml['ext'],
                    'filepath' => $yaml['filepath'],
                    'sessionId' => $session_id,
                    'newExt' => $yaml['newExt'],
                ]);
            } catch (ParseException $e) {
                printf('Unable to parse the YAML string: %s', $e->getMessage());
            }
        }

        return $this->render('default/error404.html.twig');
    }
}
