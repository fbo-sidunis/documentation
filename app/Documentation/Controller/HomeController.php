<?php

namespace App\Documentation\Controller;


class HomeController extends Controller
{

  public function render()
  {
    $this->datas["STATE"] = [
      "page" => getRequest("p"),
    ];
    $this->datas["EDITMENU"] = $_ENV["EDITMENU"];
    $this->datas["EDITPAGE"] = $_ENV["EDITPAGE"];
    return $this->display();
  }
}
