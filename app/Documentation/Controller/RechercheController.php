<?php

namespace App\Documentation\Controller;

use App\Documentation\Helpers\Content;
use App\Documentation\Helpers\Recherche;

class RechercheController extends Controller
{

  public function recherche()
  {
    $q = getRequest("q");
    $terms = array_filter(explode(" ", $q));
    $recherche = new Recherche($terms);
    $results = $recherche->search();
    $results = array_map(fn ($branch) => [
      "title" => $branch->getFilArianeString(),
      "fullPath" => $branch->getFullPath(),
    ], $results);
    return successResponse([
      "choices" => $results,
    ]);
  }
}
