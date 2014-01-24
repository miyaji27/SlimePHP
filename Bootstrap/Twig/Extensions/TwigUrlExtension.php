<?php

namespace Slim\Bootstrap\Twig\Extensions;

class TwigUrlExtension extends \Twig_Extension
{

    public function getName()
    {
        return 'twig_url_extension';
    }

    public function getFunctions()
    {
        return array(
            'assets' => new \Twig_Function_Method($this, 'assets'),
						'url' => new \Twig_Function_Method($this, 'url'),
        );
    }

    public function assets($url, $appName = 'default')
    {
			$app = \Slim\Slim::getInstance($appName);
      return sprintf('%s%s/%s',$app->request()->getRootUri(),$app->config('assets.path'),$url);
    }

    public function url($url, $appName = 'default')
    {
			$app = \Slim\Slim::getInstance($appName);
      return sprintf('%s%s',$app->request()->getRootUri(),$url);
    }
}
