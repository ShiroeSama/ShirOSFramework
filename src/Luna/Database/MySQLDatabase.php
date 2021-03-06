<?php
	
	/**
	 * --------------------------------------------------------------------------
	 *   @Copyright : License MIT 2017
	 *
	 *   @Author : Alexandre Caillot
	 *   @WebSite : https://www.shiros.fr
	 *
	 *   @File : MySQLDatabase.php
	 *   @Created_at : 24/11/2016
	 *   @Update_at : 14/03/2018
	 * --------------------------------------------------------------------------
	 */
	
	namespace Luna\Database;
	use \PDO;
	use Luna\Config\Config;
	
	class MySQLDatabase implements Database
	{
		/**
		 * PDO Instance
		 * @var PDO
		 */
		protected $PDOModule;
		
		/** @var string */
		protected $db_name;
		
		/** @var string */
		protected $db_user;
		
		/** @var string */
		protected $db_pass;
		
		/** @var string */
		protected $db_host;
		
		/**
		 * MySQLDatabase constructor.
		 * Established the connexion to MySql Database with PDO
		 *
		 * @param string $db_name
		 * @param string $db_user
		 * @param string $db_pass
		 * @param string $db_host
		 */
		public function __construct(string $db_name, string $db_user, string $db_pass, string $db_host)
		{
			$this->db_name = $db_name;
			$this->db_user = $db_user;
			$this->db_pass = $db_pass;
			$this->db_host = $db_host;
		}
		
		protected function preparePDO()
		{
			$dsn = "mysql:dbname={$this->db_name};host={$this->db_host}";
			$pdoOptions = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
			
			$this->PDOModule = new PDO($dsn, $this->db_user, $this->db_pass, $pdoOptions);
            $this->PDOModule->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		
		public function getPDO(): PDO
		{
            $this->preparePDO();
			return $this->PDOModule;
		}
	}
?>