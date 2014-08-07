<?php

namespace Arya;

interface Status {

    const ACCEPTED = 202,
          BAD_GATEWAY = 502,
          BAD_REQUEST = 400,
          CONFLICT = 409,
          CONTINUE_100 = 100, // <-- php borks on "CONTINUE"
          CREATED = 201,
          EXPECTATION_FAILED = 417,
          FORBIDDEN = 403,
          FOUND = 302,
          GATEWAY_TIMEOUT = 504,
          GONE = 410,
          HTTP_VERSION_NOT_SUPPORTED = 505,
          INTERNAL_SERVER_ERROR = 500,
          LENGTH_REQUIRED = 411,
          METHOD_NOT_ALLOWED = 405,
          MOVED_PERMANENTLY = 301,
          MULTIPLE_CHOICES = 300,
          NETWORK_AUTHENTICATION_REQUIRED = 511,
          NON_AUTHORITATIVE_INFORMATION = 203,
          NOT_ACCEPTABLE = 406,
          NOT_FOUND = 404,
          NOT_IMPLEMENTED = 501,
          NOT_MODIFIED = 304,
          NO_CONTENT = 204,
          OK = 200,
          PARTIAL_CONTENT = 206,
          PAYMENT_REQUIRED = 402,
          PRECONDITION_FAILED = 412,
          PRECONDITION_REQUIRED = 428,
          PROXY_AUTHENTICATION_REQUIRED = 407,
          REQUESTED_RANGE_NOT_SATISFIABLE = 416,
          REQUEST_ENTITY_TOO_LARGE = 413,
          REQUEST_HEADER_FIELDS_TOO_LARGE = 431,
          REQUEST_TIMEOUT = 408,
          REQUEST_URI_TOO_LONG = 414,
          RESET_CONTENT = 205,
          SEE_OTHER = 303,
          SERVICE_UNAVAILABLE = 503,
          SWITCHING_PROTOCOLS = 101,
          TEMPORARY_REDIRECT = 307,
          TOO_MANY_REQUESTS = 429,
          UNAUTHORIZED = 401,
          UNSUPPORTED_MEDIA_TYPE = 415,
          UPGRADE_REQUIRED = 426,
          USE_PROXY = 305;

}
