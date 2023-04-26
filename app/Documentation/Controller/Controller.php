<?php

namespace App\Documentation\Controller;

use Core\Controller as CoreController;
use Core\Twig\Extension;

class Controller extends CoreController
{

  function __construct()
  {
    parent::__construct();
    $this->loader->addPath(__DIR__ . '/../templates', 'Documentation');
    Extension::addAssetPath(__DIR__ . '/../assets');
  }

  public function getJsonPost($name)
  {
    $json = file_get_contents('php://input');
    $json = json_decode($json, true);
    return $json[$name];
  }
}
