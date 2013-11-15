<?php

/**
 * TechDivision\ServletContainer\Servlets\Legacy\MageServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Servlets\Legacy;

use TechDivision\ServletContainer\Http\HttpPart;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Servlets\PhpServlet;

/**
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
 */
class MageServlet extends PhpServlet
{
    
    /**
     * Defines session mapping
     *
     * @var array
     */
    protected $sessionMapping = array(
        'core' => 'core/session',
        'customer_base' => 'customer/session',
        'catalog' => 'catalog/session',
        'checkout' => 'checkout/session',
        'adminhtml' => 'adminhtml/session',
        'admin' => 'admin/session',
    );

    /**
     * (non-PHPdoc)
     *
     * @see \TechDivision\ServletContainer\Servlets\PhpServlet::prepareGlobals()
     */
    protected function prepareGlobals(Request $req)
    {
        // prepare the globals
        parent::prepareGlobals($req);
        
        // if the application has not been called over a vhost configuration append application folder name
        if ($this->getServletConfig()->getApplication()->isVhostOf($req->getServerName()) === true) {
            $directoryIndex = $this->getDirectoryIndex();
        } else {
            $directoryToPrepend = DIRECTORY_SEPARATOR . $this->getServletConfig()->getApplication()->getName() . DIRECTORY_SEPARATOR;
            $directoryIndex = $this->getDirectoryIndex($directoryToPrepend);
        }
        
        // initialize the server variables
        $req->setServerVar('SCRIPT_FILENAME', $req->getServerVar('DOCUMENT_ROOT') . $directoryIndex);
        $req->setServerVar('SCRIPT_NAME', $directoryIndex);
        $req->setServerVar('PHP_SELF', $directoryIndex);
        
        // ATTENTION: This is necessary because of a Magento bug!!!!
        $req->setServerVar('SERVER_PORT', NULL);
    }

    /**
     * Tries to load the requested file and adds the content to the response.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $req
     *            The servlet request
     * @param \TechDivision\ServletContainer\Interfaces\Response $res
     *            The servlet response
     * @throws \TechDivision\ServletContainer\Exceptions\PermissionDeniedException Is thrown if the request tries to execute a PHP file
     * @return void
     */
    public function doGet(Request $req, Response $res)
    {
        // start session
        $req->getSession()->start();
        // load \Mage
        $this->load();
        // init globals
        $this->initGlobals($req);
        // run \Mage and set content
        $res->setContent($this->run($req));
        // set headers
        $this->addHeaders($res);
    }

    /**
     * Loads the necessary files needed.
     *
     * @return void
     */
    public function load()
    {
        require_once $this->getServletConfig()->getWebappPath() . '/app/Mage.php';
    }

    /**
     * Runs the WebApplication
     *
     * @return string The WebApplications content
     */
    public function run(Request $req)
    {
        
        try {
            
            // register the Magento autoloader as FIRST autoloader
            spl_autoload_register(array(new \Varien_Autoload(), 'autoload'), true, true);

            #Varien_Profiler::enable();
            if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
                \Mage::setIsDeveloperMode(true);
            }
            
            ini_set('display_errors', 1);
            umask(0);
            
            /* Store or website code */
            $mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
            
            /* Run store or run website */
            $mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
            
            // set headers sent to false and start output caching
            appserver_set_headers_sent(false);
            ob_start();
            
            // init magento framework
            \Mage::init($mageRunCode, $mageRunType);
        
            // load session data
            foreach ($this->sessionMapping as $sessionNamespace => $sessionModel) {
                \Mage::getSingleton($sessionModel)->setData(
                    $req->getSession()->getData($sessionNamespace)
                );
            }
            
            // run magento
            \Mage::run();

            // grab the contents generated by magento
            $content = ob_get_clean();

            // persist session data
            foreach ($this->sessionMapping as $sessionNamespace => $sessionModel) {
                $req->getSession()->putData(
                    $sessionNamespace, \Mage::getSingleton($sessionModel)->getData()
                );
            }
            
        } catch (\Exception $e) {
            error_log($content = $e->__toString());
        }

        // return the content
        return $content;
    }
}