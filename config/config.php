<?php

return [

    /**
     * The default number of seconds for the Rate Limiter.
     */
    'seconds_between_logs' => null,

    /**
     * Here you can set an optional class@method which should return a boolean
     * that specifies wether the Request/Response should be traced.
     * It takes two parameters: the request and the response.
     *
     * For example: 'App\Http\Kernel@shouldTrace'
     */
    'should_trace_user' => null,

];
