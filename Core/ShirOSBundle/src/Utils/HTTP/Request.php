<?php

    /**
     * --------------------------------------------------------------------------
     *   @Copyright : License MIT 2017
     *
     *   @Author : Alexandre Caillot
     *   @WebSite : https://www.shiros.fr
     *
     *   @File : Request.php
     *   @Created_at : 06/12/2017
     *   @Update_at : 06/12/2017
     * --------------------------------------------------------------------------
     */

    namespace ShirOSBundle\Utils\HTTP;

    class Request
    {
    	public const POST = 'POST';
	    public const GET = 'GET';
    	
        /** @var array $server */
        protected $server = [];

        /** @var array $get */
        protected $get = [];

        /** @var array $post */
        protected $post = [];

        public function __construct()
        {
            $this->init();
        }

        public function init()
        {
        	if (isset($_SERVER)) { $this->server =& $_SERVER; }
	        if (isset($_GET)) { $this->get =& $_GET; }
	        if (isset($_POST)) { $this->post =& $_POST; }
        }

        public function isGetRequest(string ...$params): bool
        {
        	$bool = true;
        	
        	if (!empty($this->get) && ($this->getMethod() === self::GET)) {
		        foreach ($params as $param) {
					if (!isset($this->get[$param])) {
						$bool = false;
						break;
					}
		        }
	        } else {
        		$bool = false;
	        }
        	
        	return $bool;
        }
        
        public function isPostRequest(string ...$params): bool
        {
	        $bool = true;
	
	        if (!empty($this->post) && ($this->getMethod() === self::POST)) {
		        foreach ($params as $param) {
			        if (!isset($this->get[$param])) {
				        $bool = false;
				        break;
			        }
		        }
	        } else {
		        $bool = false;
	        }
	
	        return $bool;
        }

        public function getGetRequest(): array { return $this->get; }
        public function getPostRequest(): array { return $this->post; }
        
        //TODO : Create Access Method to Server Var.
	    
	    public function getMethod(): ?string { return (isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : NULL); }
    }
?>