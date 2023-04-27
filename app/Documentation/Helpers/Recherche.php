<?php

namespace App\Documentation\Helpers;

class Recherche
{
  public array $terms;
  public int $max;

  function __construct(array $terms, int $max = 10)
  {
    $this->terms = $terms;
    $this->max = $max;
  }

  /**
   * Recherche les branches correspondant aux termes
   * @return Branch[]
   */
  public function search()
  {
    $branches = Tree::getListBranches();
    $results = [];
    foreach ($branches as $branch) {
      $score = $branch->getScore($this->terms);
      if ($score > 0) {
        $results[] = [
          "branch" => $branch,
          "score" => $score,
        ];
      }
    }
    usort($results, function ($a, $b) {
      return $b["score"] <=> $a["score"];
    });
    $results = array_slice($results, 0, $this->max);
    return array_column($results, "branch");
  }
}
