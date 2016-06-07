# Service wrapper for the OneAll API

The following methods are currently supported:

 * /users/TOKEN.json (used to pull data of a certain user)
 * /users.json (used to get all users who previously logged in through OneAll)
 * /connections/TOKEN.json (pull data when people log into your site through OneAll)

More? Work in progress — or feel free to send pull-requests!

## Installation

This code is available through composer and [packagist.org](https://packagist.org/packages/easybib/services_oneall) - add the following to your `composer.json`:

```json
{
  "require": {
    "easybib/services_oneall": "*"
  }
}
```

Or define a repository:

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

