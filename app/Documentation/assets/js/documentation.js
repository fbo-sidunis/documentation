import { Recherche } from "./documentation/recherche.js";
import { Page } from "./documentation/page.js";
import { Menu } from "./documentation/menu.js";

window.Recherche = Recherche;
window.Page = Page;
window.Menu = Menu;

Page.init();
Menu.init();
Recherche.init();
