<?php

/*
 * This file is part of the overtrue/socialite.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Overtrue\Socialite\AccessToken;
use Overtrue\Socialite\Config;
use Overtrue\Socialite\User;
use PHPUnit\Framework\TestCase;

class Php83CompatibilityTest extends TestCase
{
    public function testArrayAccessAndJsonSerialization()
    {
        $config = new Config(['client_id' => 'app-id']);
        $this->assertTrue(isset($config['client_id']));
        $this->assertSame('app-id', $config['client_id']);

        $config['secret'] = 'app-secret';
        $this->assertSame('app-secret', $config['secret']);
        unset($config['secret']);
        $this->assertNull($config['secret']);

        $token = new AccessToken(['access_token' => 'token-value']);
        $this->assertSame('"token-value"', json_encode($token));

        $user = new User(['id' => 'openid']);
        $this->assertSame('openid', $user['id']);
        $this->assertSame('{"id":"openid"}', json_encode($user));
    }
}
