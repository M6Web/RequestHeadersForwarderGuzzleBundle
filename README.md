# RequestHeadersForwarderGuzzleBundle

[![Build Status](https://travis-ci.org/M6Web/RequestHeadersForwarderGuzzleBundle.svg?branch=master)](https://travis-ci.org/M6Web/RequestHeadersForwarderGuzzleBundle)

Symfony Bundle which fetch some HTTP headers from current request and reuse them in Guzzle clients sub-requests.

One of the "use case" may be when you call a frontal API which make a sub-request to another API which needs authentication HTTP headers... 
Using this bundle, you can call the frontal API with authentication headers and let it forward them seamlessly to other APIs.

## Configuration

```yml
m6_web_request_headers_forwarder_guzzle:
    clients:
        test.guzzle1:                               # Guzzle client service id
            headers: ['x-auth-sample', 'something'] # Headers that will be forwarded to "test.guzzle1" client requests

services:
    test.guzzle1:
        class: 'GuzzleHttp\Client'

```

## Suggest

- [GuzzleHttp Bundle](https://github.com/M6Web/GuzzleHttpBundle) to easily instantiate and configure Guzzle client services.
