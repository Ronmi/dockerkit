# DockerKit

DockerKit is NOT part of Fruit Framework.

DockerKit is a set of tools helping you manage docker container/image from php.

DockerKit supports only bash as default shell, using other shell like zsh is not supported.

## Synopsis

Write your dockerfile generator:

```php
$f = new Fruit\DockerKit\Dockerfile('debian:latest', 'John Doe <john@example.com>');

$f
    ->distro('debian')
    ->install(['php5-fpm', 'php5'])
    ->ensureBash()
    ->grouping(true)
    ->textfileArray([
        '#!/bin/bash',
        'service php5-fpm restart',
        'trap "echo Stopping fpm...;service php5-fpm stop" INT TERM',
        '(kill -STOP $BASHPID)&',
        'wait',
    ], '/start.sh')
    ->chmod('a+x', ['/start.sh'])
    ->grouping(false)
    ->entrypoint(['bash', '/start.sh']);

echo $f->generate();
```

which generates

```
FROM debian:latest
MAINTAINER John Doe <john@example.com>
RUN apt-get update \
 && apt-get install -y php5-fpm php5 \
 && apt-get clean \
 && echo 'dash dash/sh boolean false'|debconf-set-selections \
 && DEBIAN_FRONTEND=noninteractive dpkg-reconfigure dash \
 && echo '#!/bin/bash'|tee /start.sh \
 && echo 'service php5-fpm restart'|tee -a /start.sh \
 && echo 'trap "echo Stopping fpm...;service php5-fpm stop" INT TERM'|tee -a /start.sh \
 && echo '(kill -STOP $BASHPID)&'|tee -a /start.sh \
 && echo 'wait'|tee -a /start.sh \
 && chmod a+x /start.sh
ENTRYPOINT ["bash","/start.sh"]
```

So you can pipe it to `docker build` like `php my_generator.php|docker build -t my_tag -`, or

```php
(new Fruit\DockerKit\DockerBuild('my_tag')->run($f);
```

### Grouping

Grouping can merge shell commands into one `RUN` command, reduce intermediate layers needed when building image.

### Installers

You can write custom installers to share same application/library between different dockerfile generators. See `src/ServiceStarter.php`, which helps you generate shell script to manage service start/stop, as example.

```php
$f = new Fruit\DockerKit\Dockerfile('debian:latest', 'John Doe <john@example.com>');
$f->distro('debian')->install(['nginx', 'php5-fpm']);

(new Fruit\DockerKit\ServiceStarter('/entry.sh'))
    ->starters([
        'service php5-fpm start',
        'service nginx start',
    ])
    ->stopers([
        'service php5-fpm stop',
        'service nginx stop',
    ])
    ->installTo($f);

echo $f->entrypoint('/entry.sh')->generate();
```

will generates

```
FROM debian:latest
MAINTAINER John Doe <john@example.com>
RUN apt-get update \
 && apt-get install -y nginx php5-fpm \
 && apt-get clean \
 && echo '#!/bin/bash'|tee /entry.sh \
 && echo 'function start() {'|tee -a /entry.sh \
 && echo '  service php5-fpm start'|tee -a /entry.sh \
 && echo '  service nginx start'|tee -a /entry.sh \
 && echo '}'|tee -a /entry.sh \
 && echo ''|tee -a /entry.sh \
 && echo 'function stop() {'|tee -a /entry.sh \
 && echo '  service php5-fpm stop'|tee -a /entry.sh \
 && echo '  service nginx stop'|tee -a /entry.sh \
 && echo '}'|tee -a /entry.sh \
 && echo 'trap stop INT TERM'|tee -a /entry.sh \
 && echo ''|tee -a /entry.sh \
 && echo '(kill -SIGSTOP $BASHPID)&'|tee -a /entry.sh \
 && echo 'wait'|tee -a /entry.sh \
 && chmod a+x /entry.sh
ENTRYPOINT /entry.sh
```

### Supported distros

Currently we support only Debian. PR is welcome. See `src/Distro/Distro.php` about how to implement.

### More examples

You can find more examples in test cases, especially commands not documented here.

## License

You can choose any of GPL, LGPL or MIT license.
