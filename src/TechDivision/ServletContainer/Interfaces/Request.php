<?php

/**
 * TechDivision\ServletContainer\Interfaces\Request
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Interfaces;

/**
 * Interface for the servlet request.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 *              Johann Zelger <j.zelger@techdivision.com>
 */
interface Request {

    /**
     * Parse request content
     *
     * @param string $content
     * @return void
     */
    public function parse($content);

    /**
     * validate actual InputStream
     *
     * @param string $buffer InputStream
     * @return void
     */
    public function initFromRawHeader($buffer);

}
