<?php
/**
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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

namespace Tuleap\OpenIDConnectClient\Provider;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tuleap\OpenIDConnectClient\Provider\AzureADProvider\AzureADProvider;
use Tuleap\OpenIDConnectClient\Provider\AzureADProvider\AzureADProviderDao;
use Tuleap\OpenIDConnectClient\Provider\AzureADProvider\AzureADProviderManager;

class AzureADProviderManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItCreatesNewAzureProvider(): void
    {
        $azure_provider_dao     = \Mockery::mock(AzureADProviderDao::class);
        $azure_provider_manager = new AzureADProviderManager($azure_provider_dao);

        $azure_provider_dao->shouldReceive('create')->andReturn(1)->once();

        $azure_provider = new AzureADProvider(
            1,
            'Provider',
            'Id Client',
            'secret',
            false,
            'github',
            'fiesta_red',
            'tenant'
        );

        $res = $azure_provider_manager->createAzureADProvider(
            'Provider',
            'Id Client',
            'secret',
            'github',
            'fiesta_red',
            'tenant'
        );

        $this->assertEquals($azure_provider, $res);
    }
}
