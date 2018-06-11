<?php
/**
 * Pinoco: makes existing static web site dynamic transparently.
 * Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5
 *
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @package    Pinoco
 */

/**
 * Null renderer.
 *
 * @package Pinoco
 */
class Pinoco_NullRenderer extends Pinoco_Renderer
{
    /**
     * Do nothing in rendering phase.
     *
     * @param string $page
     * @param array $extravars
     * @return void
     */
    public function render($page, $extravars = array())
    {
    }
}
