<?php
	
	/**
	 * --------------------------------------------------------------------------
	 *   @Copyright : License MIT 2017
	 *
	 *   @Author : Alexandre Caillot
	 *   @WebSite : https://www.shiros.fr
	 *
	 *   @File : Kernel.php
	 *   @Created_at : 03/12/2017
     *   @Update_at : 07/05/2018
	 * --------------------------------------------------------------------------
	 */
	
	namespace Luna;

	use Luna\Bridge\Component\Dispatcher\Exception\ExceptionDispatcherBridge;
	use Luna\Bridge\Component\Routing\RouterBridge;
	use Luna\Component\Container\LunaContainer;
	use Luna\Component\Logger\Builder\LoggerBuilder;
	use Luna\Component\Handler\Exception\ExceptionHandler;
    use Luna\Component\HTTP\Request\Builder\RequestBuilder;
    use Luna\Component\HTTP\Request\ResponseInterface;
	use Luna\Component\Session\Session;
	use Luna\Component\Session\SessionInterface;
	use Luna\Config\Config;
	
	use \Throwable;

    class Kernel implements KernelInterface
	{
	    # -------------------------------------------------------------
	    #   Constants
	    # -------------------------------------------------------------
		
	    // ----------------
	    // App Name
	    
	    public const APP_NAME = 'Luna';
	    
	
	    // ----------------
	    // LOG
	    
		public const LOG_EXCEPTION = 'Exception';
		
	
	    // ----------------
	    // Environment
	    
	    public const ENV_PROD = 'prod';
	    public const ENV_DEV = 'dev';
	    public const ENV_TEST = 'test';
	
	
	    # -------------------------------------------------------------
	    #   Vars
	    # -------------------------------------------------------------
	    
        /** @var Config */
	    protected $ConfigModule;
	    
	    /** @var LunaContainer */
	    protected $ContainerModule;
	    
	    /** @var SessionInterface */
	    protected $SessionModule;

        /** @var RouterBridge */
        protected $RouterBridgeModule;

        /** @var ExceptionDispatcherBridge */
        protected $ExceptionDispatcherBridgeModule;


        # -------------------------------------------------------------
        #   Information Vars
        # -------------------------------------------------------------
	
	    /** @var string */
	    protected $env;
	
	    /** @var ResponseInterface */
	    protected $response;


        /**
		 * Kernel constructor.
		 */
		public function __construct()
		{
            # ----------------------------------------------------------
			# LUNA Constants

            define('LUNA_ROOT', __DIR__);
			define('LUNA_CONFIG_DIR', LUNA_ROOT . '/Config/files');



			# ----------------------------------------------------------
			# APP Constants

            $luna_root = LUNA_ROOT;
			$app_root = preg_replace('#/vendor/([^<]*)$#', '', $luna_root);
			
			define('APP_ROOT', $app_root);
			define('APP_CONFIG_DIR', APP_ROOT . '/config');
			
			
			
			# ----------------------------------------------------------
			# Default Constants
			
			$this->env = self::ENV_DEV;

            try {

                # ----------------------------------------------------------
                # Prepare Singleton Instance

                $this->ConfigModule = Config::getInstance();
	            $this->ContainerModule = LunaContainer::getInstance();
	
	
	
	            # ----------------------------------------------------------
	            # Prepare Session
	            
	            $this->SessionModule = new Session();
	            $this->SessionModule->init();
	
	
	            # ----------------------------------------------------------
	            # Define Kernel on Container
	            
	            $this->ContainerModule
		            ->setKernel($this)
		            ->setSession($this->SessionModule)
	            ;


                # ----------------------------------------------------------
                # Define Global vars
	
	            $this->env = is_null($this->ConfigModule->get('Luna.Environment')) ? self::ENV_DEV : $this->ConfigModule->get('Luna.Environment');


                # ----------------------------------------------------------
                # Construct Object


                $this->RouterBridgeModule = new RouterBridge();
                $this->RouterBridgeModule->bridge();
            } catch (Throwable $throwable) {
                try {
                    $this->ExceptionDispatcherBridgeModule = new ExceptionDispatcherBridge();
                    $this->ExceptionDispatcherBridgeModule->bridge();
                    $this->ExceptionDispatcherBridgeModule->dispatch($throwable);
                } catch (Throwable $throwable) {
	                $logger = LoggerBuilder::createExceptionLogger();
	                $exceptionHandler = new ExceptionHandler($logger, $throwable);
                    $exceptionHandler->onKernelException();
                }
            }
		}

		/**
		 * Access point of the application
		 * Allow to start the routing component and settings the Luna Framework
		 *
		 * - DI (Dependency Injector)
		 * - Routing
		 * - Templating
		 * - Constant
		 */
		public function start()
		{
		    try {

                # ----------------------------------------------------------
                # Build the Request

                $request = RequestBuilder::create();
                
                $this->ContainerModule->setRequest($request);


                # ----------------------------------------------------------
                # Routing Init

                $this->response = $this->RouterBridgeModule->init($request);
            } catch (Throwable $throwable) {
                try {
	                $this->ExceptionDispatcherBridgeModule = new ExceptionDispatcherBridge();
	                $this->ExceptionDispatcherBridgeModule->bridge();
                    $this->ExceptionDispatcherBridgeModule->dispatch($throwable);
                } catch (Throwable $throwable) {
	                $logger = LoggerBuilder::createExceptionLogger();
                    $exceptionHandler = new ExceptionHandler($logger, $throwable);
                    $exceptionHandler->onKernelException();
                }
            }
        }


        /**
         * Send response to the client.
         * Configure headers and display content
         */
        public function send()
        {
	        $this->response->getHeaders();
	        $this->response->getContent();
        }
	
	
	
	    /* -------------------------------------------------------------------------- */
	    /* Informations */
		
	    /**
	     * @return string
	     */
	    public function getEnv(): string
	    {
		    return $this->env;
	    }
	
	    /**
	     * @return string
	     */
	    public function getLogPath(): string
	    {
		    return APP_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $this->getEnv();
	    }
	
	    /**
	     * @return ResponseInterface
	     */
	    public function getResponse(): ResponseInterface
	    {
		    return $this->response;
	    }
	}
?>