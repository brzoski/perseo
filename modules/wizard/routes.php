<?php

if ($container->has('settings.database')) {
    $app->any('/wizard[{params:\b(?!wizard\b).*\w+}]',
        function (\Slim\Http\Request $request, \Slim\Http\Response $response) use ($container) {
            return $response->withRedirect($request->getUri()->getBasePath());
        });
} else {
    $app->get('/wizard[/]', function (\Slim\Http\Request $request, \Slim\Http\Response $response) use ($container) {
            $container->set('view', function ($container) {
                $view = new \Slim\Views\Twig('modules', [
                    'cache' => false
                ]);
                $router = $container->get('router');
                $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
                $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
                return $view;
            });
            $csrfarray = array();
            $csrfarray['nameKey'] = $this->get('csrf')->getTokenNameKey();
            $csrfarray['valueKey'] = $this->get('csrf')->getTokenValueKey();
            $csrfarray['name'] = $request->getAttribute($csrfarray['nameKey']);
            $csrfarray['value'] = $request->getAttribute($csrfarray['valueKey']);
            $lang = new \PerSeo\Translator($container->get('current.language'), \PerSeo\Path::LangPath('wizard'));
            $langall = $lang->get();
            return $this->get('view')->render($response, '/wizard/views/default/index.twig', [
                'csrf' => $csrfarray,
                'lang' => $langall['body'],
                'host' => \PerSeo\Path::SiteName($request),
                'vars' => $container->get('Templater')->vars('wizard'),
                'cookiepath' => \PerSeo\Path::cookiepath($request),
                'writeperm' => (is_writable(\PerSeo\Path::CONF_PATH) ? "ok" : "no"),
                'openssl' => (extension_loaded('openssl') ? "ok" : "no")
            ]);
    })->setName('wizard');
    $app->post('/wizard/test[/]',
        function (\Slim\Http\Request $request, \Slim\Http\Response $response) use ($container) {
			$myresponse = array(
				'type' => 'json',
				'verbose' => false
			);
			$container->set('myresponse', $myresponse);
            return $response->withJson(\wizard\Controllers\Test::main($container));
        })->setName('wizard');
    $app->post('/wizard/install[/]',
        function (\Slim\Http\Request $request, \Slim\Http\Response $response) use ($container) {
			$myresponse = array(
				'type' => 'json',
				'verbose' => false
			);
			$container->set('myresponse', $myresponse);
            return $response->withJson(\wizard\Controllers\Install::main($container));
        })->setName('wizard');
}