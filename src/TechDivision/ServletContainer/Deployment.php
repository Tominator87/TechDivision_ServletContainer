<?php

/**
 * TechDivision\ServletContainer\Deployment
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer;

/**
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class Deployment {

    /**
     * Path to the container's base directory.
     * @var string
     */
    const CONTAINER_BASE_DIRECTORY = '/container/baseDirectory';

    /**
     * Path to the container's host configuration.
     * @var string
     */
    const CONTAINER_HOST = '/container/host';

    /**
     * The container thread that invokes the deployment.
     * @var \TechDivision\ApplicationServer\ContainerThread
     */
    protected $containerThread;

    /**
     * Array with the initialized applications.
     * @var array
     */
    protected $applications;

    /**
     * Initializes the deployment with the container thread.
     *
     * @param $containerThread \TechDivision\ApplicationServer\ContainerThread
     * @return void
     */
    public function __construct($containerThread) {
        $this->containerThread = $containerThread;
    }

    /**
     * Returns the container thread the invokes the deployment.
     *
     * @return \TechDivision\ApplicationServer\ContainerThread The container thread
     */
    public function getContainerThread() {
        return $this->containerThread;
    }

    /**
     * Returns the deployed applications.
     *
     * @return array The deployed applications
     */
    public function getApplications() {
        return $this->applications;
    }

    /**
     * Returns an array with available applications.
     *
     * @return \TechDivision\Server The server instance
     */
    public function deploy() {

        // the container configuration
        $containerThread = $this->getContainerThread();
        $configuration = $containerThread->getConfiguration();

        // load the host configuration for the path to the web application folder
        $baseDirectory = $configuration->getChild(self::CONTAINER_BASE_DIRECTORY)->getValue();
        $appBase = $configuration->getChild(self::CONTAINER_HOST)->getAppBase();

        // gather all the deployed web applications
        foreach (new \FilesystemIterator($baseDirectory . $appBase) as $folder) {

            // check if file or subdirectory has been found
            if (is_dir($folder)) {

                // initialize the application name
                $name = basename($folder);

                // initialize the application instance
                $application = $containerThread->newInstance('\TechDivision\ServletContainer\Application', array($name));
                $application->setConfiguration($configuration);

                // add the application to the available applications
                $this->applications[$application->getName()] = $application->connect();
            }
        }

        // return initialized applications
        return $this;
    }
}