# DockerKit

DockerKit is a set of tools helping you manage docker container/image from php.

DockerKit supports only bash as default shell, using other shell like zsh is not supported.

## Synopsis

Write your dockerfile generator:

```php
$f = new Fruit\DockerKit\Dockerfile('debian:latest', 'John Doe <john@example.com>');

$f
    ->import(
        (new Fruit\DockerKit\Distro\Debian)
        ->install(['php5-fpm', 'php5'])
        ->ensureBash()
    )
    ->grouping(true)
    ->textfileArray([
        '#!/bin/bash',
        'service php5-fpm restart',
        'trap "echo Stopping fpm...;service php5-fpm stop" INT TERM',
        '(kill -STOP $BASHPID)&',
        'wait',
    ], '/start.sh')
    ->shell('chmod a+x /start.sh')
    ->grouping(false)
    ->entrypoint(['bash', '/start.sh']);

echo $f->generate();
```

which generates

```
FROM debian:latest
MAINTAINER John Doe <john@example.com>
RUN apt-get update \
 && apt-get -y php5-fpm php5 \
 && apt-get clean \
 && echo 'dash dash/sh boolean false'|debconf-set-selections \
 && DEBIAN_FRONTEND=noninteractive dpkg-reconfigure dash
RUN echo '#!/bin/bash'|tee '/start.sh' \
 && echo 'service php5-fpm restart'|tee -a '/start.sh' \
 && echo 'trap "echo Stopping fpm...;service php5-fpm stop" INT TERM'|tee -a '/start.sh' \
 && echo '(kill -STOP $BASHPID)&'|tee -a '/start.sh' \
 && echo 'wait'|tee -a '/start.sh' \
 && chmod a+x /start.sh
ENTRYPOINT ["bash","/start.sh"]
```

So you can pipe it to `docker build` like `php my_generator.php|docker build -t my_tag -`, or

```php
(new Fruit\DockerKit\DockerBuild('my_tag')->run($f);
```

### Grouping

Grouping can merge shell commands into one `RUN` command, reduce intermediate layers needed when building image.

## License

You can choose any of GPL, LGPL or MIT license.
