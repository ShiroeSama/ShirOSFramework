<?php
	
	/**
	 * --------------------------------------------------------------------------
	 *   @Copyright : License MIT 2017
	 *
	 *   @Author : Alexandre Caillot
	 *   @WebSite : https://www.shiros.fr
	 *
	 *   @File : RouteBuilder.php
	 *   @Created_at : 18/03/2018
     *   @Update_at : 26/03/2018
	 * --------------------------------------------------------------------------
	 */
	
	namespace Luna\Component\Routing\Builder;
	
	use Luna\Component\Bag\ServerBag;
    use Luna\Component\DI\DependencyInjector;
	use Luna\Component\Exception\ConfigException;
	use Luna\Component\Exception\DependencyInjectorException;
	use Luna\Component\Exception\RouteException;
	use Luna\Component\HTTP\Request\Request;
    use Luna\Component\Routing\Route;
	use Luna\Config\Config;
	use Luna\Controller\Controller;
	
	class RouteBuilder
	{
		/** @var Config */
		protected $ConfigModule;

        /** @var DependencyInjector */
        protected $DIModule;

        /** @var Request */
        protected $request;
		
		/** @var string */
		protected $userRequest;
		
		/** @var array */
		protected $requestTab;
		
		/** @var array */
		protected $routes = [];
		
		/** @var array */
		protected $route;
		
		/** @var string */
		protected $namespace;
		
		/** @var Controller */
		protected $controller;
		
		/** @var string */
		protected $method;
		
		/** @var array */
		protected $params;


        /**
         * RouteBuilder constructor.
         *
         * @param Request $request
         * @param array $requestTab
         *
         * @throws \Luna\Component\Exception\ConfigException
         */
		public function __construct(Request $request, array $requestTab)
		{
			$this->ConfigModule = Config::getInstance();
			$this->DIModule = new DependencyInjector();
			$this->request = $request;
			
			$this->userRequest = (sizeof($requestTab) === 0) ? '/' : implode('/', $requestTab);
			$this->requestTab = $requestTab;
			$this->routes = $this->ConfigModule->getRouting('ROUTES');
			$this->namespace = $this->ConfigModule->getRouting('NAMESPACE');
		}
		
		/**
		 * Prepare the Route
		 *
		 * - Check if the Controller Dir exist
		 * - Check if the route exist in the config
		 *
		 * @throws ConfigException
		 * @throws DependencyInjectorException
		 * @throws RouteException
		 */
		public function prepare()
		{
			# Prepare the Route's List
			$this->formatRoutes();
			
			# Check if the route exist
			$key = $this->matchRoute();
			if (!$key) {
			    throw new RouteException("Route '{$this->request}' not found");
			}
			
			# Set the Rout information
			$this->route = [ $key => $this->routes[$key] ];
			
			# Get Action
			$action = $this->routes[$key]['Action'];
			
			# Action Parse
			$action = explode('.', $action);

            # Prepare RequestBuilder
            $this->request->getServer()
                ->set(ServerBag::RULE, $this->routes[$key]['Rule'])
                ->set(ServerBag::RULE_NAME, $key)
                ->set(ServerBag::PATH_INFO, $this->userRequest)
            ;
			
			# Get Method
			$this->method = end($action);
			$keyForMethodName = array_search($this->method, $action);
			unset($action[$keyForMethodName]);
			
			# Get Controller
			$controllerNamespace = str_replace('/',	'\\', $this->namespace);
			$controllerClassName = $controllerNamespace . '\\' . implode('\\', $action);
			
			$this->controller = $this->DIModule->callController($controllerClassName, $this->request);
			
			# Get Parameters
			$this->params = $this->getParams($this->routes[$key]['Rule']);
			$this->request->getParametersRequest()->replace($this->params);
		}
		
		/**
		 * Get the route request
		 *
		 * @return Route
		 *
		 * @throws ConfigException
		 * @throws DependencyInjectorException
		 * @throws RouteException
		 */
		public function getRoute(): Route
		{
			if (is_null($this->route)
				|| is_null($this->controller)
				|| is_null($this->method)
				|| is_null($this->params)
			) {
				$this->prepare();
			}
			
			return new Route($this->route, $this->controller, $this->method, $this->params);
		}
		
		
		/* ------------------------ Match ------------------------ */
			
			/**
			 * Check if the route exist in the config
			 * @return bool|string
			 */
			protected function matchRoute()
			{
				foreach ($this->routes as $key => $value) {
					$rule = $value['Rule'];
					
					if (count($rule) === count($this->requestTab)) {
						if ($this->checkRouteRule($rule, $this->requestTab) && $this->checkRouteMethod($this->routes[$key])) {
							return $key;
						}
					}
				}
				
				return false;
			}
		
		
		/* ------------------------ Format ------------------------ */

            /**
             * Prepare the Route's List
             * @throws RouteException
             */
			protected function formatRoutes()
			{
				foreach ($this->routes as $key => $value) {
					if (!isset($value['Rule']) || !isset($value['Action'])) {
                        throw new RouteException(RouteException::DEFAULT_CODE, "The 'Rule' or 'Action' parameters as not configured for {$key}");
					}
					
					$value['Rule'] = (($value['Rule'] == '/') ? array('/') : explode('/', $value['Rule']));
					$this->routes[$key]['Rule'] =  $this->clearRoute($value['Rule']);
				}
			}
		
			/**
			 * Removing empty values
			 *
			 * @param array $requestTab
			 * @return array
			 */
			protected function clearRoute(Array $requestTab): array { return array_values(array_filter($requestTab, __CLASS__ . '::arrayFilterCallback', ARRAY_FILTER_USE_BOTH)); }
			protected function arrayFilterCallback($var) : bool { return (!empty($var) || $var == '0'); }
		
			
		
		/* ------------------------ Check ------------------------ */
		
			/**
			 * Check if the user's request match with a route
			 *
			 * @param array $route
			 * @param array $request
			 *
			 * @return bool
			 */
			protected function checkRouteRule(array $route, array $request): bool
			{
				$size = count($route);
				
				for ($i=0; $i < $size; $i++) {
					if ($route[$i] !== $request[$i] && $route[$i][0] !== ':') {
						return false;
					}
				}
				return true;
			}
			
			/**
			 * Check if the method (HTTP verb) of the user's request match with that of the route
			 * Example :
			 * - User Request (HTTP Method : GET) == Route (HTTP Method : GET)
			 *
			 * @param array $route
			 *
			 * @return bool
			 */
			protected function checkRouteMethod(array $route): bool
			{
				if (!isset($route['Method'])) { return true; }
				
				$routeMethod = $route['Method'];
				$requestMethod = $this->request->getServer()->get('REQUEST_METHOD');
				
				return strtolower($routeMethod) === strtolower($requestMethod);
			}
		
			/**
			 * Check if the Controller Dir exist
			 */
			protected function checkControllerDir()
			{
				return is_dir($this->rootFolder);
			}
		
		
		
		/* ------------------------ Getters ------------------------ */
			
			/**
			 * Example :
			 * - User Request ('/Test/Testy') && Route's Rule ('/Test/:name') => Param['name'] = 'Testy'
			 *
			 * @param array $route
			 *
			 * @return array
			 */
			protected function getParams(array $route): array
			{
				$params = [];
				$size = count($route);
				
				for ($i=0; $i < $size; $i++) {
					if ($route[$i][0] === ':') {
					    $params[substr($route[$i],1)] = rawurldecode($this->requestTab[$i]);
					}
				}
				
				return $params;
			}
	}
?>