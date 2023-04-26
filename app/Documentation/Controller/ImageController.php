<?php

namespace App\Documentation\Controller;

use App\Documentation\Helpers\File;
use Curl\Curl;

class ImageController extends Controller
{

  public function uploadByFile()
  {
    $file = File::getFileFromName('image');
    if (!$file) {
      return jsonResponse([
        "success" => 0,
      ]);
    }
    //On génère un nom aléatoire pour le fichier
    $newName = uniqid() . '.' . $file->getExtension();
    $file->save(ROOT_DIR . 'app/Documentation/assets/upload/images/', $newName);
    return jsonResponse([
      "success" => 1,
      "file" => [
        "url" => $file->getRelativePathToDomain(),
      ]
    ]);
  }

  public function uploadByUrl()
  {
    $url = $this->getJsonPost("url");
    $curl = new Curl();
    /**
     * On récupère d'abord le nom du fichier à partir de l'url
     * On vérifie que le fichier est bien une image
     */
    $curl->get($url, [
      "Content-Type" => "image/*",
    ]);
    $name = $curl->getInfo(CURLINFO_EFFECTIVE_URL);
    $name = pathinfo($name, PATHINFO_BASENAME);
    //On nettoies les variables get
    $name = explode('?', $name)[0];
    $curl->close();
    /**
     * On récupère le contenu du fichier
     */
    $curl = new Curl();
    $fullPath = ROOT_DIR . 'app/Documentation/assets/upload/images/' . uniqid() . '.' . pathinfo($name, PATHINFO_EXTENSION);
    $curl->download($url, $fullPath);

    $url = str_replace(ROOT_DIR, '/', $fullPath);

    return jsonResponse([
      "success" => 1,
      "file" => [
        "url" => $url,
      ]
    ]);
  }
}
