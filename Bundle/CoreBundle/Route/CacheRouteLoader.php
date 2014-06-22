<?php
namespace Victoire\Bundle\CoreBundle\Route;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CacheRouteLoader extends Loader
{

    protected $cacheRouteRegisterer;
    protected $em;
    private $loaded = false;

    public function __construct($cacheRouteRegisterer, $em)
    {
        $this->cacheRouteRegisterer = $cacheRouteRegisterer;
        $this->em = $em;
    }


    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $routes = new RouteCollection();
        $cachedRoutes = $this->cacheRouteRegisterer->getRoutes();
        if (count($cachedRoutes) === 0) {
            $pages = $this->em->getRepository('VictoirePageBundle:BasePage')->findAll();
            $cachedRoutes = $this->cacheRouteRegisterer->registerRoutes($pages);
        }
        foreach ($cachedRoutes as $routeName => $route) {
            $defaults = array(
                '_controller' => 'VictoirePageBundle:Page:show',
            );
            $routes->add($routeName, new Route('/' . implode('/', $route['pattern']), $defaults));

        }

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'cache';
    }


}