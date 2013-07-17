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
     * Path to the container's host configuration.
     * @var string
     */
    const CONTAINER_HOST = '/container/host';

    protected $containerThread;

    protected $applications;

    public function __construct($containerThread) {
        $this->containerThread = $containerThread;
    }

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

        // load the host configuration for the path to the webapps folder
        $host = $configuration->getChild(self::CONTAINER_HOST);

        // gather all the deployed web applications
        foreach (new \FilesystemIterator($host->getAppBase()) as $folder) {

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