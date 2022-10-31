<?php

namespace Tests\Unit;

use Tests\TestCase;

class ShipmentTest extends TestCase
{
    /**
     * @test
     * @small
     */
    public function retries_twice_on_connect_exception(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @small
     */
    public function retries_twice_when_http_error_code_is_greater_or_equal_to_500(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @small
     */
    public function does_not_retry_when_http_error_code_is_less_than_500(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @small
     */
    public function retries_with_a_1_second_delay(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @small
     */
    public function reruns_the_request_with_a_refresh_token_when_a_401_error_occurs(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @small
     */
    public function has_10_seconds_of_timeout_for_requests(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @small
     */
    public function sets_the_base_uri_based_on_the_current_environment(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @small
     */
    public function sets_the_bearer_token_header_in_request(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @small
     */
    public function sets_the_accept_application_json_header_in_request(): void
    {
        $this->markTestIncomplete();
    }
}
