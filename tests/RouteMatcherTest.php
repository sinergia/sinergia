<?php

use Sinergia\Sinergia\RouteMatcher;

class RouteMatcherTest extends PHPUnit_Framework_Testcase
{
    /**
     * @var RouteMatcher
     */
    protected $matcher;

    public function setUp()
    {
        $this->matcher = new RouteMatcher();
    }

    public function testMatchId()
    {
        $matches = $this->matcher->match("user/(?<id>\d+)", 'user/12');
        $this->assertEquals(array('id' => '12'), $matches);
    }

    public function testNoMatch()
    {
        $matches = $this->matcher->match("user/(?<id>\d+)", 'user/foo');
        $this->assertNull($matches);
    }

    public function testMatchNoParameters()
    {
        $matches = $this->matcher->match('user/\d+', 'user/12');
        $this->assertEquals(array(), $matches);
    }

    public function testRegexRoot()
    {
        $params = $this->matcher->match('/', '');
        $this->assertEquals(array(), $params);
    }

    public function testRegexOptional()
    {
        $pattern = '/posts(/:ano)(/:mes)(/:dia)';
        $params = $this->matcher->match($pattern, 'posts');
        $this->assertEquals(array(), $params);

        $params = $this->matcher->match($pattern, 'posts/2012');
        $this->assertEquals(array('ano' => '2012'), $params);

        $params = $this->matcher->match($pattern, 'posts/2012/04');
        $this->assertEquals(array('ano' => '2012', 'mes' => '04'), $params);

        $params = $this->matcher->match($pattern, 'posts/2012/04/27');
        $this->assertEquals(array('ano' => '2012', 'mes' => '04', 'dia' => '27'), $params);
    }

    public function testSlug()
    {
        $params = $this->matcher->match('/page/*', 'page/contact/sent');
        $this->assertEquals(array('slug' => 'contact/sent'), $params);
    }

    public function testRegexRootNoMatch()
    {
        $params = $this->matcher->match('/', 'foo');
        $this->assertNull($params);
    }

    public function testRegexLaravelParam()
    {
        $params = $this->matcher->match('/produtos/{id}', 'produtos/23');
        $this->assertEquals(array('id' => '23'), $params);
    }

    public function testRegexLaravelWithOptionalParam()
    {
        $params = $this->matcher->match('/produtos/{id?}', 'produtos/23');
        $this->assertEquals(array('id' => '23'), $params);
    }

    public function testRegexLaravelWithoutOptionalParam()
    {
        $params = $this->matcher->match('/produtos/{id?}', 'produtos');
        $this->assertEquals(array(), $params);
    }

    public function testFormat()
    {
        $pattern = '/produtos/:id(.:format)';
        $params = $this->matcher->match($pattern, 'produtos/12');
        $this->assertEquals(array('id' => '12'), $params);

        $params = $this->matcher->match($pattern, 'produtos/12.xml');
        $this->assertEquals(array('id' => '12', 'format' => 'xml'), $params);

        $params = $this->matcher->match('/page/*(.:format)', 'page/teste.xml');
        $this->assertEquals(array('slug' => 'teste', 'format' => 'xml'), $params);
    }

    public function testFindMatch()
    {
        $routes = array(
            '/(:controller)(/:action)(/:id)(.:format)' => 'callback'
        );

        $route = $this->matcher->findMatch($routes, '');
        $this->assertEquals(array('callback', array()), $route);

        $route = $this->matcher->findMatch($routes, 'home');
        $this->assertEquals(array('callback', array('controller' => 'home')), $route);

        $route = $this->matcher->findMatch($routes, 'user/index');
        $this->assertEquals(array('callback', array('controller' => 'user', 'action' => 'index')), $route);

        $route = $this->matcher->findMatch($routes, 'user/edit/12');
        $this->assertEquals(array('callback', array('controller' => 'user', 'action' => 'edit', 'id' => '12')), $route);

        $route = $this->matcher->findMatch($routes, 'user/view/12.xml');
        $this->assertEquals(array('callback', array('controller' => 'user', 'action' => 'view', 'id' => '12', 'format' => 'xml')), $route);

        $route = $this->matcher->findMatch($routes, 'does/not/match/path');
        $this->assertNull($route);
    }
}
