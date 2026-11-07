<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Root URL = dasbor; tamu diarahkan ke halaman login.
     */
    public function test_tamu_di_root_diarahkan_ke_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
