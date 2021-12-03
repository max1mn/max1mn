## Betfair sport exchange API connector

This is JSON REST connector to Betfair API, an sport exchange platform. Connector is made with CURL requests,
no rest frameworks are used. Connector is able to: 

* Read account balance
* Read available markets
* Read events in market
* For uk horseracing events, read runners and odds
* Read detailed odds information for selected runner
* Place bets (back bets or long position)

Main parts of connector are:
* [HTTPConnection](https://github.com/max1mn/max1mn/tree/master/example_projects/betfair/src/HTTPConnection.php)
  : CURL calls to exchange endpoints
* [Parser](https://github.com/max1mn/max1mn/tree/master/example_projects/betfair/src/Exchange/Parser.php)
  : parsing of API responses

Use case - reading of events on remote AWS server, saving winners and coefficients history to db for future backtesting.
```
# cat /var/spool/cron/crontabs/root

#betfair - create races (every 3 hours, on 10-th minute, on 10-th second)
10 */3 * * * (sleep 10; /root/scripts/run-betfair-3h.sh) >> /root/betfair/config/local/logs/cli_errors-3h.log 2>&1

#betfair - update coefs (every 1 min, on 40-th second)
*/1 * * * * (sleep 40; /root/scripts/run-betfair-1m.sh) >> /root/betfair/config/local/logs/cli_errors-1m.log 2>&1
```

Log outputs
```console
[2020-02-06 19:34:41.749897]    [INFO]  Maximn\Horseracing\Process\ExchangeRacesUpdate::run
[2020-02-06 19:34:42.099069]    [INFO]  Maximn\Horseracing\Exchange\Parser::Maximn\Horseracing\Exchange\Parser::readRunnerOdds
[2020-02-06 19:34:48.316557]    [INFO]  Maximn\Horseracing\Process\ExchangeRacesUpdate::run      FINISHED! Lines updated: 15
[2020-02-06 19:36:41.463212]    [INFO]  Maximn\Horseracing\Process\ExchangeRacesUpdate::run
[2020-02-06 19:36:41.852967]    [INFO]  Maximn\Horseracing\Exchange\Parser::Maximn\Horseracing\Exchange\Parser::readRunnerOdds
[2020-02-06 19:36:48.052525]    [INFO]  Maximn\Horseracing\Exchange\Parser::Maximn\Horseracing\Exchange\Parser::readRunnerOdds
[2020-02-06 19:36:48.068358]    [NOTICE]        Maximn\Horseracing\Process\ExchangeRacesUpdate::Maximn\Horseracing\Process\ExchangeRacesUpdate::updateOdds : market 1.168392099 skipped, status not CLOSED : SUSPENDED
[2020-02-06 19:36:48.068402]    [INFO]  Maximn\Horseracing\Process\ExchangeRacesUpdate::run      FINISHED! Lines updated: 15
[2020-02-06 19:39:41.865963]    [INFO]  Maximn\Horseracing\Process\ExchangeRacesUpdate::run
[2020-02-06 19:39:42.309571]    [INFO]  Maximn\Horseracing\Exchange\Parser::Maximn\Horseracing\Exchange\Parser::readRunnerOdds
[2020-02-06 19:39:48.496176]    [INFO]  Maximn\Horseracing\Process\ExchangeRacesUpdate::run      FINISHED! Lines updated: 15

```
