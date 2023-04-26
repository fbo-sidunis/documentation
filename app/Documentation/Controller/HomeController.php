<?php

namespace App\Documentation\Controller;


class HomeController extends Controller
{

  public function render()
  {
    $this->datas["STATE"] = [
      "page" => getRequest("p"),
    ];
    return $this->display();
  }
}
