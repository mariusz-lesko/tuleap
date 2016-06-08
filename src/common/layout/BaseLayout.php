<?php
/**
 * Copyright (c) Enalean, 2016. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Layout;

use Widget_Static;
use Response;

abstract class BaseLayout extends Response
{
    /**
     * The root location for the current theme : '/themes/Tuleap/'
     */
    public $root;

    /**
     * The root location for images : '/themes/Tuleap/images/'
     */
    public $imgroot;

    public function __construct($root)
    {
        parent::__construct();
        $this->root     = $root;
        $this->imgroot  = $root . '/images/';
    }

    abstract public function header(array $params);
    abstract public function footer(array $params);
    abstract public function displayStaticWidget(Widget_Static $widget);
    abstract public function isLabFeature();

    /** @deprecated */
    public function feedback($feedback)
    {
        return '';
    }
}
