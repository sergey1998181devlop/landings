<?php

require_once('View.php');

class SitemapView extends View
{
    public $routes;

    public function fetch()
    {
        $this->routes = [
            '/' => 'Главная страница', // Homepage
            '/#documents' => 'Документы', // Contacts
            '/share_files/docs/текст_для_страницы_информация_о_структуре_и_составе%20акционеров.pdf' => 'Информация о структуре и составе акционеров ООО МКК «Лорд» ', // Info
            '/share_files/docs/режим_работы_и_обособленные_подразделения.pdf' => 'Информация о графике работе ООО МКК «Лорд» и обособленных подразделений', 
        ];

        $this->design->assign('routes', $this->routes);
        return $this->design->fetch('sitemap.tpl');
    }
}
