#LaterJob - Database Queue with Metrics.

###I have in the past needed a simple queue for applications hosted on 'sharded host' or other limited php environments. 
1. no access to the cli, 
2. no persistent processes
3. no chmod - physically set write permissions in cpanel or equivlent.
4. Small scale single node installations.

###I developed LaterJob to power jobs such as
1. Mail Queues.
2. Sms Queues.
3. Thumbnail Generation.
4. PDF Generation.

### Its a simple queue, simlar to Zend\DB\Queue but comes packaged with `metrics`.
1. Written for php 5.3.
2. Written with Symfony2 components and Doctrine DBAL.
3. Uses Cron scripts for workers and metric generation.
4. Can Run many workers.
5. Multiple queues per script if needed.
6. Records state transitions into the database as activity which drive Metrics.
7. Installed via composer.

## Getting Started
Install via composer

```php
 "require" : {
        "icomefromthenet/laterjob" :"dev-master"
 }
```

#Learn More

I would start at the [docs](https://github.com/icomefromthenet/LaterJob/tree/master/doc).


