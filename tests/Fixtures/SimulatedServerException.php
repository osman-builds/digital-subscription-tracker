<?php

namespace Tests\Fixtures;

use RuntimeException;

/**
 * Dedicated test exception used to verify non-debug 500 error masking.
 */
class SimulatedServerException extends RuntimeException {}
