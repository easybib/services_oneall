# Service wrapper for the OneAll API

The following methods are currently supported:

 * /users/TOKEN.json (used to pull data of a certain user)
 * /users.json (used to get all users who previously logged in through OneAll)
 * /connections/TOKEN.json (pull data when people log into your site through OneAll)

More? Work in progress — or feel free to send pull-requests!

## Installation

Add the following to your `composer.json`:

```json
{
  "repositories": [
    {
      "url": "http://github.com/easybib/services_oneall",
      "type": "vcs"
    }
  ],
  "require": {
    "easybib/services_oneall": "*"
  }
}
```

### Requirements:

 * PHP 5.3.x
 * [HTTP_Request2](http://pear.php.net/package/HTTP_Request2)

### Tests

```
$ phpunit
```

## License

Copyright (c) 2013, Till Klampaeckel

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
